<?php
// app/Http/Controllers/ChoiceController.php

namespace App\Http\Controllers;

use App\Models\Choice;
use Illuminate\Http\Request;

class ChoiceController extends Controller
{
    public function index()
    {
        return Choice::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',         
        ]);

        $choice = Choice::create($request->all());

        return response()->json($choice, 201);
    }

    public function show(Choice $choice)
    {
        return $choice;
    }

    public function update(Request $request, Choice $choice)
    {
        $choice->update($request->all());

        return response()->json($choice, 200);
    }

    public function destroy(Choice $choice)
    {
        $choice->delete();

        return response()->json(null, 204);
    }
}
