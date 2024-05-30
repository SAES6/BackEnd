<?php

// app/Http/Controllers/QuestionnaireController.php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use Illuminate\Http\Request;
use App\Models\Response;
use App\Models\Question;
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

    public function launch(Request $request)
    {
        $request->validate([
            'deployed' => 'required',
        ]);
        $questionnaire = Questionnaire::find($request->questionnaire_id);
        $questionnaire->update(['deployed' => $request->deployed]);
        return response()->json($questionnaire, 200);
    }
    
    public function show(Questionnaire $questionnaire)
    {
        return $questionnaire->with('questions');
    }


    public function loadById(Request $request)
    {
        $questions = Question::where('questionnaire_id', $request->id)->with('choices')->with('section')->get();
        return response()->json($questions, 200);
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

    public function getQuestionnaireByToken(Request $request)
    {
        $questionnaire = Questionnaire::all();
        $token = $request->token;
        foreach ($questionnaire as $q) {

            $questions = $q->questions;
            $q['number_of_questions'] = count($questions);
            foreach ($questions as $question) {
                $reponses = Response::where('question_id', $question->id)->get();
                foreach ($reponses as $reponse) {
                    if ($reponse->user_token == $token) {
                        $q['completed'] = true;
                    }
                }
            }
            if($q['completed'] == null){
                $q['completed'] = false;
            }

        }
        if($questionnaire->isEmpty()){
            return response()->json(['message' => 'No questionnaire found'], 404);
        }
        return response()->json($questionnaire, 200);
    }

    public function loadQuestionnairesAndSections()
    {
        if(auth()->user()->id){
            $questionnaires = Questionnaire::with('sections')->get();
            return response()->json($questionnaires, 200);
        }
        else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }


}
