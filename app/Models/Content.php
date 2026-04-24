<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasFactory, HasUuids;

    protected $casts = [
        'integrity_mode_enabled' => 'boolean',
        'require_fullscreen' => 'boolean',
        'max_violations' => 'integer',
    ];

    protected $fillable = [
        'module_id',
        'title',
        'type',
        'content',
        'integrity_mode_enabled',
        'require_fullscreen',
        'max_violations',
        'order'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class, 'content_id')->orderBy('order');
    }
}
