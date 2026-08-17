<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,

            'featured_image' => $this->featured_image
                ? Storage::disk('public')->url($this->featured_image)
                : null,

            'featured_image_alt' => $this->featured_image_alt,
            'is_featured' => $this->is_featured,

            'category' => $this->whenLoaded('category', fn (): ?array => $this->category
                ? [
                    'name' => $this->category->name,
                    'slug' => $this->category->slug,
                ]
                : null),

            'tags' => $this->whenLoaded(
                'tags',
                fn () => $this->tags->map(fn ($tag): array => [
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ])
            ),

            'author' => $this->whenLoaded('author', fn (): ?array => $this->author
                ? [
                    'name' => $this->author->name,
                ]
                : null),

            'seo' => [
                'title' => $this->meta_title ?: $this->title,
                'description' => $this->meta_description ?: $this->excerpt,
                'canonical_url' => $this->canonical_url,
                'focus_keyword' => $this->focus_keyword,
                'schema' => $this->schema_json,
            ],

            'published_at' => $this->published_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}