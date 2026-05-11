<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

interface Mahasiswa {
  id: number
  nama: string
  nim: string
  prodi: string
}

interface Peminjaman {
  id: number
  nim: string
  nama_barang: string
  kelas: string
  waktu_peminjaman: string
  waktu_pengembalian: string | null
}

interface Item {
  id: number
  kelas: string
  nama_item: string
  tipe_maintenance: string
  status: 'done' | 'progress'
}

const email = ref('')
const password = ref('')
const isLoggedIn = ref(!!localStorage.getItem('token')) // Cek apakah sudah ada token

// ini ditambhin interceptor axios y guys
// ini biar setiap kali manggil API, tokennya otomatis dikirim di header
axios.interceptors.request.use(config => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

const handleLogin = async () => {
  try {
    const response = await axios.post('http://127.0.0.1:8000/api/login', {
      email: email.value,
      password: password.value
    })
    
    // 1. Simpan token
    const token = response.data.access_token
    localStorage.setItem('token', token)
    
    // 2. TEMPEL TOKEN KE AXIOS 
    axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    
    // 3. Update status login
    isLoggedIn.value = true
    
    // 4. Paksa ambil data ulang
    await Promise.all([
      getMahasiswa(),
      getPeminjaman(),
      getItems()
    ])
    
    alert("Login Berhasil! Dashboard ClassKit siap.")
  } catch (error: any) {
    console.error("Detail Error:", error.response?.data) 
    alert("Gagal Login: " + (error.response?.data?.message || "Cek koneksi database!"))
  }
}

const handleLogout = () => {
  localStorage.removeItem('token')
  isLoggedIn.value = false
  alert("Sampai jumpa lagi!")
}

// STATE MAHASISWA
const mahasiswa = ref<Mahasiswa[]>([])

const nama = ref('')
const nim = ref('')
const prodi = ref('')

const file = ref<File | null>(null)

const editId = ref<number | null>(null)

// STATE PEMINJAMAN
const peminjaman = ref<Peminjaman[]>([])

const nimPinjam = ref('')
const namaBarang = ref('')
const kelas = ref('')

// State Items
const items = ref<Item[]>([])
const kelasItem = ref('')
const namaItem = ref('')
const tipeMaintenance = ref('')

// GET MAHASISWA
const getMahasiswa = async () => {
  const response = await axios.get(
    'http://127.0.0.1:8000/api/mahasiswa'
  )

  mahasiswa.value = response.data
}

// HANDLE FILE
const handleFile = (event: Event) => {

  const target = event.target as HTMLInputElement

  if (target.files && target.files.length > 0) {
    file.value = target.files[0] as File
  }

}

// RESET FORM
const resetForm = () => {

  nama.value = ''
  nim.value = ''
  prodi.value = ''

  file.value = null

  editId.value = null

}

// TAMBAH MAHASISWA
const tambahMahasiswa = async () => {

  try {

    const formData = new FormData()

    formData.append('nama', nama.value)
    formData.append('nim', nim.value)
    formData.append('prodi', prodi.value)

    if (file.value) {
      formData.append('ktp_ktm', file.value)
    }

    await axios.post(
      'http://127.0.0.1:8000/api/mahasiswa',
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    await getMahasiswa()

    resetForm()

  } catch (error: any) {

    console.log(error.response?.data)

  }

}

// EDIT MAHASISWA
const editMahasiswa = (m: Mahasiswa) => {

  editId.value = m.id

  nama.value = m.nama
  nim.value = m.nim
  prodi.value = m.prodi

}

// UPDATE MAHASISWA
const updateMahasiswa = async () => {

  try {

    const formData = new FormData()

    formData.append('nama', nama.value)
    formData.append('nim', nim.value)
    formData.append('prodi', prodi.value)

    if (file.value) {
      formData.append('ktp_ktm', file.value)
    }

    await axios.post(
      `http://127.0.0.1:8000/api/mahasiswa/${editId.value}?_method=PUT`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    await getMahasiswa()

    resetForm()

  } catch (error) {

    console.log(error)

  }

}

// HAPUS MAHASISWA
const hapusMahasiswa = async (id: number) => {

  const yakin = confirm('Yakin hapus data?')

  if (!yakin) return

  await axios.delete(
    `http://127.0.0.1:8000/api/mahasiswa/${id}`
  )

  await getMahasiswa()

}

// GET PEMINJAMAN
const getPeminjaman = async () => {
  try {
    const response = await axios.get('http://127.0.0.1:8000/api/peminjaman'); // SESUAIKAN ROUTE
    peminjaman.value = response.data;
  } catch (error) {
    console.error("Gagal ambil data peminjaman:", error);
  }
}

// TAMBAH PEMINJAMAN
const tambahPeminjaman = async () => {
  try {
    await axios.post('http://127.0.0.1:8000/api/peminjaman', { // SESUAIKAN ROUTE
      nim: nimPinjam.value,
      nama_barang: namaBarang.value,
      kelas: kelas.value
    });
    await getPeminjaman(); 
    nimPinjam.value = '';
    namaBarang.value = '';
    kelas.value = '';
  } catch (error) {
    console.error("Gagal pinjam barang:", error);
  }
}

// KEMBALIKAN BARANG
const kembalikanBarang = async (id: number) => {

  await axios.put(
    `http://127.0.0.1:8000/api/peminjaman/${id}`
  )

  await getPeminjaman()

}

// HAPUS PEMINJAMAN
const hapusPeminjaman = async (id: number) => {

  await axios.delete(
    `http://127.0.0.1:8000/api/peminjaman/${id}`
  )

  await getPeminjaman()

}

// GET ITEMS
const getItems = async () => {
  const response = await axios.get('http://127.0.0.1:8000/api/items')
  items.value = response.data
}

// TAMBAH ITEM
const tambahItem = async () => {
  try {
    await axios.post('http://127.0.0.1:8000/api/items', { // Cek URL ini!
      kelas: kelasItem.value,
      nama_item: namaItem.value,
      tipe_maintenance: tipeMaintenance.value
    })
    await getItems() 
    
    kelasItem.value = ''
    namaItem.value = ''
    tipeMaintenance.value = ''
  } catch (error: any) {
    console.error("Error pas lapor:", error.response?.data)
  }
}

// UPDATE STATUS ITEM 
const selesaikanMaintenance = async (item: Item) => {
  await axios.put(`http://127.0.0.1:8000/api/items/${item.id}`, {
    status: 'done'
  })
  await getItems()
}

// HAPUS ITEM
const hapusItem = async (id: number) => {
  if (!confirm('Hapus laporan maintenance?')) return
  await axios.delete(`http://127.0.0.1:8000/api/items/${id}`)
  await getItems()
}

// MOUNTED
onMounted(() => {
  const token = localStorage.getItem('token')
  if (token) {
    getMahasiswa()
    getPeminjaman()
    getItems()
  }
})
</script>

<template>
  <div v-if="!isLoggedIn" class="login-page">
    <div class="login-card">
      <div class="nav-logo" style="margin-bottom: 20px; font-size: 2rem; text-align: center;">
        Class<span>Kit</span>
      </div>
      <h2>Admin Login</h2>
      <p style="color: #94a3b8; margin-bottom: 20px;">Silahkan masuk untuk mengelola inventaris</p>
      
      <div class="login-form">
        <input v-model="email" type="email" placeholder="Email Admin" class="login-input" />
        <input v-model="password" type="password" placeholder="Password" class="login-input" />
        <button class="primary-btn login-btn" @click="handleLogin">Masuk ke Dashboard</button>
      </div>
    </div>
  </div>

  <div v-else>
    <nav class="navbar">
      <div class="nav-container">
        <span class="nav-logo">Class<span>Kit</span></span>
        <div class="nav-links">
          <a href="#peminjaman">Peminjaman</a>
          <a href="#mahasiswa">Mahasiswa</a>
          <a href="#items">Maintenance</a>
          <button @click="handleLogout" class="hapus-btn" style="padding: 5px 12px; margin-left: 15px; font-size: 0.8rem;">
            Logout
          </button>
        </div>
      </div>
    </nav>

    <div class="dashboard-wrapper">
      <header class="dashboard-header">
        <h1>Beranda Dashboard</h1>
        <p>Halo Admin, selamat datang di sistem manajemen inventaris.</p>
        
        <div class="stats-grid">
          <div class="stat-card">
            <span class="stat-label">Total Mahasiswa</span>
            <span class="stat-value">{{ mahasiswa.length }}</span>
          </div>
          <div class="stat-card active">
            <span class="stat-label">Peminjaman Aktif</span>
            <span class="stat-value">
              {{ peminjaman.filter(p => !p.waktu_pengembalian).length }}
            </span>
          </div>
          <div class="stat-card alert">
            <span class="stat-label">Maintenance Progress</span>
            <span class="stat-value">
              {{ items.filter(i => i.status === 'progress').length }}
            </span>
          </div>
        </div>
      </header>

      <section id="mahasiswa" class="content-section">
        <div class="section-header"><h2>Data Mahasiswa</h2></div>
        <div class="form-group">
          <input v-model="nama" placeholder="Nama Lengkap" />
          <input v-model="nim" placeholder="NIM" />
          <input v-model="prodi" placeholder="Prodi" />
          <input type="file" @change="handleFile" />
          <button v-if="editId === null" class="primary-btn" @click="tambahMahasiswa">Tambah</button>
          <div v-else>
            <button class="edit-btn" @click="updateMahasiswa">Update</button>
            <button class="cancel-btn" @click="resetForm">Batal</button>
          </div>
        </div>
        <ul class="data-list grid">
          <li v-for="m in mahasiswa" :key="m.id" class="card-mini">
            <div class="info">
              <strong>{{ m.nama }}</strong>
              <p>{{ m.nim }} | {{ m.prodi }}</p>
            </div>
            <div class="actions">
              <button class="edit-btn" @click="editMahasiswa(m)">Edit</button>
              <button class="hapus-btn" @click="hapusMahasiswa(m.id)">Hapus</button>
            </div>
          </li>
        </ul>
      </section>

      <section id="peminjaman" class="content-section">
        <div class="section-header"><h2>Data Peminjaman</h2></div>
        <div class="form-group">
          <input v-model="nimPinjam" placeholder="NIM" />
          <input v-model="namaBarang" placeholder="Nama Barang" />
          <input v-model="kelas" placeholder="Kelas" />
          <button class="primary-btn" @click="tambahPeminjaman">Pinjam Sekarang</button>
        </div>
        <ul class="data-list">
          <li v-for="p in peminjaman" :key="p.id" class="card">
            <div class="card-content">
              <strong>{{ p.nama_barang }}</strong>
              <span>{{ p.nim }} - {{ p.kelas }}</span>
              <small>Pinjam: {{ p.waktu_peminjaman }}</small>
              <small v-if="p.waktu_pengembalian" class="text-success">Kembali: {{ p.waktu_pengembalian }}</small>
            </div>
            <div class="card-actions">
              <button v-if="!p.waktu_pengembalian" class="edit-btn" @click="kembalikanBarang(p.id)">Kembalikan</button>
              <button class="hapus-btn" @click="hapusPeminjaman(p.id)">Hapus</button>
            </div>
          </li>
        </ul>
      </section>

      <section id="items" class="content-section">
        <div class="section-header"><h2>Maintenance Items</h2></div>
        <div class="form-group">
          <input v-model="kelasItem" placeholder="Kelas" />
          <input v-model="namaItem" placeholder="Nama Item" />
          <input v-model="tipeMaintenance" placeholder="Kendala/Masalah" />
          <button class="primary-btn" @click="tambahItem">Laporkan</button>
        </div>
        <ul class="data-list">
          <li v-for="item in items" :key="item.id" class="card">
            <div class="card-content">
              <span :class="item.status === 'done' ? 'tag-done' : 'tag-progress'">
                {{ item.status.toUpperCase() }}
              </span>
              <strong>{{ item.nama_item }} - {{ item.kelas }}</strong>
              <p>Masalah: {{ item.tipe_maintenance }}</p>
            </div>
            <div class="card-actions">
              <button v-if="item.status === 'progress'" class="edit-btn" @click="selesaikanMaintenance(item)">Selesai</button>
              <button class="hapus-btn" @click="hapusItem(item.id)">Hapus</button>
            </div>
          </li>
        </ul>
      </section>
    </div>
  </div>
</template>

<style>
/* BASE */
body {
  font-family: 'Inter', sans-serif;
  background-color: #0f172a;
  color: #f8fafc;
  margin: 0;
  padding: 0;
  scroll-behavior: smooth;
}

.dashboard-wrapper {
  max-width: 1100px;
  margin: 80px auto; 
  padding: 20px;
}

/* NAVBAR */
.navbar {
  background: rgba(30, 41, 59, 0.8);
  backdrop-filter: blur(10px);
  padding: 15px 0;
  position: fixed;
  top: 0; width: 100%;
  z-index: 1000;
  border-bottom: 1px solid #334155;
}
.nav-container {
  max-width: 1100px;
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 20px;
}
.nav-logo { font-weight: 800; font-size: 1.2rem; color: #bb86fc; }
.nav-logo span { color: #03dac6; }
.nav-links a {
  color: #94a3b8;
  text-decoration: none;
  margin-left: 25px;
  font-size: 0.9rem;
  transition: 0.3s;
}
.nav-links a:hover { color: #bb86fc; }

/* HEADER & STATS */
.dashboard-header { margin-bottom: 50px; }
.dashboard-header h1 { font-size: 2rem; margin-bottom: 10px; }
.dashboard-header p { color: #94a3b8; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-top: 30px;
}
.stat-card {
  background: #1e293b;
  padding: 20px;
  border-radius: 12px;
  border: 1px solid #334155;
}
.stat-label { display: block; color: #94a3b8; font-size: 0.8rem; margin-bottom: 10px; }
.stat-value { font-size: 1.8rem; font-weight: 700; }
.stat-card.active { border-left: 4px solid #03dac6; }
.stat-card.alert { border-left: 4px solid #cf6679; }

/* SECTION & FORMS */
.content-section {
  background: #1e293b;
  border-radius: 16px;
  padding: 30px;
  margin-bottom: 40px;
}
.section-header h2 { margin-top: 0; color: #bb86fc; }

.form-group {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  background: #0f172a;
  padding: 20px;
  border-radius: 12px;
  margin-bottom: 25px;
}
input {
  background: #1e293b;
  border: 1px solid #334155;
  color: white;
  padding: 12px;
  border-radius: 8px;
  flex: 1; min-width: 150px;
}

/* BUTTONS */
button {
  padding: 12px 20px;
  border-radius: 8px;
  border: none; cursor: pointer;
  font-weight: 600; transition: 0.2s;
}
.primary-btn { background: #bb86fc; color: #000; }
.edit-btn { background: #03dac6; color: #000; margin-right: 5px; }
.hapus-btn { background: #cf6679; color: #fff; }
.cancel-btn { background: #64748b; color: #fff; }

/* LIST & CARDS */
.data-list { padding: 0; }
.card {
  background: #0f172a;
  margin-bottom: 15px;
  padding: 20px;
  border-radius: 12px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.card-content strong { display: block; font-size: 1.1rem; margin-bottom: 5px; }
.card-content small { display: block; color: #64748b; margin-top: 5px; }

/* GRID MAHASISWA */
.data-list.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 15px;
}
.card-mini {
  background: #0f172a;
  padding: 15px;
  border-radius: 10px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

/* LOGIN PAGE STYLING */
.login-page {
  height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background-color: #0f172a;
}

.login-card {
  background: #1e293b;
  padding: 40px;
  border-radius: 20px;
  width: 100%;
  max-width: 400px;
  border: 1px solid #334155;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.login-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.login-input {
  width: 100%;
  box-sizing: border-box;
  padding: 14px;
  font-size: 1rem;
}

.login-btn {
  width: 100%;
  margin-top: 10px;
  font-size: 1rem;
  padding: 14px;
}

/* TAGS */
.tag-done { background: #065f46; color: #34d399; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; margin-bottom: 10px; display: inline-block; }
.tag-progress { background: #7c2d12; color: #fb923c; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; margin-bottom: 10px; display: inline-block; }

.text-success { color: #03dac6; }
</style>


