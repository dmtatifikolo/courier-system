<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BuyerContractController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\CoffeeController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\TransporterController;
use App\Http\Controllers\TransporterContractController;
use App\Http\Controllers\TransporterDriverController;
use App\Http\Controllers\TransporterVehicleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    $user = User::where('username', $request->username)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'username' => ['The provided credentials are incorrect.'],
        ]);
    }

    $user->tokens()->delete();

    $token = $user->createToken($request->username)->plainTextToken;

    return  ['token' => $token];
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('access/export-permission-data', [AccessController::class, 'exportPermissionData']);
    Route::get('/access/get-all-permissions', [AccessController::class, 'getAllPermissions']);
    Route::get('/access/get-permission/{id}', [AccessController::class, 'getPermission']);
    Route::post('/access/create-permission', [AccessController::class, 'createPermission']);
    Route::put('/access/update-permission', [AccessController::class, 'updatePermission']);
    Route::delete('/access/delete-permission/{id}', [AccessController::class, 'deletePermission']);

    Route::get('access/export-role-data', [AccessController::class, 'exportRoleData']);
    Route::get('/access/get-all-roles', [AccessController::class, 'getAllRoles']);
    Route::get('/access/get-role/{id}', [AccessController::class, 'getRole']);
    Route::post('/access/create-role', [AccessController::class, 'createRole']);
    Route::put('/access/update-role', [AccessController::class, 'updateRole']);
    Route::delete('/access/delete-role/{id}', [AccessController::class, 'deleteRole']);

    Route::post('/access/assign-permissions-to-role', [AccessController::class, 'assignPermissionsToRole']);
    Route::post('/access/assign-roles-to-user', [AccessController::class, 'assignRolesToUser']);
    Route::get('/access/get-user/{id}', [AccessController::class, 'getUser']);
    Route::get('/access/get-user-by-username/{username}', [AccessController::class, 'getUserByUsername']);
    
    //Route::get('user/export-data', [UserController::class, 'exportData']);
    Route::apiResource('user', 'App\Http\Controllers\UserController');

    Route::get('audit/export-data', [AuditController::class, 'exportData']);
    Route::apiResource('audit', 'App\Http\Controllers\AuditController');
});

