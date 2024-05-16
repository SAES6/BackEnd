<?php
// app/Http/Controllers/AdminUserController.php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Response;
use Illuminate\Http\Request;
use ReturnTypeWillChange;
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

    public function destroy(AdminUser $adminUser)
    {
        $adminUser->delete();

        return response()->json(null, 204);
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
