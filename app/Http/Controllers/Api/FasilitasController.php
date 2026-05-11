<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Peminjaman;
use App\Models\Items; 
use Illuminate\Http\Request;

class FasilitasController extends Controller {
    
    // --- CRUD MAHASISWA (Table 1) ---
    
    public function indexMahasiswa() {
        return response()->json(Mahasiswa::all());
    }


    public function storeMahasiswa(Request $request) {

    $request->validate([
        'nim' => 'required|unique:mahasiswa',
        'nama' => 'required',
        'prodi' => 'required',
        'ktp_ktm' => 'required|file'
    ]);

    $fileName = null;

    if ($request->hasFile('ktp_ktm')) {

        $file = $request->file('ktp_ktm');

        $fileName = time() . '_' . $file->getClientOriginalName();

        $file->move(public_path('uploads'), $fileName);
    }

    $mahasiswa = Mahasiswa::create([
        'nim' => $request->nim,
        'nama' => $request->nama,
        'prodi' => $request->prodi,
        'ktp_ktm' => $fileName
    ]);

    return response()->json([
        'message' => 'Mahasiswa berhasil ditambah!',
        'data' => $mahasiswa
    ], 201);
}

    // Update pakai ID
public function updateMahasiswa(Request $request, $id) {
    $mahasiswa = Mahasiswa::findOrFail($id); // Cari berdasarkan ID (1, 2, 3...)
    $request->validate([
        'nim' => 'required|unique:mahasiswa,nim,' . $id, // Abaikan pengecekan unique untuk ID ini sendiri
        'nama' => 'required',
    ]);
    $mahasiswa->update($request->all());
    return response()->json(['message' => 'Data berhasil diubah!', 'data' => $mahasiswa]);
}

// Delete pakai ID
public function destroyMahasiswa($id) {
    Mahasiswa::destroy($id);
    return response()->json(['message' => 'Mahasiswa dihapus!']);
}

    // --- CRUD PEMINJAMAN (Table 2) ---

    public function indexPeminjaman() {
        return response()->json(Peminjaman::all());
    }

    public function storePeminjaman(Request $request) {

    $request->validate([
        'nim' => 'required',
        'nama_barang' => 'required',
        'kelas' => 'required'
    ]);

    $pinjam = Peminjaman::create([
        'nim' => $request->nim,
        'nama_barang' => $request->nama_barang,
        'kelas' => $request->kelas,
        'waktu_peminjaman' => now(),
    ]);

    return response()->json([
        'message' => 'Peminjaman berhasil!',
        'data' => $pinjam
    ]);
}

    public function updatePeminjaman($id) {
        $pinjam = Peminjaman::findOrFail($id);
        $pinjam->update(['waktu_pengembalian' => now()]);
        return response()->json(['message' => 'Barang sudah dikembalikan!', 'data' => $pinjam]);
    }

    public function destroyPeminjaman($id) {
        Peminjaman::destroy($id);
        return response()->json(['message' => 'Data peminjaman dihapus!']);
    }

    // --- CRUD ITEMS/MAINTENANCE (Table 3) ---

    public function indexItems() {
        return response()->json(Items::all());
    }

    public function storeItems(Request $request) {
        $request->validate([
            'kelas' => 'required',
            'nama_item' => 'required',
            'tipe_maintenance' => 'required'
        ]);

        $item = Items::create([
            'kelas' => $request->kelas,
            'nama_item' => $request->nama_item,
            'tipe_maintenance' => $request->tipe_maintenance,
            'status' => 'progress'
        ]);
        return response()->json(['message' => 'Laporan maintenance masuk!', 'data' => $item]);
    }

    public function updateItems(Request $request, $id) {
        $item = Items::findOrFail($id);
        // Bisa update status saja atau semua field
        $item->update($request->all()); 
        return response()->json(['message' => 'Data maintenance diperbarui!', 'data' => $item]);
    }

    public function destroyItems($id) {
        Items::destroy($id);
        return response()->json(['message' => 'Data maintenance dihapus!']);
    }
}