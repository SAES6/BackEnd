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
use App\Http\Controllers\StatsController;




Route::get('questionnaires/loadWithSections', [QuestionnaireController::class, 'loadQuestionnairesAndSections']);

Route::get('questions/loadBySection', [QuestionController::class, 'loadQestionsBySection']);

Route::post('questions/create', [QuestionController::class, 'createQuestion']);

// export des data 

Route::get('admin/exportData', [StatsController::class,'exportData']);

Route::get('admins/list', [AdminUserController::class,'getAdminDetails']);

Route::put('admin/updateUsername', [AdminUserController::class,'updateUsername']);
Route::put('admin/updatePassword', [AdminUserController::class,'updatePassword']);
Route::put('admin/updateEmail', [AdminUserController::class,'updateEmail']);
Route::post('admin/add', [AdminUserController::class,'createAdmin']);
Route::delete('admin/delete', [AdminUserController::class,'deleteAdmin']);

// launch a questionnaire
Route::put('questionnaires/launch', [QuestionnaireController::class, 'launch']);




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



Route::post('/response/{userToken}/{role}', [ResponseController::class, 'store']);


Route::get('stats/loadQuestions', [StatsController::class, 'statQuestion']);
Route::get('stat/users' , [StatsController::class, 'statUsers']);
Route::get('users/{id}/{userToken}' , [StatsController::class, 'statQuestionRecap']);

