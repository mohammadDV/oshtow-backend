<?php

namespace Domain\Plan\Repositories;

use Application\Api\Plan\Requests\PlanRequest;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Plan\Models\Plan;
use Domain\Plan\Repositories\Contracts\IPlanRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class PlanRepository.
 */
class PlanRepository implements IPlanRepository
{
    use GlobalFunc;

    /**
     * Get the plans pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        return Plan::query()
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('title', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }

    /**
     * Get the plans.
     * @return Collection
     */
    public function activePlans() :Collection
    {
        return Plan::query()
            ->where('id', '!=', config('plan.default_plan_id'))
            ->where('status', 1)
            ->get();
    }

    /**
     * Get the plan.
     * @param Plan $plan
     * @return Plan
     */
    public function show(Plan $plan) :Plan
    {
        return Plan::query()
                ->where('id', $plan->id)
                ->first();
    }

    /**
     * Store the plan.
     * @param PlanRequest $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function store(PlanRequest $request) :JsonResponse
    {
        $this->checkLevelAccess();

        $plan = Plan::create([
            'title' => $request->input('title'),
            'priod' => $request->input('priod'),
            'status' => $request->input('status'),
            'amount' => $request->input('amount'),
            'period_count' => $request->input('period_count'),
            'claim_count' => $request->input('claim_count'),
            'project_count' => $request->input('project_count'),
            'user_id' => Auth::user()->id,
        ]);

        if ($plan) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully'),
                'plan' =>  $plan
            ], Response::HTTP_CREATED);
        }

        throw new \Exception();
    }

    /**
     * Update the plan.
     * @param PlanRequest $request
     * @param Plan $plan
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(PlanRequest $request, Plan $plan) :JsonResponse
    {
        $this->checkLevelAccess(Auth::user()->id == $plan->user_id);

        $plan = $plan->update([
            'title' => $request->input('title'),
            'priod' => $request->input('priod'),
            'type' => $request->input('type'),
            'status' => $request->input('status'),
            'amount' => $request->input('amount'),
            'period_count' => $request->input('period_count'),
            'claim_count' => $request->input('claim_count'),
            'project_count' => $request->input('project_count'),
            'user_id' => Auth::user()->id,
        ]);

        if ($plan) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
    }

    /**
    * Delete the plan.
    * @param UpdatePasswordRequest $request
    * @param Plan $plan
    * @return JsonResponse
    */
   public function destroy(Plan $plan) :JsonResponse
   {
        $this->checkLevelAccess(Auth::user()->id == $plan->user_id);

        $plan->delete();

        if ($plan) {
            return response()->json([
                'status' => 1,
                'message' => __('site.The operation has been successfully')
            ], Response::HTTP_OK);
        }

        throw new \Exception();
   }
}