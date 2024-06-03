<?php

// database/factories/QuestionFactory.php

namespace Database\Factories;

use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition()
    {
        return [
            'questionnaire_id' => Questionnaire::factory(),
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'img_src' => $this->faker->imageUrl,
            'type' => $this->faker->randomElement(['multiple_choice', 'single_choice', 'text', 'slider']),
            'slider_min' => $this->faker->optional()->numberBetween(1, 5),
            'slider_max' => $this->faker->optional()->numberBetween(6, 10),
            'section_id' => Section::factory(),
            'order' => $this->faker->numberBetween(1, 10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
