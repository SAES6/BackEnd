<?php
// app/Http/Controllers/ResponseController.php

namespace App\Http\Controllers;

use App\Models\Response;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    public function index()
    {
        return Response::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'response_text' => 'nullable|string',
            'choice_id' => 'nullable|exists:choices,id',
            'slider_value' => 'nullable|integer',
        ]);

        $response = Response::create($request->all());

        return response()->json($response, 201);
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
