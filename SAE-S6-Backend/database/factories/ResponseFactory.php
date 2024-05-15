<?php
// database/factories/ResponseFactory.php

namespace Database\Factories;

use App\Models\Response;
use App\Models\Question;
use App\Models\Choice;
use Illuminate\Database\Eloquent\Factories\Factory;

class ResponseFactory extends Factory
{
    protected $model = Response::class;

    public function definition()
    {
        return [
            'question_id' => Question::factory(),
            'response_text' => $this->faker->optional()->text,
            'choice_id' => $this->faker->optional()->randomElement([Choice::factory()]),
            'slider_value' => $this->faker->optional()->numberBetween(1, 10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
