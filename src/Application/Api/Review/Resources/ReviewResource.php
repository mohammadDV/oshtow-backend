<?php

namespace Application\Api\Review\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Application\Api\User\Resources\UserResource;

class ReviewResource extends JsonResource
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
            'comment' => $this->comment,
            'rate' => $this->rate,
            'status' => $this->status,
            'claim_id' => $this->claim_id,
            'owner_id' => $this->owner_id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}