<?php

namespace Application\Api\Post\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Morilog\Jalali\Jalalian;

class PostResource extends JsonResource
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
            'pre_title' => $this->pre_title,
            'title' => $this->title,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'content' => $this->content,
            'image' => $this->image,
            'thumbnail' => $this->thumbnail,
            'slide' => $this->slide,
            'video' => $this->video,
            'view' => $this->view,
            'special' => $this->special,
            'created_at' => $this->created_at ? Jalalian::fromDateTime($this->created_at)->format('Y/m/d H:i') : null,
        ];
    }
}