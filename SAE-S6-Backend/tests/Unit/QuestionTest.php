<?php
// tests/Unit/QuestionTest.php

namespace Tests\Unit;

use App\Models\Question;
use App\Models\Questionnaire;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuestionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_question()
    {
        $questionnaire = Questionnaire::factory()->create();
        $question = Question::factory()->create(['questionnaire_id' => $questionnaire->id]);

        $this->assertDatabaseHas('questions', [
            'title' => $question->title,
            'questionnaire_id' => $questionnaire->id,
        ]);
    }

    public function test_update_question()
    {
        $question = Question::factory()->create();
        $question->update(['title' => 'new_title']);

        $this->assertDatabaseHas('questions', [
            'title' => 'new_title',
        ]);
    }

    public function test_delete_question()
    {
        $question = Question::factory()->create();
        $question->delete();

        $this->assertDatabaseMissing('questions', [
            'id' => $question->id,
        ]);
    }

    public function test_read_question()
    {
        $question = Question::factory()->create();

        $this->assertEquals(Question::find($question->id)->title, $question->title);
    }
}
