<?php

use App\Http\Controllers\Api\FasilitasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// Route Login
Route::post('/login', function (Request $request) {
    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        return response()->json(['message' => 'Email atau password salah!'], 401);
    }

    return response()->json([
        'access_token' => $user->createToken('auth_token')->plainTextToken,
        'token_type' => 'Bearer',
    ]);
});

// Route yang butuh Login
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/mahasiswa', [FasilitasController::class, 'indexMahasiswa']);
    Route::post('/mahasiswa', [FasilitasController::class, 'storeMahasiswa']);
    Route::post('/mahasiswa/{id}', [FasilitasController::class, 'updateMahasiswa']);
    Route::delete('/mahasiswa/{id}', [FasilitasController::class, 'destroyMahasiswa']);

    Route::get('/peminjaman', [FasilitasController::class, 'indexPeminjaman']);
    Route::post('/peminjaman', [FasilitasController::class, 'storePeminjaman']);
    Route::put('/peminjaman/{id}', [FasilitasController::class, 'updatePeminjaman']);
    Route::delete('/peminjaman/{id}', [FasilitasController::class, 'destroyPeminjaman']);

    Route::get('/items', [FasilitasController::class, 'indexItems']);
    Route::post('/items', [FasilitasController::class, 'storeItems']);
    Route::put('/items/{id}', [FasilitasController::class, 'updateItems']);
    Route::delete('/items/{id}', [FasilitasController::class, 'destroyItems']);
});