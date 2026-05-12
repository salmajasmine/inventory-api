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
// UPDATE MAHASISWA (Ganti fungsi yang lama sama ini)
const updateMahasiswa = async () => {
  try {
    const formData = new FormData()

    formData.append('nama', nama.value)
    formData.append('nim', nim.value)
    formData.append('prodi', prodi.value)

    // Penting: Laravel butuh ini di dalam FormData kalau mau simulasi PUT via POST
    formData.append('_method', 'PUT') 

    if (file.value) {
      formData.append('ktp_ktm', file.value)
    }

    // URL-nya balikin jadi POST biasa, jangan dipaksa di URL stringnya
    await axios.post(
      `http://127.0.0.1:8000/api/mahasiswa/${editId.value}`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    await getMahasiswa()
    resetForm()
    alert("Data mahasiswa berhasil diperbarui!")

  } catch (error: any) {
    console.error("Gagal Update:", error.response?.data)
    alert("Gagal Update: " + (error.response?.data?.message || "Cek inputan!"))
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
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&family=DM+Sans:wght@400;500;600&display=swap');

:root {
  --cr: #c4763a; --cr-l: #faf3eb; --cr-m: #edd9bc;
  --cg: #5a7a5e; --cg-l: #eef4ee; --cg-m: #c4d9c6;
  --bg: #f7f2eb;
  --surface: #fdfaf5;
  --border: #ddd3bc;
  --border-dark: #c4b89a;
  --text: #2e2416;
  --text2: #6b5a40;
  --muted: #9c8b72;
  --serif: 'Playfair Display', Georgia, serif;
  --sans: 'DM Sans', system-ui, sans-serif;
}

* { box-sizing: border-box; }

body {
  font-family: var(--sans);
  background: var(--bg);
  color: var(--text);
  margin: 0; padding: 0;
  scroll-behavior: smooth;
}

.dashboard-wrapper {
  max-width: 900px;        
  margin: 80px auto 60px;  
  padding: 0 24px;
  width: 100%;
}

/* NAVBAR */
.navbar {
  background: rgba(253, 250, 245, 0.92);
  backdrop-filter: blur(10px);
  padding: 13px 0;
  position: fixed;
  top: 0; width: 100%;
  z-index: 1000;
  border-bottom: 1px solid var(--border);
}
.nav-container {
  max-width: 900px;   
  margin: 0 auto;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 24px;
}
.nav-logo {
  font-family: var(--serif);
  font-weight: 600;
  font-size: 1.2rem;
  color: var(--cr);
  letter-spacing: 0.5px;
}
.nav-logo span { color: var(--cg); }
.nav-links a {
  color: var(--muted);
  text-decoration: none;
  margin-left: 24px;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.6px;
  text-transform: uppercase;
  transition: color 0.2s;
}
.nav-links a:hover { color: var(--text); }

/* HEADER & STATS */
.dashboard-header { margin-bottom: 40px; }
.dashboard-header h1 {
  font-family: var(--serif);
  font-size: 1.9rem;
  font-weight: 600;
  margin-bottom: 6px;
  color: var(--text);
}
.dashboard-header p { color: var(--muted); font-size: 0.875rem; }

.stats-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);  /* ganti dari auto-fit */
  gap: 14px;
  margin-top: 24px;
}
.stat-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-top: 2px solid var(--border-dark);
  border-radius: 4px;
  padding: 20px 18px;
}
.stat-card.active { border-top-color: var(--cg); }
.stat-card.alert  { border-top-color: var(--cr); }
.stat-label {
  display: block;
  color: var(--muted);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 0.4px;
  text-transform: uppercase;
  margin-bottom: 8px;
}
.stat-value {
  font-family: var(--serif);
  font-size: 2rem;
  font-weight: 600;
  color: var(--text);
  line-height: 1;
}

/* SECTION */
.content-section {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 4px;
  padding: 26px;
  margin-bottom: 28px;
}
.section-header h2 {
  margin-top: 0;
  font-family: var(--sans);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 2px;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 18px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.section-header h2::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border);
}

