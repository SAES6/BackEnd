<?php

// app/Http/Controllers/QuestionnaireController.php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use Illuminate\Http\Request;

class QuestionnaireController extends Controller
{
    public function index()
    {
        return Questionnaire::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $questionnaire = Questionnaire::create($request->all());

        return response()->json($questionnaire, 201);
    }

    public function show(Questionnaire $questionnaire)
    {
        return $questionnaire;
    }

    public function update(Request $request, Questionnaire $questionnaire)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $questionnaire->update($request->all());

        return response()->json($questionnaire, 200);
    }

    public function destroy(Questionnaire $questionnaire)
    {
        $questionnaire->delete();

        return response()->json(null, 204);
    }
}
