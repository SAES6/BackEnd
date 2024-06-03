<?php
// app/Http/Controllers/AdminUserController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;
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


class AdminUserController extends Controller
{
    public function index()
    {
        return AdminUser::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required',
            'username' => 'required|unique:admin_users,username',
        ]);

        $adminUser = AdminUser::create([
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'username' => $request->username,
        ]);

        return response()->json($adminUser, 201);
    }

    public function show(AdminUser $adminUser)
    {
        return $adminUser;
    }

    public function update(Request $request, AdminUser $adminUser)
    {
        $request->validate([
            'email' => 'required|email|unique:admin_users,email,' . $adminUser->id,
            'username' => 'required|unique:admin_users,username,' . $adminUser->id,
        ]);

        $adminUser->update($request->all());

        return response()->json($adminUser, 200);
    }

    public function updateUsername(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'id' => 'required',
        ]);
        if(auth()->user()){
            $adminUser = AdminUser::find($request->id);
            $adminUser->update(['username' => $request->username]);
            return response()->json($adminUser, 200);
        }
        else{
            return response()->json(['error' => 'Non autorisé'], 404);
        }
    }

    public function updateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'id' => 'required',
        ]);
        if(auth()->user()){
            $adminUser = AdminUser::find($request->id);
            $adminUser->update(['email' => $request->email]);
            return response()->json($adminUser, 200);
        }
        else{
            return response()->json(['error' => 'Non autorisé'], 404);
        }
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'id' => 'required',
        ]);
        if(auth()->user()){
            $adminUser = AdminUser::find($request->id);
            $adminUser->update(['password' => bcrypt($request->password)]);
            return response()->json($adminUser, 200);
        }
        else{
            return response()->json(['error' => 'Non autorisé'], 404);
        }
    }

    public function createAdmin(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required',
            'username' => 'required|unique:admin_users,username',
        ]);
        if(auth()->user()){
            $adminUser = AdminUser::create([
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'username' => $request->username,
            ]);
            return response()->json($adminUser, 201);
        }
        else{
            return response()->json(['error' => 'Non autorisé'], 404);
        }
    }

    public function deleteAdmin(Request $request)
    {
        $request->validate([
            'id' => 'required',
        ]);
        if(auth()->user()){
            $adminUser = AdminUser::find($request->id);
            $adminUser->delete();
            if($adminUser->id == auth()->user()->id){
                return response()->json("deconnect", 200);
            }
            return response()->json(null, 204);
        }
        else{
            return response()->json(['error' => 'Non autorisé'], 404);
        }
    }


    public function destroy(AdminUser $adminUser)
    {
        $adminUser->delete();

        return response()->json(null, 204);
    }
  
  
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }

        // Je veux que ça renvoie le mail lié au token
        $user = Auth::user();
        $token = JWTAuth::fromUser($user);

        // renvoie le token et l'utilisateur
        return response()->json(([ 'token' => $token, 'user' => $user]));



    }

    public function me()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
    public function getAdminDetails(Request $request)
{
    try {
        $user = JWTAuth::parseToken()->authenticate();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        // Récupérer l'utilisateur courant
        $currentAdmin = AdminUser::where('id', $user->id)->first();
        // Ajouter le mot de passe décrypté aux données de l'utilisateur courant
        $currentAdminData = array();
        $currentAdminData[] = [
            'id' => $currentAdmin->id,
            'email' => $currentAdmin->email,
            'username' => $currentAdmin->username,
            'current' => true,
            'principal' => $currentAdmin->principal,
        ];

        // Récupérer tous les autres administrateurs
        $allAdmins = AdminUser::where('id', '!=', $currentAdmin->id)->get();
        foreach ($allAdmins as $admin) {
            $currentAdminData[] = [
                'id' => $admin->id,
                'email' => $admin->email,
                'username' => $admin->username,
                'current' => false,
                'principal' => $admin->principal,
            ];
        }


        return response()->json($currentAdminData, 200);

    } catch (TokenExpiredException $e) {
        return response()->json(['error' => 'Token has expired'], 401);
    } catch (TokenInvalidException $e) {
        return response()->json(['error' => 'Token is invalid'], 401);
    } catch (JWTException $e) {
        return response()->json(['error' => 'Token is absent'], 401);
    } catch (Exception $e) {
        return response()->json(['error' => 'Could not fetch details', 'exception' => $e->getMessage()], 500);
    }
}




    public function exportData(Request $request) {
        // Récupérer toutes les instances de Response avec les relations 'question' et 'choice'
        $responses = Response::with(['question', 'choice'])->get();

        // Définir le nom du fichier CSV
        $filename = 'responses.csv';

        // Créer une réponse en flux (streamed response)
        $response = new StreamedResponse(function() use ($responses) {
            // Ouvrir le flux en écriture
            $handle = fopen('php://output', 'w');

            // Ajouter l'en-tête du fichier CSV
            fputcsv($handle, ['Response ID', 'Question Libelle', 'Question Description',  'Choice Value', 'Response Content', 'Created At'], ";");

            // Ajouter les données des réponses
            foreach ($responses as $response) {
                fputcsv($handle, [
                    $response->id,
                    $response->question->title ?? '', // Accès au libelle de la question
                    $response->question->description ?? '', // Accès au libelle de la question
                    $response->choice->text ?? '', // Accès à la valeur du choix
                    $response->response_text,
                    $response->created_at,
                ], ";");
            }

            // Fermer le flux
            fclose($handle);
        });

        // Définir les en-têtes HTTP pour forcer le téléchargement
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
    public function statQuestion($id) {
        $questions = DB::table('questions')
            ->where('questions.questionnaire_id', $id)
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
                    ->select('choices.id as choice_id', DB::raw('count(responses.id) as total'))
                    ->where('responses.question_id', $question->id)
                    ->where('responses.role', 'journalist')
                    ->groupBy('choices.id')
                    ->get();
    
                // Récupérer les statistiques des réponses groupées par type de choix pour les autres utilisateurs
                $otherStats = DB::table('responses')
                    ->join('choices', 'responses.choice_id', '=', 'choices.id')
                    ->select('choices.id as choice_id', DB::raw('count(responses.id) as total'))
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

                    $result['stats']['journalists'][$userResponses[$i]->choice_id] = array($journalistResponses,round($journalistResponses/$totalJournalists, 2) *100);
                    $result['stats']['others'][$userResponses[$i]->choice_id] = array($otherResponses,round($otherResponses/$totalOthers,2) * 100);
                }
            }
            $statsQuestions[] = $result;
            $resultArray["statsQuestions"] = $statsQuestions;
        }
        return response()->json($resultArray, 200, [], JSON_PRETTY_PRINT);
    }

  
}
