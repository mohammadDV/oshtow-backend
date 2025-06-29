<?php

namespace Domain\Claim\Repositories;

use Application\Api\Chat\Requests\ChatRequest;
use Application\Api\Claim\Requests\ClaimRequest;
use Application\Api\Claim\Requests\ConfirmationRequest;
use Application\Api\Claim\Requests\DeliveryConfirmationRequest;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Chat\Repositories\Contracts\IChatRepository;
use Domain\Claim\Models\Claim;
use Domain\Claim\Models\ClaimStep;
use Domain\Claim\Repositories\Contracts\IClaimRepository;
use Domain\Payment\Models\PaymentSecure;
use Domain\Project\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Domain\Wallet\Models\Wallet;

/**
 * Class ClaimRepository.
 */
class ClaimRepository implements IClaimRepository
{
    use GlobalFunc;

    /**
     * Get all claims for a specific project.
     * @param Project $project
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getClaimsPerProject(Project $project, TableRequest $request): LengthAwarePaginator
    {
        $this->checkLevelAccess(Auth::user()->id == $project->user_id);

        $search = $request->get('query');
        $projects = Claim::query()
            ->with(['project', 'user:id,nickname,bg_photo_path,profile_photo_path'])
            ->where('project_id', $project->id)
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('description', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));

        return $projects;
    }

    /**
     * Get the claim.
     * @param Claim $claim
     * @return Claim
     */
    public function show(Claim $claim): Claim
    {
        $claim = Claim::query()
            ->with(['project', 'user:id,nickname,bg_photo_path,profile_photo_path'])
            ->where('id', $claim->id)
            ->first();

        return $claim;
    }

    /**
     * Store a new claim.
     * @param ClaimRequest $request
     * @return JsonResponse
     */
    public function store(ClaimRequest $request): JsonResponse
    {
        $this->expireSubscriprions();

        if (!$this->checkSubscriprion('claim')) {
            return response()->json([
                'status' => 0,
                'message' => __('site.No active subscription found'),
            ], Response::HTTP_BAD_REQUEST);
        }

        $project = Project::findOrfail($request->input('project_id'));

        $exist = Claim::query()
            ->where('user_id', Auth::user()->id)
            ->where('project_id', $request->input('project_id'))
            ->first();

        if ($exist) {
            return response()->json([
                'status' => 0,
                'message' => __('site.You have already created a claim for this project')
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->checkLevelAccess(
            Auth::user()->id != $project->user_id &&
            $project->status == Project::PENDING &&
            $project->active == 1
        );

        $address = $request->input('address');

        if (empty($request->input('address'))) {
            if ($request->input('address_type') == Claim::OTHER) {
                $address = $project->address;
            } else {
                $address = Auth::user()->address;
            }
        }

        $claim = Claim::create([
            'description' => $request->input('description'),
            'amount' => $request->input('amount') ?? null,
            'weight' => $request->input('weight') ?? null,
            'address' => $address,
            'address_type' => $request->input('address_type') ?? null,
            'image' => $request->input('image') ?? null,
            'status' => Claim::PENDING,
            'project_id' => $request->input('project_id'),
            'user_id' => Auth::user()->id,
            'sponsor_id' => $project->type == Project::PASSENGER ? Auth::user()->id : $project->user_id
        ]);

        // create chat for them
        $this->handleClaimChat($project);

        //todo notification
        if ($claim) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully'),
                'data' => $claim
            ], Response::HTTP_CREATED);
        }

        throw new \Exception();
    }

    /**
     * Handle chat creation between claim creator and project owner.
     *
     * @param Project $project
     * @return void
     */
    private function handleClaimChat(Project $project): void
    {
        // Only create chat if claim creator and project owner are different
        if (Auth::user()->id != $project->user_id) {
            $chatRepo = app(IChatRepository::class);
            $chatUser = $project->user; // project owner

            $chatRequest = new ChatRequest([
                'message' => __('site.A claim has been created for your project.'),
            ]);

            $chatRepo->store($chatRequest, $chatUser);
        }
    }

