<?php
// tests/Unit/ChoiceTest.php

namespace Tests\Unit;

use App\Models\Choice;
use App\Models\Question;
use Illuminate\Foundation\Testing\DatabaseTransactions; 
use Tests\TestCase;

class ChoiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_choice()
    {
        $question = Question::factory()->create();
        $choice = Choice::factory()->create(['question_id' => $question->id]);

        $this->assertDatabaseHas('choices', [
            'text' => $choice->text,
            'question_id' => $question->id,
        ]);
    }

    public function test_update_choice()
    {
        $choice = Choice::factory()->create();
        $choice->update(['text' => 'new_text']);

        $this->assertDatabaseHas('choices', [
            'text' => 'new_text',
        ]);
    }

    public function test_delete_choice()
    {
        $choice = Choice::factory()->create();
        $choice->delete();

        $this->assertDatabaseMissing('choices', [
            'id' => $choice->id,
        ]);
    }

    public function test_read_choice()
    {
        $choice = Choice::factory()->create();

        $this->assertEquals(Choice::find($choice->id)->text, $choice->text);
    }
}
