<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
            'place_id' => $this->place_id,
            'place' => new PlaceResource($this->whenLoaded('place')),
            'rating' => (int) $this->rating,
            'title' => $this->title,
            'comment' => $this->comment,
            'visit_date' => $this->visit_date?->format('Y-m-d'),
            'is_verified' => (bool) $this->is_verified,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
