<?php

namespace Application\Api\Claim\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Application\Api\User\Resources\UserResource;
use Application\Api\Project\Resources\ProjectResource;

class ClaimResource extends JsonResource
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
            'description' => $this->description,
            'amount' => $this->amount,
            'weight' => $this->weight,
            'address' => $this->address,
            'address_type' => $this->address_type,
            'image' => $this->image,
            'status' => $this->status,
            'user' => new UserResource($this->whenLoaded('user')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'sponsor_id' => $this->sponsor_id,
            'created_at' => $this->created_at,
        ];
    }
}