<?php

namespace Application\Api\Plan\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
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
            'title' => $this->title,
            'period' => $this->priod, // Note: 'priod' seems to be a typo in the database, using as is.
            'status' => $this->status,
            'amount' => $this->amount,
            'period_count' => $this->period_count,
            'claim_count' => $this->claim_count,
            'project_count' => $this->project_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
