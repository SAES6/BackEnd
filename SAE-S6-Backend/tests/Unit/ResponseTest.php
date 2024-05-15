<?php
// tests/Unit/ResponseTest.php

namespace Tests\Unit;

use App\Models\Response;
use App\Models\Question;
use App\Models\Choice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ResponseTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_response()
    {
        $question = Question::factory()->create();
        $choice = Choice::factory()->create(['question_id' => $question->id]);
        $response = Response::factory()->create([
            'question_id' => $question->id,
            'choice_id' => $choice->id,
        ]);

        $this->assertDatabaseHas('responses', [
            'question_id' => $response->question_id,
            'choice_id' => $response->choice_id,
        ]);
    }

    public function test_update_response()
    {
        $response = Response::factory()->create();
        $response->update(['response_text' => 'new_text']);

        $this->assertDatabaseHas('responses', [
            'response_text' => 'new_text',
        ]);
    }

    public function test_delete_response()
    {
        $response = Response::factory()->create();
        $response->delete();

        $this->assertDatabaseMissing('responses', [
            'id' => $response->id,
        ]);
    }

    public function test_read_response()
    {
        $response = Response::factory()->create();

        $this->assertEquals(Response::find($response->id)->response_text, $response->response_text);
    }
}
