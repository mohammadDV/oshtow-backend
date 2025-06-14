<?php

namespace Application\Api\Project\Resources;

use Application\Api\Address\Resources\CityResource;
use Application\Api\Address\Resources\CountryResource;
use Application\Api\Address\Resources\ProvinceResource;
use Application\Api\Project\Resources\CategoryResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Morilog\Jalali\Jalalian;

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}