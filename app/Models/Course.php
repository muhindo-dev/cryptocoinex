<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'category', 'description', 'short_description',
        'image', 'fee', 'outline',
        'level', 'duration_weeks', 'schedule', 'mode',
        'is_featured', 'is_active',
    ];

    protected $casts = [
        'outline' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
