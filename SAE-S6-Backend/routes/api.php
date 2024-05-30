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




Route::get('questionnaires/loadWithSections', [QuestionnaireController::class, 'loadQuestionnairesAndSections']);

Route::get('questions/loadBySection', [QuestionController::class, 'loadQestionsBySection']);

// export des data 

Route::get('admin/exportData', [AdminUserController::class,'exportData']);

Route::get('admins/list', [AdminUserController::class,'getAdminDetails']);

Route::put('admin/updateUsername', [AdminUserController::class,'updateUsername']);
Route::put('admin/updatePassword', [AdminUserController::class,'updatePassword']);
Route::put('admin/updateEmail', [AdminUserController::class,'updateEmail']);
Route::post('admin/add', [AdminUserController::class,'createAdmin']);
Route::delete('admin/delete', [AdminUserController::class,'deleteAdmin']);

// launch a questionnaire
Route::put('questionnaires/launch', [QuestionnaireController::class, 'launch']);



// login logout 

Route::post('login', [AdminUserController::class, 'login']);
Route::middleware('auth:api')->get('me', [AdminUserController::class, 'me']);
Route::middleware('auth:api')->post('logout', [AdminUserController::class, 'logout']);

// Token

Route::get('createToken', [TokenController::class, 'createToken']);

// get questionnaires by user token
Route::get('questionnaire/byToken', [QuestionnaireController::class, 'getQuestionnaireByToken']);

Route::get('questionnaire/loadById', [QuestionnaireController::class, 'loadById']);
