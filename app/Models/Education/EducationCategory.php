<?php

namespace App\Models\Education;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EducationCategory extends Model
{
    protected $table = 'education_categories';

    protected $fillable = ['name', 'slug', 'tagline', 'icon', 'accent', 'sort_order'];

    public function articles(): HasMany
    {
        return $this->hasMany(EducationArticle::class, 'category_id')->orderBy('sort_order');
    }
}
