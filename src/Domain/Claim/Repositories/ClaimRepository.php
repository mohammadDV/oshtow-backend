<?php

namespace Domain\Claim\Repositories;

use Application\Api\Chat\Requests\ChatRequest;
use Application\Api\Claim\Requests\ClaimRequest;
use Application\Api\Claim\Requests\ConfirmationRequest;
use Application\Api\Claim\Requests\DeliveryConfirmationRequest;
use Application\Api\Claim\Resources\ClaimResource;
use Application\Api\Payment\Requests\PaymentSecureRequest;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Chat\Models\Chat;
use Domain\Chat\Repositories\Contracts\IChatRepository;
use Domain\Claim\Models\Claim;
use Domain\Claim\Models\ClaimStep;
use Domain\Claim\Repositories\Contracts\IClaimRepository;
use Domain\Notification\Services\NotificationService;
use Domain\Payment\Models\PaymentSecure;
use Domain\Payment\Models\Transaction;
use Domain\Project\Models\Project;
use Domain\Review\Models\Review;
use Domain\User\Models\User;
use Domain\User\Services\TelegramNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Domain\Wallet\Models\Wallet;
use Domain\Wallet\Models\WalletTransaction;
use Domain\Wallet\Repositories\Contracts\IWalletRepository;

/**
 * Class ClaimRepository.
 */
class ClaimRepository implements IClaimRepository
{
    use GlobalFunc;

