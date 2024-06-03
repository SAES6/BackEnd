<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ChoiceController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\TokenController;

Route::apiResource('admin-users', AdminUserController::class);


Route::apiResource('questionnaires', QuestionnaireController::class);

// export des data 

Route::get('admin/exportData', [AdminUserController::class,'exportData']);

// launch a questionnaire
Route::put('questionnaires/launch', [QuestionnaireController::class, 'launch']);

Route::apiResource('questions', QuestionController::class);
Route::apiResource('choices', ChoiceController::class);
Route::apiResource('responses', ResponseController::class);



// login logout 
Route::post('users', [AdminUserController::class, 'store']);

Route::post('login', [AdminUserController::class, 'login']);
Route::middleware('auth:api')->get('me', [AdminUserController::class, 'me']);
Route::middleware('auth:api')->post('logout', [AdminUserController::class, 'logout']);

// Token

Route::get('createToken', [TokenController::class, 'createToken']);

// get questionnaires by user token
Route::get('questionnaire/byToken', [QuestionnaireController::class, 'getQuestionnaireByToken']);

Route::get('questionnaire/loadById', [QuestionnaireController::class, 'loadById']);


Route::get('admin/{id}', [AdminUserController::class, 'statQuestion']);
Route::get('user/{id}', [AdminUserController::class, 'statQuestionRecap']);
