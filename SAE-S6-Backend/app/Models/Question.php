<?php
// app/Models/Question.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'questionnaire_id', 'title', 'description', 'img_src', 'type', 'slider_min', 'slider_max', 'page', 'order', 'slider_gap'
    ];

    public function questionnaire()
    {
        return $this->belongsTo(Questionnaire::class);
    }

    public function choices()
    {
        return $this->hasMany(Choice::class);
    }

    public function responses()
    {
        return $this->hasMany(Response::class);
    }
}
