<?php

namespace Domain\Wallet\Repositories;

use Core\Http\Requests\TableRequest;
use Core\Http\traits\GlobalFunc;
use Domain\Wallet\Models\Wallet;
use Domain\Wallet\Models\WalletTransaction;
use Domain\Wallet\Repositories\Contracts\IWalletTransactionRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class WalletTransactionRepository implements IWalletTransactionRepository
{

    use GlobalFunc;

    /**
     * Get the WalletTransaction pagination.
     * @param TableRequest $request
     * @param Wallet $wallet
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request, Wallet $wallet) :LengthAwarePaginator
    {
        $type = $request->get('type');
        $status = $request->get('status');
        return WalletTransaction::query()
            ->where('wallet_id', $wallet->id)
            ->when(!empty($type), function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->when(!empty($status), function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));
    }
}