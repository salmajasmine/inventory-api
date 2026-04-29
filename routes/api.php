<?php

use App\Http\Controllers\Api\FasilitasController;

// Routes Mahasiswa
Route::get('/mahasiswa', [FasilitasController::class, 'indexMahasiswa']);
Route::post('/mahasiswa', [FasilitasController::class, 'storeMahasiswa']);
Route::put('/mahasiswa/{id}', [FasilitasController::class, 'updateMahasiswa']);
Route::delete('/mahasiswa/{id}', [FasilitasController::class, 'destroyMahasiswa']);

// Routes Peminjaman
Route::get('/pinjam', [FasilitasController::class, 'indexPeminjaman']);
Route::post('/pinjam', [FasilitasController::class, 'storePeminjaman']);
Route::put('/pinjam/{id}', [FasilitasController::class, 'updatePeminjaman']); // Untuk balikin barang

// Routes Items (Maintenance)
Route::get('/items', [FasilitasController::class, 'indexItems']);
Route::post('/items', [FasilitasController::class, 'storeItems']);
Route::put('/items/{id}', [FasilitasController::class, 'updateItems']);
Route::delete('/items/{id}', [FasilitasController::class, 'destroyItems']);