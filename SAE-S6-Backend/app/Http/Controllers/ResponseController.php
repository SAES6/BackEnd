<?php
// app/Http/Controllers/ResponseController.php

namespace App\Http\Controllers;

use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResponseController extends Controller
{
    public function index()
    {
        return Response::all();
    }

   public function store($userToken, $role = "", Request $request)
    {
        // Récupérer le body JSON et le décoder en tableau associatif
        $reponsesUser = $request->input('responses');

        // Initialiser un tableau pour stocker les réponses créées
        $responses = [];

        foreach ($reponsesUser as &$reponseUser) {
            // Ajouter les valeurs de role et user_token à chaque réponse
            $reponseUser['question_id'] = $reponseUser['questionId'];
            $reponseUser['role'] = $role;
            $reponseUser['user_token'] = $userToken;
            $reponseUser['choice_id'] = null;
            $reponseUser['response_text'] = null;

            if(gettype($reponseUser['value']) == "array") {
                foreach ($reponseUser['value'] as $value) {
                    $responsesSauvegarde= $reponseUser;
                    $responsesSauvegarde['choice_id'] = $value;
                    $validatedData = Validator::make($responsesSauvegarde, [
                        'question_id' => 'required|exists:questions,id',
                        'response_text' => 'nullable|string',
                        'choice_id' => 'nullable|exists:choices,id',
                        'slider_value' => 'nullable|integer',
                        'role' => 'nullable|string',
                        'user_token' => 'nullable|string'
                    ])->validate();
                    $response = Response::create($validatedData);
                    $responses[] = $response;
                }
            }
            else {
                if(gettype($reponseUser['value']) == "integer") {
                    $reponseUser['slider_value'] = $reponseUser['value'];
                }
                else if(gettype($reponseUser['value']) == "string") {
                    $reponseUser['response_text'] = $reponseUser['value'];
                }
                $validatedData = Validator::make($reponseUser, [
                    'question_id' => 'required|exists:questions,id',
                    'response_text' => 'nullable|string',
                    'choice_id' => 'nullable|exists:choices,id',
                    'slider_value' => 'nullable|integer',
                    'role' => 'nullable|string',
                    'user_token' => 'nullable|string'
                ])->validate();
                $response = Response::create($validatedData);
                $responses[] = $response;
            }
        }
        return response()->json(['message' => 'Responses received successfully', 'responses' => $responses], 201);
    }

    public function show(Response $response)
    {
        return $response;
    }

    public function update(Request $request, Response $response)
    {
        $request->validate([
            'response_text' => 'nullable|string',
            'choice_id' => 'nullable|exists:choices,id',
            'slider_value' => 'nullable|integer',
            'role' => 'required|string',
        ]);

        $response->update($request->all());

        return response()->json($response, 200);
    }

    public function destroy(Response $response)
    {
        $response->delete();

        return response()->json(null, 204);
    }
}
