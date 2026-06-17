<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationArticle extends Model
{
    protected $table = 'education_articles';

    protected $fillable = [
        'category_id', 'title', 'slug', 'level', 'excerpt', 'body',
        'youtube_id', 'video_title', 'duration', 'thumbnail',
        'read_minutes', 'is_recommended', 'sort_order',
    ];

    protected $casts = [
        'is_recommended' => 'boolean',
        'read_minutes' => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EducationCategory::class, 'category_id');
    }

    /** Privacy-friendly YouTube embed URL (no autoplay). */
    public function embedUrl(): ?string
    {
        return $this->youtube_id
            ? 'https://www.youtube-nocookie.com/embed/'.$this->youtube_id.'?rel=0&modestbranding=1'
            : null;
    }

    /** Thumbnail — explicit override or YouTube hqdefault fallback. */
    public function thumbUrl(): ?string
    {
        if ($this->thumbnail) {
            return $this->thumbnail;
        }

        return $this->youtube_id
            ? 'https://img.youtube.com/vi/'.$this->youtube_id.'/hqdefault.jpg'
            : null;
    }

    public function levelColor(): string
    {
        return match ($this->level) {
            'beginner' => 'var(--green)',
            'base' => 'var(--blue)',
            default => 'var(--gold)',
        };
    }
}
