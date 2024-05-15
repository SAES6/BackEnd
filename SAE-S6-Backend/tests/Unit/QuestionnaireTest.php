<?php

// tests/Unit/QuestionnaireTest.php

namespace Tests\Unit;

use App\Models\Questionnaire;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class QuestionnaireTest extends TestCase
{
    use DatabaseTransactions;

    public function test_create_questionnaire()
    {
        $questionnaire = Questionnaire::factory()->create();

        $this->assertDatabaseHas('questionnaires', [
            'name' => $questionnaire->name,
        ]);
    }

    public function test_update_questionnaire()
    {
        $questionnaire = Questionnaire::factory()->create();
        $questionnaire->update(['name' => 'new_name']);

        $this->assertDatabaseHas('questionnaires', [
            'name' => 'new_name',
        ]);
    }

    public function test_delete_questionnaire()
    {
        $questionnaire = Questionnaire::factory()->create();
        $questionnaire->delete();

        $this->assertDatabaseMissing('questionnaires', [
            'id' => $questionnaire->id,
        ]);
    }

    public function test_read_questionnaire()
    {
        $questionnaire = Questionnaire::factory()->create();

        $this->assertEquals(Questionnaire::find($questionnaire->id)->name, $questionnaire->name);
    }
}
