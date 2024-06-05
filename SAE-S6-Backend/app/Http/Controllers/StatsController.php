<?php
// app/Http/Controllers/AdminUserController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;
use App\Models\Choice;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\Response;
use App\Models\Question;
use Illuminate\Http\Request;
use ReturnTypeWillChange;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use ModelNotFoundException;

class StatsController extends Controller
{


    public function loadResponseForOneUser(Request $request) {

        $questionnaireId = $request->query('id');
        $userToken = $request->query('user_token');
        $sectionId = $request->query('section_id');

        $questions = Question::where('questionnaire_id', $questionnaireId)->where('section_id', $sectionId)->get();
        if ($questions->isEmpty()) {
            return response()->json(['error' => 'No questions found'], 404);
        }
        else{
            $answers = array();
            foreach ($questions as $question) {
                if($question->type == 'multiple_choice' || $question->type == 'single_choice'){
                    $response = Response::where('question_id', $question->id)->where('user_token', $userToken)->get();
                    $choices = array();
                    foreach($response as $r){
                        if($r != null)
                        $choices[] = $r->choice_id;
                    }
                    $answers[] = [
                        'question_id' => $question->id,
                        'answer' => $choices
                    ];
                }
                else if( $question->type == 'slider'){
                    $response = Response::where('question_id', $question->id)->where('user_token', $userToken)->first();
                    if($response != null)
                    $answers[] = [
                        'question_id' => $question->id,
                        'answer' => $response->slider_value
                    ];
                }
                else{
                    $response = Response::where('question_id', $question->id)->where('user_token', $userToken)->first();
                    if($response != null)
                    $answers[] = [
                        'question_id' => $question->id,
                        'answer' => $response->response_text
                    ];
                }
                
            }
            return response()->json($answers, 200);

        }


        
        return response()->json($responses, 200);
    }
    public function statUsers(Request $request){
        $questions = Question::where('questionnaire_id', $request->id)->where('section_id', $request->section_id)->get();
        if ($questions->isEmpty()) {
            return response()->json(['error' => 'No questions found'], 404);
        }

        $totalResponses = Response::where('question_id', $questions[0]->id)->distinct('user_token')->get();
        $finalUsers = array();
        foreach($totalResponses as $response){
                          //recupere les 4 premiers caractères du token
                          $token = substr($response->user_token, 0, 4); 
            if($response->role == 'journalist'){
  
                $finalUsers[] = [
                    'user_token' => $response->user_token,
                    'user_name' => 'Journalist-'.$token,
                    'role' => $response->role
                ];   
            } else {

                $finalUsers[] = [
                    'user_token' => $response->user_token,
                    'user_name' => 'User-'.$token,
                    'role' => $response->role
                ];   
            }
        }
        return response()->json($finalUsers, 200);
    }
    public function exportData(Request $request) {
        $questionIds = Question::where('questionnaire_id', $request->query('questionnaire_id'))->pluck('id')->toArray();
        $responses = Response::with(['question', 'choice'])
            ->whereIn('question_id', $questionIds)
            ->get();
        
        $filename = 'responses.csv';
    
        $response = new StreamedResponse(function() use ($responses) {
            $handle = fopen('php://output', 'w');
    
            // Ajout d'un BOM UTF-8 pour indiquer l'encodage du fichier
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
            fputcsv($handle, ['Response ID', 'Question Libelle', 'Question Image','Question Description', 'Choice Value', 'Response Content', 'Created At'], ";");
    
            foreach ($responses as $response) {
                fputcsv($handle, [
                    $response->id,
                    $response->question->title ?? '',
                    $response->question->img_src ?? '',
                    $response->question->description ?? '',
                    $response->choice->text ?? '',
                    $response->response_text,
                    $response->created_at,
                ], ";");
            }
    
            fclose($handle);
        });
    
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
    
        return $response;
    }
    

