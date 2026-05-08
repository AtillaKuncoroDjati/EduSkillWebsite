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
        'require_fullscreen'     => 'boolean',
        'max_violations'         => 'integer',
        'is_ai_generated'        => 'boolean',
        'ai_question_count'      => 'integer',
    ];

    protected $fillable = [
        'module_id',
        'title',
        'type',
        'content',
        'integrity_mode_enabled',
        'require_fullscreen',
        'max_violations',
        'is_ai_generated',
        'ai_question_count',
        'quiz_type',
        'grading_type',
        'pdf_path',
        'order',
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
