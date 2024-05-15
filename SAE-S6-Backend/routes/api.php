<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ChoiceController;
use App\Http\Controllers\ResponseController;

Route::apiResource('admin-users', AdminUserController::class);
Route::apiResource('questionnaires', QuestionnaireController::class);

// export des data 

Route::get('admin/exportData', [AdminUserController::class,'exportData']);
Route::apiResource('questions', QuestionController::class);
Route::apiResource('choices', ChoiceController::class);
Route::apiResource('responses', ResponseController::class);

