<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            'username'      => $this->user->username,
            'caption'       => $this->caption,
            'medias_count'  => $this->postMedias()->count(),
            'like_count'    => $this->like_count,
            'comment_count' => $this->comment_count,
            'created_at'    => (string)$this->created_at,
        ];
    }
}
