<?php

namespace Application\Api\Project\Resources;

use Application\Api\Address\Resources\CityResource;
use Application\Api\Address\Resources\CountryResource;
use Application\Api\Address\Resources\ProvinceResource;
use Application\Api\Claim\Resources\ClaimResource;
use Application\Api\Project\Resources\CategoryResource;
use Domain\Project\Models\Project;
use Illuminate\Http\Resources\Json\JsonResource;
use Morilog\Jalali\Jalalian;
use Application\Api\User\Resources\UserResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $destinationImage = config('image.default_project_image');

        if ($this->type == Project::PASSENGER) {
            if ($this->relationLoaded('dProvince') && $this->dProvince?->image) {
                $destinationImage = $this->dProvince->image;
            } elseif ($this->relationLoaded('dCountry') && $this->dCountry?->image) {
                $destinationImage = $this->dCountry->image;
            }
        }
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'path_type' => $this->path_type,
            'amount' => $this->amount,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
            'status' => $this->status,
            'description' => $this->description,
            'vip' => $this->vip,
            'destination_image' => $destinationImage,
            'send_date' => $this->send_date ? Jalalian::fromDateTime($this->send_date)->format('d F') : null,
            'receive_date' => $this->receive_date ? Jalalian::fromDateTime($this->receive_date)->format('d F') : null,
            'origin' => [
                'country' => new CountryResource($this->whenLoaded('oCountry')),
                'province' => new ProvinceResource($this->whenLoaded('oProvince')),
                'city' => new CityResource($this->whenLoaded('oCity')),
            ],
            'destination' => [
                'country' => new CountryResource($this->whenLoaded('dCountry')),
                'province' => new ProvinceResource($this->whenLoaded('dProvince')),
                'city' => new CityResource($this->whenLoaded('dCity')),
            ],
            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            'user' => new UserResource($this->whenLoaded('user')),
            'claims' => ClaimResource::collection($this->whenLoaded('claims')),
            'claimsLimit' => ClaimResource::collection($this->whenLoaded('claimsLimit')),
            'claimSelected' => ClaimResource::collection($this->whenLoaded('claimSelected')),
            'claims_count' => $this->whenLoaded('claims') ? $this->claims->count() : ($this->claims_count ?? 0),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}