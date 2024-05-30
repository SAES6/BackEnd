<?php
// app/Http/Controllers/AdminUserController.php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\AdminUser;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\Response;
use Illuminate\Http\Request;
use ReturnTypeWillChange;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Symfony\Component\HttpFoundation\StreamedResponse;


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
        if(auth()->user()->id){
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
        if(auth()->user()->id){
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
        if(auth()->user()->id){
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
        if(auth()->user()->id){
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
        if(auth()->user()->id){
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

        return response()->json(compact('token'));
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
}
