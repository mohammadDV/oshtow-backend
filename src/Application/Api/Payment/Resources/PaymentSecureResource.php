<?php

namespace Application\Api\Payment\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Morilog\Jalali\Jalalian;

class PaymentSecureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'status' => $this->status,
            'user_id' => $this->user_id,
            'created_at' => $this->created_at ? Jalalian::fromDateTime($this->created_at)->format('Y-m-d H:i:s') : null,
        ];
    }
}