    public function __construct(protected IWalletRepository $walletRepository, protected TelegramNotificationService $service)
    {
    }

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
        $claims = Claim::query()
            ->with(['project', 'user:id,nickname,bg_photo_path,profile_photo_path'])
            ->where('project_id', $project->id)
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('description', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));

        return $claims->through(fn ($claim) => new ClaimResource($claim));

    }

    /**
     * Get all claims for a specific user.
     * @param User $user
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function getClaimsPerUser(User $user, TableRequest $request): LengthAwarePaginator
    {
        $this->checkLevelAccess(Auth::user()->id == $user->id);

        $status = $request->get('status');
        $claims = Claim::query()
            ->with(['project.user', 'user:id,nickname,bg_photo_path,profile_photo_path'])
            ->where('user_id', $user->id)
            ->when(!empty($status), function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));

        return $claims->through(fn ($claim) => new ClaimResource($claim));
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
     * Get the status of the claim.
     * @param Claim $claim
     * @return array
     */
    public function getStatus(Claim $claim): array
    {
        $this->checkLevelAccess(in_array(Auth::user()->id, [$claim->project->user_id, $claim->user_id]));

        if ($claim->project->user_id == Auth::id()) {
            $type = $claim->project->type;
        } else {
            $type = $claim->project->type == Project::PASSENGER ? Project::SENDER : Project::PASSENGER;
        }


        $showCommentForm = false;

        if ($claim->status == Claim::DELIVERED) {
            $review = Review::query()
                ->where('user_id', Auth::id())
                ->where('claim_id', $claim->id)
                ->first();

            if (!$review) {
                $showCommentForm = true;
            }
        }

        $chat = Chat::query()
            ->where(function ($query) use ($claim) {
                $query->where('user_id', $claim->user_id)
                    ->where('target_id', $claim->project->user_id);
            })
            ->orWhere(function ($query) use ($claim) {
                $query->where('user_id', $claim->project->user_id)
                    ->where('target_id', $claim->user_id);
            })
            ->orderBy('id', 'desc')
            ->first();

        return [
            'type' => $type,
            'sponsor' => $claim->sponsor_id == Auth::id(),
            'status' => $claim->status,
            'delivery_code' => $type == Project::SENDER ? $claim->delivery_code ?? '' : '',
            'show_review_form' => $showCommentForm,
            'chat_id' => $chat->id ?? null,
        ];
    }

    /**
     * Store a new claim.
     * @param ClaimRequest $request
     * @return JsonResponse
     */
    public function store(ClaimRequest $request): JsonResponse
    {
        if (empty(Auth::user()->status)) {
            return response()->json([
                'status' => 0,
                'message' => __('site.Your account is not active yet. Please send a message to the admin from ticket section.'),
            ], Response::HTTP_BAD_REQUEST);
        }

        if (empty(Auth::user()->verified_at)) {
            return response()->json([
                'status' => 0,
                'message' => __('site.You must verify your account to create a claim'),
            ], Response::HTTP_BAD_REQUEST);
        }

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
            $project->status == Project::APPROVED &&
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

        NotificationService::create([
            'title' => __('site.new_claim_received_title'),
            'content' => __('site.new_claim_received_content', ['project_title' => $project->title]),
            'id' => $project->id,
            'type' => $project->type,
        ], $project->user);

        $this->service->sendNotification(
            config('telegram.chat_id'),
            'ارسال درخواست جدید' . PHP_EOL .
            'id ' . Auth::user()->id . PHP_EOL .
            'nickname ' . Auth::user()->nickname . PHP_EOL .
            'title' . $project->title . PHP_EOL .
            'claim' . $claim->id . PHP_EOL .
            'amount' . number_format($claim->amount) . PHP_EOL .
            'time ' . now()
        );

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

        NotificationService::create([
            'title' => __('site.claim_updated_title'),
            'content' => __('site.claim_updated_content', ['project_title' => $claim->project->title]),
            'id' => $claim->project->id,
            'type' => $claim->project->type,
        ], $claim->project->user);

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

            NotificationService::create([
                'title' => __('site.claim_approved_title'),
                'content' => __('site.claim_approved_content', ['project_title' => $claim->project->title]),
                'id' => $claim->id,
                'type' => 'claim',
            ], $claim->user);

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
     * @param PaymentSecureRequest $request
     * @param Claim $claim
     */
    public function paidClaim(PaymentSecureRequest $request ,Claim $claim)
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

        if ($request->input('payment_method') === PaymentSecure::WALLET) {
            return $this->payWithWallet($claim, $request->input('amount'));
        }

        // TODO: Implement bank payment gateway
        return $this->payWithBank($claim, $request->input('amount'));

    }

    /**
     * Pay with wallet.
     * @param Plan $plan
     * @throws \Exception
     */
    private function payWithBank(Claim $claim, $amount)
    {
        $amount = intval($amount);

        $transaction = Transaction::create([
            'status' => Transaction::PENDING,
            'model_id' => $claim->id,
            'model_type' => Transaction::SECURE,
            'amount' => $amount,
            'user_id' => Auth::user()->id,
        ]);

        $code = Transaction::generateHash($transaction->id);

        if ($transaction) {
            return [
                'status' => 1,
                'url' => route('user.payment') . '?transaction=' . $transaction->id . '&sign=' . $code
            ];
        }

        return response()->json([
            'status' => 0,
            'message' => __('site.Top-up failed. Please try again.'),
        ], 500);
    }

    /**
     * Pay with wallet.
     * @param Claim $claim
     * @return JsonResponse
     * @throws \Exception
     */
    private function payWithWallet(Claim $claim, $amount): JsonResponse
    {
        $wallet = $this->walletRepository->findByUserId(Auth::id());

        if ($wallet->balance < $amount) {
            return response()->json([
                'status' => 0,
                'message' => __('site.Insufficient funds'),
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        try {
            DB::beginTransaction();

            // Create claim step
            ClaimStep::create([
                'step_id' => 2,
                'claim_id' => $claim->id,
                'data' => $claim->image,
                'description' => 'Claim paid: project #' . $claim->project->id,
            ]);

            // Get the wallet of the user that created this project
            $wallet = Wallet::query()
                ->where('currency', Wallet::IRR)
                ->where('user_id', Auth::user()->id)
                ->firstOrFail();

            $walletReleased = Wallet::query()
                ->where('currency', Wallet::IRR)
                ->where('user_id', $claim->project->type == Project::PASSENGER ? $claim->project->user_id :$claim->user_id)
                ->firstOrFail();

            // Create payment secure
            PaymentSecure::create([
                'claim_id' => $claim->id,
                'wallet_id' => $wallet->id,
                'wallet_id_released' => $walletReleased->id,
                'amount' => $amount,
                'status' => PaymentSecure::PENDING,
                'expires_at' => now()->addDays(15),
                'description' => 'Payment secure for claim #' . $claim->id,
                'user_id' => Auth::user()->id,
            ]);

            // Update claim status
            $claim->update(['status' => Claim::PAID]);

            WalletTransaction::createTransaction(
                $wallet,
                -$amount,
                WalletTransaction::PURCHASE,
                __('site.wallet_transaction_payment_secure_purchase', ['claim_id' => $claim->id])
            );

            NotificationService::create([
                'title' => __('site.claim_paid_title'),
                'content' => __('site.claim_paid_content', ['project_title' => $claim->project->title]),
                'id' => $claim->id,
                'type' => NotificationService::CLAIM,
            ], $claim->project->user);

            DB::commit();

            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create a subscription
     * @param Plan $plan
     * @param User $user
     * @return array
     */
    public function createPaymentSecure(Claim $claim): array
    {

        try {
            DB::beginTransaction();

            // Update claim status
            $claim->update(['status' => Claim::PAID]);

            // Create claim step
            ClaimStep::updateOrCreate([
                'step_id' => 2,
                'claim_id' => $claim->id,
            ],[
                'data' => $claim->image,
                'description' => 'Claim paid: project #' . $claim->project->id,
            ]);

            $walletOwnerId = $claim->project->type == Project::PASSENGER ? $claim->user_id : $claim->project->user_id;

            // Get the wallet of the user that created this project
            $wallet = Wallet::query()
                ->where('currency', Wallet::IRR)
                ->where('user_id', $walletOwnerId)
                ->firstOrFail();

            $walletReleased = Wallet::query()
                ->where('currency', Wallet::IRR)
                ->where('user_id', $claim->project->type == Project::PASSENGER ? $claim->project->user_id :$claim->user_id)
                ->firstOrFail();

            // Create payment secure
            $payment = PaymentSecure::create([
                'claim_id' => $claim->id,
                'wallet_id' => $wallet->id,
                'wallet_id_released' => $walletReleased->id,
                'amount' => $claim->amount,
                'status' => PaymentSecure::PENDING,
                'expires_at' => now()->addDays(15),
                'description' => 'Payment secure for claim #' . $claim->id,
                'user_id' => $walletOwnerId,
            ]);

            NotificationService::create([
                'title' => __('site.claim_paid_title'),
                'content' => __('site.claim_paid_content_alt', ['project_title' => $claim->project->title]),
                'id' => $claim->id,
                'type' => NotificationService::CLAIM,
            ], $claim->project->user);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }


        if ($payment) {
            return [
                'status' => 1,
                'payment_secure' => $payment
            ];
        }

        return [
            'status' => 0,
            'payment_secure' => '',
        ];
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

            NotificationService::create([
                'title' => __('site.claim_inprogress_title'),
                'content' => __('site.claim_inprogress_content', ['user_nickname' => Auth::user()->nickname]),
                'id' => $claim->id,
                'type' => NotificationService::CLAIM,
            ], $claim->project->type == Project::PASSENGER ? $claim->user : $claim->project->user);

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

            $this->releasePaymentSecure($claim);

            NotificationService::create([
                'title' => __('site.claim_delivered_title'),
                'content' => __('site.claim_delivered_content', ['user_nickname' => Auth::user()->nickname]),
                'id' => $claim->id,
                'type' => NotificationService::CLAIM,
            ], $claim->project->type == Project::PASSENGER ? $claim->user : $claim->project->user);

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
     * Release the payment secure.
     * @param Claim $claim
     * @return void
     */
    private function releasePaymentSecure(Claim $claim): void
    {
        $paymentSecure = PaymentSecure::where('claim_id', $claim->id)->first();
        if ($paymentSecure->status == PaymentSecure::PENDING) {
            $paymentSecure->release();

            WalletTransaction::createTransaction(
                $paymentSecure->walletReleased,
                $paymentSecure->amount,
                WalletTransaction::DEPOSITE,
                __('site.wallet_transaction_payment_secure_released', ['claim_id' => $claim->id])
            );

            NotificationService::create([
                'title' => __('site.payment_secure_released_title'),
                'content' => __('site.payment_secure_released_content'),
                'id' => $claim->id,
                'type' => NotificationService::CLAIM,
            ], $claim->project->type == Project::PASSENGER ? $claim->project->user : $claim->user);
        }
    }
}
