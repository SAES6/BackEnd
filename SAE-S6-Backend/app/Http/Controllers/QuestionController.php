<?php

// app/Http/Controllers/QuestionController.php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index()
    {
        return Question::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'questionnaire_id' => 'required|exists:questionnaires,id',
            'title' => 'required',
            'type' => 'required|in:multiple_choice,single_choice,text,slider',
            'page' => 'required|integer',
            'order' => 'required|integer',
        ]);

        $question = Question::create($request->all());

        return response()->json($question, 201);
    }

    public function show(Question $question)
    {
        return $question;
    }

    public function update(Request $request, Question $question)
    {
        $request->validate([
            'title' => 'required',
            'type' => 'required|in:multiple_choice,single_choice,text,slider',
            'page' => 'required|integer',
            'order' => 'required|integer',
        ]);

        $question->update($request->all());

        return response()->json($question, 200);
    }

    public function destroy(Question $question)
    {
        $question->delete();

        return response()->json(null, 204);
    }
}
