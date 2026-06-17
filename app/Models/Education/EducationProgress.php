<?php

namespace App\Models\Education;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EducationProgress extends Model
{
    public $timestamps = false;

    protected $table = 'education_progress';

    protected $fillable = ['user_id', 'article_id', 'completed_at'];

    protected $casts = ['completed_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(EducationArticle::class, 'article_id');
    }
}