    public function statQuestion(Request $request) {
        $questions = DB::table('questions')
            ->where('questions.questionnaire_id', $request->id)
            ->where('questions.section_id', $request->section_id)
            ->get();
            
        $resultArray = array();
        $totalResponses = DB::table('responses')
        ->where('responses.question_id', $questions[0]->id)
        ->distinct('user_token')
        ->count();

        $totalResponsesJournalist = DB::table('responses')
            ->where('responses.question_id', $questions[0]->id)
            ->distinct('user_token')
            ->where('responses.role', 'journalist')
            ->count();

        // Initialiser le résultat avec le total des réponses
        $resultArray['statsTypeUser'] = array(array("type" => "journalist", "total" => $totalResponsesJournalist), array("type" => "other", "total" =>  $totalResponses - $totalResponsesJournalist));
        $statsQuestions = array();
        // trie les questions par l'attribut order
        $questions = $questions->sortBy('order');
        foreach ($questions as $question) {

            
            if($question->type == 'multiple_choice' || $question->type== 'single_choice'){
                $choices = Choice::where('question_id', $question->id)->get();
                $question->choices = $choices;
            }

            

        
            $result = [
                'question' => $question,
                'stats' => [
                    'journalists' => [],
                    'others' => []
                ]
            ];
    
            // Vérifier le type de la question
            if ($question->type === 'slider') {
                // Récupérer les réponses des journalistes
                $journalistResponses = DB::table('responses')
                    ->select('responses.slider_value')
                    ->where('responses.question_id', $question->id)
                    ->where('responses.role', 'journalist')
                    ->pluck('slider_value');
    
                // Récupérer les réponses des autres utilisateurs
                $otherResponses = DB::table('responses')
                    ->select('responses.slider_value')
                    ->where('responses.question_id', $question->id)
                    ->where(function($query) {
                        $query->where('responses.role', '<>', 'journalist')
                              ->orWhereNull('responses.role')
                              ->orWhere('responses.role', '');
                    })
                    ->pluck('slider_value');
    
                // Calculer la moyenne et la médiane pour les journalistes
                $journalistMean = round($journalistResponses->avg(), 2);
                $journalistMedian = round($journalistResponses->median(), 2);
    
                // Calculer la moyenne et la médiane pour les autres
                $otherMean = round($otherResponses->avg(), 2);
                $otherMedian = round($otherResponses->median(), 2);
    
                // Ajouter les statistiques au résultat final
                $result['stats']['journalists'] = [
                    'mean' => $journalistMean,
                    'slider_max' => $question->slider_max,
                    'median' => $journalistMedian,
                    'responses' => $journalistResponses
                ];
                $result['stats']['others'] = [
                    'mean' => $otherMean,
                    'median' => $otherMedian,
                    'responses' => $otherResponses
                ];    
            }
            elseif ($question->type === 'text') { 
                $journalistResponses = DB::table('responses')
                ->select('responses.user_token', 'responses.response_text')
                ->where('responses.question_id', $question->id)
                ->where('responses.role', 'journalist')
                ->get();

                // Récupérer les réponses des autres utilisateurs
                $otherResponses = DB::table('responses')
                ->select('responses.user_token', 'responses.response_text')
                    ->where('responses.question_id', $question->id)
                    ->where(function($query) {
                        $query->where('responses.role', '<>', 'journalist')
                                ->orWhereNull('responses.role')
                                ->orWhere('responses.role', '');
                    })
                    ->get();
    
                // Ajouter les statistiques au résultat final
                $result['stats']['journalists'] = [
                    'responses' => $journalistResponses
                ];
                $result['stats']['others'] = [
                    'responses' => $otherResponses];
            }
            else {


                // Récupérer les statistiques des réponses groupées par type de choix pour les journalistes
                $journalistStats = DB::table('responses')
                    ->join('choices', 'responses.choice_id', '=', 'choices.id')
                    ->select('choices.id as choice_id', DB::raw('count(responses.id) as total'), 'choices.text as choice_text'  )
                    ->where('responses.question_id', $question->id)
                    ->where('responses.role', 'journalist')
                    ->groupBy('choices.id')
                    ->get();
    
                // Récupérer les statistiques des réponses groupées par type de choix pour les autres utilisateurs
                $otherStats = DB::table('responses')
                    ->join('choices', 'responses.choice_id', '=', 'choices.id')

                    ->select('choices.id as choice_id', DB::raw('count(responses.id) as total'), 'choices.text as choice_text')
                    ->where('responses.question_id', $question->id)
                    ->where(function($query) {
                        $query->where('responses.role', '<>', 'journalist')
                              ->orWhereNull('responses.role')
                              ->orWhere('responses.role', '');
                    })                
                    ->groupBy('choices.id')
                    ->get();
    
                // Ajouter les stats au résultat final
                $result['stats']['journalists'] = $journalistStats;
                $result['stats']['others'] = $otherStats;
            }
    
            $statsQuestions[] = $result;
        }
        $resultArray["statsQuestions"] = $statsQuestions;
        
        return response()->json($resultArray, 200, [], JSON_PRETTY_PRINT);
    }
    public function statQuestionRecap($id, $userToken="a") {
        $questions = DB::table('questions')
            ->where('questions.questionnaire_id', $id)
            ->get();

        $totalJournalists = DB::table('responses')
            ->where('responses.question_id', $questions[0]->id)
            ->distinct('user_token')
            ->where('responses.role', 'journalist')
            ->count();

        $totalOthers = DB::table('responses')
        ->where('responses.question_id', $questions[0]->id)
        ->distinct('user_token')
        ->count() - $totalJournalists;
            
        $totalJournalists = $totalJournalists == 0 ? 1: $totalJournalists;               
        $totalOthers = $totalOthers == 0 ? 1: $totalOthers;
        $resultArray = array();

        // Initialiser le résultat avec le total des réponses
        $statsQuestions = array();
        foreach ($questions as $question) {
            $result = [
                'question' => $question,
                'stats' => [
                    'journalists' => [],
                    'others' => []
                ]
            ];
    
            // Vérifier le type de la question
            if ($question->type === 'slider') {
                // Récupérer les réponses des journalistes
                $journalistResponses = DB::table('responses')
                    ->select('responses.slider_value')
                    ->where('responses.question_id', $question->id)
                    ->where('responses.role', 'journalist')
                    ->pluck('slider_value');
    
                // Récupérer les réponses des autres utilisateurs
                $otherResponses = DB::table('responses')
                    ->select('responses.slider_value')
                    ->where('responses.question_id', $question->id)
                    ->where(function($query) {
                        $query->where('responses.role', '<>', 'journalist')
                              ->orWhereNull('responses.role')
                              ->orWhere('responses.role', '');
                    })
                    ->pluck('slider_value');
    
                // Calculer la moyenne et la médiane pour les journalistes
                $journalistMean = round($journalistResponses->avg(), 2);
    
                // Calculer la moyenne et la médiane pour les autres
                $otherMean = round($otherResponses->avg(), 2);
    
                // Ajouter les statistiques au résultat final
                $result['stats']['journalists'] = [
                    'mean' => $journalistMean,
                ];
                $result['stats']['others'] = [
                    'mean' => $otherMean,
                ];    
            }
            else if ($question->type === 'multiple_choice' || $question->type === 'single_choice') { 
                $userResponses = DB::table('responses')
                ->where('responses.question_id', $question->id)
                ->where('responses.user_token', $userToken)
                ->get();

                for( $i = 0; $i < count($userResponses); $i++ ) {
                    $journalistResponses = DB::table('responses')
                    ->where('responses.choice_id', $userResponses[$i]->choice_id)
                    ->where('responses.role', 'journalist')
                    ->count();


                    $otherResponses = DB::table('responses')
                    ->where('responses.choice_id', $userResponses[$i]->choice_id)
                    ->count() - $journalistResponses ;
                    // $choiceText = $userResponses[$i]->choice->text;
                  

                    $result['stats']['journalists'][] = [
                        // 'choice' => $choiceText,
                        'choice_id' => $userResponses[$i]->choice_id,
                        'total' => round($journalistResponses / $totalJournalists,2)*100,
                        'pourcentage' => $journalistResponses
                    ];
                    $result['stats']['others'][] = [
                        // 'choice' => $choiceText,
                        'choice_id' => $userResponses[$i]->choice_id,
                        'total' => round($otherResponses / $totalOthers,2)*100,
                        'pourcentage' => $otherResponses

                    ];

                    
                }
            }
            $statsQuestions[] = $result;
            $resultArray["statsQuestions"] = $statsQuestions;
        }
        return response()->json($resultArray, 200, [], JSON_PRETTY_PRINT);
    }
}