    /**
     * Store a new claim.
     * @param Claim $claim
     * @param ClaimRequest $request
     * @return JsonResponse
     */
    public function update(Claim $claim, ClaimRequest $request): JsonResponse
    {
        $this->checkLevelAccess(
            Auth::user()->id == $claim->user_id &&
            $claim->status == Claim::PENDING);

        $address = $request->input('address');

        if (empty($request->input('address'))) {
            if ($request->input('address_type') == Claim::OTHER) {
                $address = $project->user->address;
            } else {
                $address = Auth::user()->address;
            }
        }

        $updated = $claim->update([
            'description' => $request->input('description'),
            'amount' => $request->input('amount') ?? null,
            'weight' => $request->input('weight') ?? null,
            'address' => $address,
            'address_type' => $request->input('address_type') ?? null,
            'image' => $request->input('image') ?? null,
            'status' => Claim::PENDING,
            'project_id' => $request->input('project_id'),
            'user_id' => Auth::user()->id,
        ]);

        //todo notification

        if ($updated) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully'),
                'data' => $claim
            ], Response::HTTP_CREATED);
        }

        throw new \Exception();
    }

    /**
     * Approve a claim.
     * @param Claim $claim
     * @return JsonResponse
     */
    public function approveClaim(Claim $claim): JsonResponse
    {

        $this->checkLevelAccess(
            Auth::user()->id == $claim->project->user_id &&
            $claim->status == Claim::PENDING);

        try {
            DB::beginTransaction();

            // Check if user has already approved a claim for this project
            $existingApprovedClaim = Claim::query()
                ->where('project_id', $claim->project_id)
                ->where('status', '!=' ,Claim::PENDING)
                ->first();

            if ($existingApprovedClaim) {
                return response()->json([
                    'status' => 0,
                    'message' => __('site.You have already approved a claim for this project')
                ], Response::HTTP_BAD_REQUEST);
            }

            // Update claim status
            $claim->update(['status' => Claim::APPROVED]);
            $claim->project()->update(['status' => Project::INPROGRESS]);

            // Create claim step
            ClaimStep::create([
                'step_id' => 1,
                'claim_id' => $claim->id,
                'data' => $claim->image,
                'description' => 'Claim approved: project #' . $claim->project->id,
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully'),
                'data' => $claim
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Paid a claim.
     * @param Claim $claim
     * @return void
     */
    public function paidClaim(Claim $claim): void
    {
        // Check if user has already approved a claim for this project
        $existingApprovedClaim = ClaimStep::query()
            ->where('step_id', 2)
            ->where('claim_id', $claim->id)
            ->first();

        $this->checkLevelAccess(
            Auth::user()->id == $claim->sponsor_id &&
            $claim->status == Claim::APPROVED &&
            !$existingApprovedClaim
        );

        try {
            DB::beginTransaction();

            // Update claim status
            $claim->update(['status' => Claim::PAID]);

            // Create claim step
            ClaimStep::create([
                'step_id' => 2,
                'claim_id' => $claim->id,
                'data' => $claim->image,
                'description' => 'Claim paid: project #' . $claim->project->id,
            ]);

            $walletOwnerId = $claim->project->type == Project::PASSENGER ? $claim->user_id : $claim->project->user_id;

            // Get the wallet of the user that created this project
            $wallet = Wallet::query()
                ->where('currency', Wallet::IRR)
                ->where('user_id', $walletOwnerId)
                ->firstOrFail();

            // Create payment secure
            PaymentSecure::create([
                'claim_id' => $claim->id,
                'wallet_id' => $wallet->id,
                'amount' => $claim->amount,
                'status' => PaymentSecure::PENDING,
                'expires_at' => now()->addDays(7),
                'description' => 'Payment secure for claim #' . $claim->id,
                'user_id' => Auth::user()->id,
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Inprogress a claim.
     * @param Claim $claim
     * @param ConfirmationRequest $request
     * @return JsonResponse
     */
    public function inprogressClaim(Claim $claim, ConfirmationRequest $request): JsonResponse
    {

        $passengerId = $claim->project->type == Project::PASSENGER ? $claim->project->user_id : $claim->user_id;

        // Check if user has already approved a claim for this project
        $existingApprovedClaim = ClaimStep::query()
            ->where('step_id', 3)
            ->where('claim_id', $claim->id)
            ->first();

        $this->checkLevelAccess(
            Auth::user()->id == $passengerId &&
            $claim->status == Claim::PAID &&
            !$existingApprovedClaim
        );

        try {
            DB::beginTransaction();

            // Update claim status
            $claim->update([
                'status' => Claim::INPROGRESS,
                'confirmation_image' => $request->input('confirmation_image'),
                'confirmation_description' => $request->input('confirmation_description'),
                'delivery_code' => random_int(10000000, 99999999),
            ]);

            // Create claim step
            ClaimStep::create([
                'step_id' => 3,
                'claim_id' => $claim->id,
                'data' => $claim->confirmation_image,
                'description' => 'Claim inprogress: project #' . $claim->project->id . ' #' . $request->input('confirmation_description'),
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully'),
                'data' => $claim
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delivery a claim.
     * @param Claim $claim
     * @param DeliveryConfirmationRequest $request
     * @return JsonResponse
     */
    public function deliveredClaim(Claim $claim, DeliveryConfirmationRequest $request): JsonResponse
    {

        $passengerId = $claim->project->type == Project::PASSENGER ? $claim->project->user_id : $claim->user_id;

        // Check if user has already approved a claim for this project
        $existingApprovedClaim = ClaimStep::query()
            ->where('step_id', 4)
            ->where('claim_id', $claim->id)
            ->first();

        $this->checkLevelAccess(
            Auth::user()->id == $passengerId &&
            $claim->status == Claim::INPROGRESS &&
            !$existingApprovedClaim
        );

        if ($claim->delivery_code != trim($request->input('confirmation_code'))) {
            return response()->json([
                'status' => 0,
                'message' => __('site.The confirmation code is incorrect.'),
            ], Response::HTTP_OK);
        }

        try {
            DB::beginTransaction();

            // Update the claim
            $claim->update([
                'status' => Claim::DELIVERED,
                'confirmed_code' => $request->input('confirmation_code'),
            ]);

            // Update the project
            $claim->project->update([
                'status' => Project::COMPLETED,
            ]);

            // Create claim step
            ClaimStep::create([
                'step_id' => 4,
                'claim_id' => $claim->id,
                'data' => $request->input('confirmation_code'),
                'description' => 'Claim delivered: project #' . $claim->project->id,
            ]);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully'),
                'data' => $claim
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
