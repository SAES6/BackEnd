<?php

// database/factories/ChoiceFactory.php

namespace Database\Factories;

use App\Models\Choice;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChoiceFactory extends Factory
{
    protected $model = Choice::class;

    public function definition()
    {
        return [
            'question_id' => Question::factory(),
            'text' => $this->faker->sentence,
            'image_src' => $this->faker->imageUrl(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