/* FORM */
.form-group {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  background: var(--bg);
  border: 1px solid var(--border);
  padding: 14px;
  border-radius: 4px;
  margin-bottom: 18px;
}
input {
  background: var(--surface);
  border: 1px solid var(--border);
  color: var(--text);
  padding: 9px 12px;
  border-radius: 3px;
  flex: 1;
  min-width: 130px;
  font-family: var(--sans);
  font-size: 13px;
  outline: none;
  transition: border-color 0.2s;
}
input:focus { border-color: var(--cr); }
input::placeholder { color: var(--muted); }

/* BUTTONS */
button {
  font-family: var(--sans);
  font-weight: 600;
  font-size: 11px;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  padding: 9px 16px;
  border-radius: 3px;
  border: none;
  cursor: pointer;
  transition: opacity 0.15s;
}
button:hover { opacity: 0.85; }

.primary-btn { background: var(--cr); color: var(--surface); }

.edit-btn {
  background: none;
  color: var(--cg);
  border: 1px solid var(--cg-m);
  margin-right: 5px;
}
.hapus-btn {
  background: none;
  color: var(--cr);
  border: 1px solid var(--cr-m);
}
.cancel-btn {
  background: none;
  color: var(--muted);
  border: 1px solid var(--border-dark);
}

/* CARDS */
.data-list { padding: 0; list-style: none; }
.card {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 4px;
  margin-bottom: 10px;
  padding: 14px 18px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: border-color 0.2s;
}
.card:hover { border-color: var(--border-dark); }
.card-content strong {
  display: block;
  font-size: 0.9rem;
  font-weight: 600;
  margin-bottom: 4px;
  color: var(--text);
}
.card-content span { font-size: 12px; color: var(--muted); }
.card-content small {
  display: block;
  color: var(--muted);
  font-size: 11px;
  margin-top: 4px;
}
.card-actions { display: flex; gap: 6px; flex-shrink: 0; }

/* MAHASISWA GRID */
.data-list.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 10px;
}
.card-mini {
  background: var(--bg);
  border: 1px solid var(--border);
  border-radius: 4px;
  padding: 13px 15px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  transition: border-color 0.2s;
}
.card-mini:hover { border-color: var(--border-dark); }
.info strong { font-size: 13px; font-weight: 600; color: var(--text); }
.info p { font-size: 11px; color: var(--muted); margin-top: 2px; }
.actions { display: flex; gap: 5px; }

/* LOGIN */
.login-page {
  width: 100%;
  min-height: 100vh;
  display: flex;
  justify-content: center;
  align-items: center;
  background: var(--cr-l);
  position: absolute;
  top: 0; left: 0;
  z-index: 2000;
}
.login-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: 4px;
  padding: 40px 36px;
  width: 100%;
  max-width: 380px;
  text-align: center;
}
.login-card .nav-logo {
  font-size: 2rem;
  display: block;
  margin-bottom: 4px;
}
.login-card h2 {
  font-family: var(--sans);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: var(--muted);
  margin-bottom: 28px;
}
.login-form { display: flex; flex-direction: column; gap: 10px; }
.login-input {
  width: 100%;
  box-sizing: border-box;
  padding: 12px 14px;
  font-size: 13px;
  border-radius: 3px;
  font-family: var(--sans);
}
.login-btn {
  width: 100%;
  background: var(--cr);
  color: var(--surface);
  border: none;
  border-radius: 3px;
  padding: 13px;
  font-family: var(--sans);
  font-size: 11px;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  cursor: pointer;
  margin-top: 6px;
}
.login-btn:hover { opacity: 0.9; }

/* TAGS */
.tag-done {
  background: var(--cg-l);
  color: #2d4e31;
  border: 1px solid var(--cg-m);
  padding: 3px 9px;
  border-radius: 2px;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  margin-bottom: 6px;
  display: inline-block;
}
.tag-progress {
  background: #fef3e2;
  color: #8a5a00;
  border: 1px solid #f0d89a;
  padding: 3px 9px;
  border-radius: 2px;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: 0.5px;
  text-transform: uppercase;
  margin-bottom: 6px;
  display: inline-block;
}
.text-success { color: var(--cg); font-weight: 600; }

/* Logout button override */
.hapus-btn[style*="padding: 5px"] {
  font-size: 11px;
}
</style>


