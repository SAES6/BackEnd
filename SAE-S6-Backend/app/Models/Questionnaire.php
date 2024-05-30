<?php

// app/Models/Questionnaire.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Questionnaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'deployed',
        'duree',
        'description'
    ];

    public function questions()
    {
        return $this->hasMany(Question::class)->with('choices');
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
