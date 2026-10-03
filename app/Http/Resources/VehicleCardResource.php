<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'version' => $this->version,
            'price' => $this->price,
            'price_mkd' => $this->price_mkd,
            'year' => $this->year,
            'mileage_km' => $this->mileage_km,
            'fuel' => config("marketplace.fuels.{$this->fuel}", $this->fuel),
            'transmission' => config("marketplace.transmissions.{$this->transmission}", $this->transmission),
            'body_type' => $this->body_type,
            'city' => $this->city,
            'seller_type' => $this->seller_type,
            'is_featured' => (bool) $this->is_featured,
            'promotion' => $this->promotion,
            'status' => $this->status,
            'cover_url' => $this->cover_url,
            'images_count' => $this->whenCounted('images', $this->images_count ?? null),
            'category' => $this->whenLoaded('category', fn () => $this->category->slug),
            'make' => $this->whenLoaded('make', fn () => $this->make?->name),
            'model' => $this->whenLoaded('carModel', fn () => $this->carModel?->name),
            'dealer_name' => $this->whenLoaded('dealer', fn () => $this->dealer?->name),
            'seller_name' => $this->whenLoaded('user', fn () => $this->user?->name),
        ];
    }
}
