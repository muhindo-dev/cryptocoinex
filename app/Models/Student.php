<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'username',
        'dob',
        'location',
        'email',
        'phone',
        'course_interest',
        'goals',
        'agreed_terms',
        'status',
    ];

    protected $casts = [
        'agreed_terms' => 'boolean',
        'dob' => 'date',
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
