<?php

// app/Http/Controllers/QuestionController.php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use App\Http\Controllers\ImageController;
use App\Models\Section;
use App\Models\Choice;


class QuestionController extends Controller
{




    public function saveSection(Request $request)
    {
        if (auth()->user()) {
            $sectionId = $request->input('section_id');
            $questionsList = is_string($request->input('questions')) ? json_decode($request->input('questions'), true) : $request->input('questions');
            $questionnaireInfos = is_string($request->input('quizInfos')) ? json_decode($request->input('quizInfos'), true) : $request->input('quizInfos');
    
            $questionnaireId = $questionsList[0]['questionnaire_id'];
            $questionnaire = Questionnaire::find($questionnaireId);
    
            if ($questionnaire == null) {
                $newQuiz = Questionnaire::create([
                    'name' => $questionnaireInfos['name'],
                    'description' => $questionnaireInfos['description'],
                    'duree' => $questionnaireInfos['duree'],
                    'deployed' => false,
                ]);
                $questionnaireId = $newQuiz->id;
            } else {
                $questionnaire->name = $questionnaireInfos['name'];
                $questionnaire->description = $questionnaireInfos['description'];
                $questionnaire->duree = $questionnaireInfos['duree'];
                $questionnaire->save();
            }
    
            $section = Section::find($sectionId);
            if ($section == null) {
                $section = Section::create([
                    'name' => $request->input('title'),
                    'order' => $request->input('order'),
                    'questionnaire_id' => $questionnaireId,
                ]);
            } else {
                $section->name = $request->input('title');
                $section->questionnaire_id = $questionnaireId;
                $section->order = $request->input('order');
                $section->save();
            }
    
            foreach ($questionsList as $questionIndex => $question) {
                $imageUrl = null;
                if (isset($question['image_src']) && $request->hasFile('questions.' . $questionIndex . '.image_src.file')) {
                    $image = $request->file('questions.' . $questionIndex . '.image_src.file');
                    if ($image && $image->isValid()) {
                        $imageUrl = (new ImageController)->uploadImage($image);
                    }
                } else if (isset($question['img_src']) && $question['img_src'] != null) {
                    $imageUrl = $question['img_src'];
                }
    
                $newType = $this->mapQuestionType($question['type']);
                $isQuestion = Question::find($question['id']);
                if ($isQuestion == null) {
                    $newQuestion = Question::create([
                        'section_id' => $section->id,
                        'questionnaire_id' => $questionnaireId,
                        'type' => $newType,
                        'img_src' => $imageUrl,
                        'title' => $question['title'],
                        'description' => $question['description'],
                        'order' => $question['order'],
                        'slider_min' => $question['slider_min'] == 'null' ? NULL : $question['slider_min'],
                        'slider_max' => $question['slider_max'] == 'null' ? NULL : $question['slider_max'],
                        'slider_gap' => $question['slider_gap'] == 'null' ? NULL : $question['slider_gap'],
                    ]);
                    $this->saveChoices($request, $newQuestion, $question['choices']);
                } else {
                    $isQuestion->update([
                        'section_id' => $section->id,
                        'questionnaire_id' => $questionnaireId,
                        'type' => $newType,
                        'img_src' => $imageUrl,
                        'title' => $question['title'],
                        'description' => $question['description'],
                        'order' => $question['order'],
                        'slider_min' => $question['slider_min'] == 'null' ? NULL : $question['slider_min'],
                        'slider_max' => $question['slider_max'] == 'null' ? NULL : $question['slider_max'],
                        'slider_gap' => $question['slider_gap'] == 'null' ? NULL : $question['slider_gap'],
                    ]);
                    $isQuestion->save();
                    if(isset($question['choices'])) $this->updateChoices($request, $isQuestion, $question['choices'], $questionIndex);
                }
            }
    
            return response()->json(['message' => 'success'], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
    
    private function mapQuestionType($type)
    {
        $mapping = [
            'Curseur' => 'slider',
            'Choix multiple' => 'multiple_choice',
            'Choix unique' => 'single_choice',
            'Libre' => 'text',
        ];
        return $mapping[$type] ?? $type;
    }
    
    private function saveChoices($request, $question, $choices, $questionIndex)
    {
        foreach ($choices as $choiceIndex => $choice) {
            $imageUrl = null;
            if (isset($choice['image_src']) && $request->hasFile('questions.' . $questionIndex . '.choices.' . $choiceIndex . '.image_src.file')) {
                $image = $request->file('questions.' . $questionIndex . '.choices.' . $choiceIndex . '.image_src.file');
                if ($image && $image->isValid()) {
                    $imageUrl = (new ImageController)->uploadImage($image);
                }
            } else if (isset($choice['img_src']) && $choice['img_src'] != null) {
                $imageUrl = $choice['img_src'];
            }
            Choice::create([
                'question_id' => $question->id,
                'text' => $choice['text'],
                'image_src' => $imageUrl,
                'order' => $choice['order'],
            ]);
        }
    }
    
    private function updateChoices($request, $question, $choices, $questionIndex)
    {
        $allChoices = Choice::where('question_id', $question->id)->get();
        foreach ($allChoices as $choice) {
            if (!in_array($choice->id, array_column($choices, 'id'))) {
                $choice->delete();
            }
        }
    
        foreach ($choices as $choiceIndex => $choice) {
            $isChoice = Choice::find($choice['id']);
            $imageUrl = null;
            if (isset($choice['image_src']) && $request->hasFile('questions.' . $questionIndex . '.choices.' . $choiceIndex . '.image_src.file')) {
                $image = $request->file('questions.' . $questionIndex . '.choices.' . $choiceIndex . '.image_src.file');
                if ($image && $image->isValid()) {
                    $imageUrl = (new ImageController)->uploadImage($image);
                }
            } else if (isset($choice['img_src']) && $choice['img_src'] != null) {
                $imageUrl = $choice['img_src'];
            }
            if ($isChoice == null) {
                Choice::create([
                    'question_id' => $question->id,
                    'text' => $choice['text'],
                    'image_src' => $imageUrl,
                    'order' => $choice['order'],
                ]);
            } else {
                $isChoice->update([
                    'question_id' => $question->id,
                    'text' => $choice['text'],
                    'image_src' => $imageUrl,
                    'order' => $choice['order'],
                ]);
            }
        }
    }
    





    public function loadQestionsBySection(Request $request)
    {
        if(auth()->user()){
            $section_id = $request->section_id;
            $questions = Question::where('section_id', $section_id)->with('choices')->with('section')->get();
            foreach ($questions as $question) {
                $question->section_order = $question->section->order;
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
