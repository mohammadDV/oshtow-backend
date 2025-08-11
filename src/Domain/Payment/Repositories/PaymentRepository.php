<?php

namespace Domain\Payment\Repositories;

use Core\Http\Requests\TableRequest;
use Domain\Payment\Models\Transaction;
use Domain\Payment\Repositories\Contracts\IPaymentRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Application\Api\Payment\Resources\TransactionsResource;

class PaymentRepository implements IPaymentRepository
{
    /**
     * Get the identityRecords pagination.
     * @param TableRequest $request
     * @return LengthAwarePaginator
     */
    public function index(TableRequest $request) :LengthAwarePaginator
    {
        $search = $request->get('query');
        $transactions = Transaction::query()
            ->where('user_id', Auth::user()->id)
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('bank_transaction_id', 'like', '%' . $search . '%')
                    ->orWhere('reference', 'like', '%' . $search . '%');
            })
            ->orderBy($request->get('column', 'id'), $request->get('sort', 'desc'))
            ->paginate($request->get('count', 25));


        return $transactions->through(fn ($transaction) => new TransactionsResource($transaction));

    }

}
