<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kursus extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'thumbnail',
        'title',
        'short_description',
        'description',
        'category',
        'difficulty',
        'certificate',
        'status',
    ];

    public function prerequisites()
    {
        return $this->belongsToMany(
            Kursus::class,
            'kursus_prerequisites',
            'kursus_id',
            'prerequisite_kursus_id'
        );
    }

    public function modules()
    {
        return $this->hasMany(Module::class)->orderBy('order', 'asc');
    }

    public function userCourses()
    {
        return $this->hasMany(UserCourse::class, 'kursus_id');
    }

    public function getTotalPesertaAttribute()
    {
        return $this->userCourses()->count();
    }

    public function getPesertaAktifAttribute()
    {
        return $this->userCourses()->where('status', 'in_progress')->count();
    }

    public function getPesertaSelesaiAttribute()
    {
        return $this->userCourses()->where('status', 'completed')->count();
    }
}
