<?php

// app/Http/Controllers/QuestionController.php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use App\Http\Controllers\ImageController;


class QuestionController extends Controller
{


    public function createQuestion(Request $request)
    {
        if(auth()->user()){
        $request->validate([
            'section_id' => 'required',
            'questionnaire_id' => 'required',
            'type' => 'required',
            'title' => 'required',
            'description' => 'required',
            'order' => 'required',
        ]);
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = (new ImageController)->uploadImage($request);
        }
        $sliderMin = $request->sliderMin ?? null;
        $sliderMax = $request->sliderMax ?? null;
        $sliderGap = $request->sliderGap ?? null;
        $newType = $request->type;
        if($request->type == 'Curseur'){
            $newType = 'slider';
        }
        else if($request->type == 'Choix multiple'){
            $newType = 'multiple_choice';
        }
        else if($request->type == 'Choix unique'){
            $newType = 'single_choice';
        }
        else if($request->type == 'Libre'){
            $newType = 'text';
        }
        $question = Question::create([
            'question' => $request->question,
            'section_id' => $request->section_id,
            'questionnaire_id' => $request->questionnaire_id,
            'type' => $newType,
            'img_src' => $imageUrl,
            'title' => $request->title,
            'description' => $request->description,
            'order' => $request->order,
            'slider_min' => $sliderMin,
            'slider_max' => $sliderMax,
            'slider_gap' => $sliderGap,
        ]);
        return response()->json($question, 201);
    }
    else{
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    }





    public function loadQestionsBySection(Request $request)
    {
        if(auth()->user()){
            $section_id = $request->section_id;
            $questions = Question::where('section_id', $section_id)->with('choices')->get();
            foreach ($questions as $question) {
                if($question->img_src != null) $question->img_src = (new ImageController)->generateSignedUrl($question->img_src);
                if($question->type == 'slider'){
                    $question->type = 'Curseur';
                }
                else if($question->type == 'multiple_choice'){
                    $question->type = 'Choix multiple';
                }
                else if($question->type == 'single_choice'){
                    $question->type = 'Choix unique';
                }
                else if($question->type == 'text'){
                    $question->type = 'Libre';
                }
            }
            return response()->json($questions, 200);
        }
        else{
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
