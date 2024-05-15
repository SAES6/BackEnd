<?php

// database/factories/QuestionnaireFactory.php

namespace Database\Factories;

use App\Models\Questionnaire;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionnaireFactory extends Factory
{
    protected $model = Questionnaire::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}