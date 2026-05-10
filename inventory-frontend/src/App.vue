<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'

interface Mahasiswa {
  id: number
  nama: string
  nim: string
  prodi: string
}

const mahasiswa = ref<Mahasiswa[]>([])

const nama = ref('')
const nim = ref('')
const prodi = ref('')

const file = ref<File | null>(null)

const editId = ref<number | null>(null)

const getMahasiswa = async () => {
  const response = await axios.get(
    'http://127.0.0.1:8000/api/mahasiswa'
  )

  mahasiswa.value = response.data
}

const handleFile = (event: Event) => {
  const target = event.target as HTMLInputElement

  if (target.files && target.files.length > 0) {
    file.value = target.files[0] as File
  }
}

const resetForm = () => {
  nama.value = ''
  nim.value = ''
  prodi.value = ''

  file.value = null

  editId.value = null
}

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

    console.log(error.response.data)

  }

}

const editMahasiswa = (m: Mahasiswa) => {

  editId.value = m.id

  nama.value = m.nama
  nim.value = m.nim
  prodi.value = m.prodi
}


const updateMahasiswa = async () => {

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
}

const hapusMahasiswa = async (id: number) => {

  await axios.delete(
    `http://127.0.0.1:8000/api/mahasiswa/${id}`
  )

  await getMahasiswa()
}

onMounted(() => {
  getMahasiswa()
})
</script>

<template>
  <div>

    <h1>Data Mahasiswa</h1>

    <input
      v-model="nama"
      placeholder="Nama"
    />

    <input
      v-model="nim"
      placeholder="NIM"
    />

    <input
      v-model="prodi"
      placeholder="Prodi"
    />

    <input
      type="file"
      @change="handleFile"
    />

    <button
      v-if="editId === null"
      @click="tambahMahasiswa"
    >
      Tambah
    </button>

    <button
      v-else
      @click="updateMahasiswa"
    >
      Update
    </button>

    <button
      v-if="editId !== null"
      @click="resetForm"
    >
      Cancel
    </button>

    <hr>

    <ul>

      <li
        v-for="m in mahasiswa"
        :key="m.id"
      >

        {{ m.nama }} - {{ m.nim }} - {{ m.prodi }}

        <button @click="editMahasiswa(m)">
          Edit
        </button>

        <button @click="hapusMahasiswa(m.id)">
          Hapus
        </button>

      </li>

    </ul>

  </div>
</template>


