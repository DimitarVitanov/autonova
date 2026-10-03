<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
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
            'vat' => config("marketplace.vat.{$this->vat}", $this->vat),
            'year' => $this->year,
            'mileage_km' => $this->mileage_km,
            'fuel' => config("marketplace.fuels.{$this->fuel}", $this->fuel),
            'transmission' => config("marketplace.transmissions.{$this->transmission}", $this->transmission),
            'drivetrain' => $this->drivetrain ? config("marketplace.drivetrains.{$this->drivetrain}", $this->drivetrain) : null,
            'engine_cc' => $this->engine_cc,
            'power_hp' => $this->power_hp,
            'body_type' => $this->body_type,
            'doors' => $this->doors,
            'seats' => $this->seats,
            'color' => $this->color,
            'condition' => config("marketplace.conditions.{$this->condition}", $this->condition),
            'owners' => $this->owners,
            'registered_until' => $this->registered_until,
            'city' => $this->city,
            'description' => $this->description,
            'seller_type' => $this->seller_type,
            'contact_phone' => $this->contact_phone,
            'status' => $this->status,
            'promotion' => $this->promotion,
            'is_featured' => (bool) $this->is_featured,
            'views' => $this->views,
            'published_at' => $this->published_at?->diffForHumans(),
            'category' => [
                'slug' => $this->category->slug,
                'name' => $this->category->name,
                'name_plural' => $this->category->name_plural,
            ],
            'make' => $this->make?->name,
            'model' => $this->carModel?->name,
            'cover_url' => $this->cover_url,
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($img) => [
                'id' => $img->id,
                'url' => $img->url,
                'thumb_url' => $img->thumb_url,
                'is_cover' => $img->is_cover,
            ])),
            'features' => $this->whenLoaded('features', fn () => $this->features
                ->groupBy('group')
                ->map(fn ($items) => $items->pluck('name'))),
            'dealer' => $this->whenLoaded('dealer', fn () => $this->dealer ? [
                'name' => $this->dealer->name,
                'slug' => $this->dealer->slug,
                'city' => $this->dealer->city,
                'verified' => $this->dealer->verified,
                'founded_year' => $this->dealer->founded_year,
                'logo_url' => $this->dealer->logo_url,
                'vehicles_count' => $this->dealer->vehicles()->active()->count(),
            ] : null),
            'seller' => [
                'name' => $this->user->name,
                'city' => $this->user->city,
            ],
        ];
    }
}
