<?php

// app/Http/Controllers/QuestionnaireController.php

namespace App\Http\Controllers;

use App\Models\Questionnaire;
use Illuminate\Http\Request;
use App\Models\Response;
use App\Models\Question;
use App\Models\Section;
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
        foreach ($questions as $question) {
            foreach ($question->choices as $choice) {
                if($choice->image_src != null) $choice->image_src = (new ImageController)->generateSignedUrl($choice->image_src);
            }
            if($question->img_src != null) $question->img_src = (new ImageController)->generateSignedUrl($question->img_src);

        }
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

    public function loadQuestionnairesAndSections(Request $request)
    {
        if(auth()->user()){
            $questionnaires = Questionnaire::with('sections')->get();
            // Je veux trier les sections par leur attribut order dans chaque questionnaire et dans l'order croissant
            foreach ($questionnaires as $questionnaire) {
                $sections = $questionnaire->sections;
                $sections = $sections->sortBy('order');
                $questionnaire['sections'] = $sections;
            }
            return response()->json($questionnaires, 200);
        }
        else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteSection (Request $request)
    {
    if(auth()->user()){
        $section = Section::find($request->id);
        $order = $section->order;
        $allSection = Section::where('questionnaire_id', $section->questionnaire_id)->get();
        foreach ($allSection as $s) {
            if($s->order > $order){
                $s->update(['order' => $s->order - 1]);
            }
        }
        $section->delete();
        return response()->json(null, 204);
        }
        else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function updateSectionName(Request $request)
    {
    if(auth()->user()){
        $section = Section::find($request->id);
        $section->update(['name' => $request->name]);
        return response()->json($section, 200);
        }
        else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function deleteQuestionnaire (Request $request)
    {
    if(auth()->user()){
        $questionnaire = Questionnaire::find($request->id);
        $sections = Section::where('questionnaire_id', $questionnaire->id)->get();
        foreach ($sections as $section) {
            $section->delete();
        }
        $questionnaire->delete();
        return response()->json(null, 204);
        }
        else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

    public function updateQuestionnaireName(Request $request)
    {
    if(auth()->user()){
        $questionnaire = Questionnaire::find($request->id);
        $questionnaire->update(['name' => $request->name]);
        return response()->json($questionnaire, 200);
        }
        else{
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }

}
