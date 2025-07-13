<?php

namespace Domain\Plan\Repositories;

use Application\Api\Plan\Requests\StoreSubscribeRequest;
use Application\Api\Plan\Resources\SubscriptionResource;
use Carbon\Carbon;
use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Payment\Models\Transaction;
use Domain\Plan\Models\Plan;
use Domain\Plan\Models\Subscription;
use Domain\Plan\Repositories\Contracts\ISubscribeRepository;
use Domain\User\Models\User;
use Domain\Wallet\Models\WalletTransaction;
use Domain\Wallet\Repositories\Contracts\IWalletRepository;
use Evryn\LaravelToman\Facades\Toman;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

/**
 * Class PlanRepository.
 */
class SubscribeRepository implements ISubscribeRepository
{
    use GlobalFunc;

    public function __construct(protected IWalletRepository $walletRepository)
    {
    }

    /**
     * Get the plans pagination.
     * @param TableRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(TableRequest $request) :AnonymousResourceCollection
    {
        $subscriptions = Subscription::query()
            ->with(['plan', 'user'])
            ->when(Auth::user()->level != 3, function ($query) {
                return $query->where('user_id', Auth::user()->id);
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));

        return SubscriptionResource::collection($subscriptions);
    }

    /**
     * Get the plans.
     * @return JsonResponse
     */
    public function activeSubscription() :JsonResponse
    {
        $subscription = Subscription::query()
            ->with(['plan', 'user'])
            ->where('user_id', Auth::user()->id)
            ->where('active', 1)
            ->first();

        if (!empty($subscription)) {
            return response()->json(new SubscriptionResource($subscription), Response::HTTP_OK);
        }

        return response()->json([
            'status' => 0,
            'message' => __('site.No active subscription found')
        ], Response::HTTP_NOT_FOUND);
    }

    /**
     * Store the subscription.
     * @param Plan $plan
     * @throws \Exception
     */
    public function store(StoreSubscribeRequest $request, Plan $plan)
    {
        if ($request->input('payment_method') === 'wallet') {
            return $this->payWithWallet($plan);
        }

        // TODO: Implement bank payment gateway
        return $this->payWithBank($plan);

    }

    /**
     * Pay with wallet.
     * @param Plan $plan
     * @throws \Exception
     */
    private function payWithBank(Plan $plan)
    {
        $amount = intval($plan->amount);

        $transaction = Transaction::create([
            'status' => Transaction::PENDING,
            'model_id' => $plan->id,
            'model_type' => Transaction::PLAN,
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
     * @param Plan $plan
     * @return JsonResponse
     * @throws \Exception
     */
    private function payWithWallet(Plan $plan): JsonResponse
    {
        $wallet = $this->walletRepository->findByUserId(Auth::id());

        if ($wallet->balance < $plan->amount) {
            return response()->json([
                'status' => 0,
                'message' => __('site.Insufficient funds'),
            ], Response::HTTP_PAYMENT_REQUIRED);
        }

        try {
            DB::beginTransaction();

            $this->createSubscription($plan, Auth::user());

            WalletTransaction::createTransaction(
                $wallet,
                -$plan->amount,
                WalletTransaction::PURCHASE,
                'Plan purchase: ' . $plan->title
            );

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
    public function createSubscription(Plan $plan, User $user): array
    {
        Subscription::query()
            ->where('active', 1)
            ->where('user_id', $user->id)
            ->update([
                'active' => 0,
                'ends_at' => now()
            ]);

        $endsAt = Carbon::now()->addMonth(1 * $plan->period_count);

        if ($plan->priod == 'yearly') {
            $endsAt = Carbon::now()->addYear(1 * $plan->period_count);
        }

        $subscribe = Subscription::create([
            'ends_at' => $endsAt,
            'plan_id' => $plan->id,
            'user_id' => $user->id,
            'project_count' => $plan->project_count,
            'claim_count' => $plan->claim_count,
            'active' => 1
        ]);

        if ($subscribe) {
            return [
                'status' => 1,
                'subscription' => $subscribe
            ];
        }

        return [
            'status' => 0,
            'subscription' => '',
        ];
    }
}