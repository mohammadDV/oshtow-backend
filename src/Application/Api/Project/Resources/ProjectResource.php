<?php

namespace App\Application\Api\Project\Resources;

use App\Application\Api\Address\Resources\CityResource;
use App\Application\Api\Address\Resources\CountryResource;
use App\Application\Api\Address\Resources\ProvinceResource;
use Illuminate\Http\Resources\Json\JsonResource;

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
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'path_type' => $this->path_type,
            'amount' => $this->amount,
            'weight' => $this->weight,
            'status' => $this->status,
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}