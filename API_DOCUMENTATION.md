# API Dokumentasi - Sistem Manajemen Penelitian (Belaravel)

Base URL: `http://localhost:8000/api`

---

## Autentikasi

Semua endpoint (kecuali `register` dan `login`) memerlukan **Bearer Token** yang diperoleh dari login.

**Header:**
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

## Role & Hak Akses

| Role | Key | Akses |
|------|-----|-------|
| Mahasiswa | `mahasiswa` | Mengajukan proposal, input progress, bimbingan, seminar |
| Dosen Pembimbing | `dosen_pembimbing` | Review progress, bimbingan, approval dokumen |
| Admin Akademik | `admin_akademik` | Kelola user, jadwal seminar, dashboard |
| Kaprodi | `kaprodi` | Review proposal, assign pembimbing/penguji, jadwal seminar |
| Penguji | `penguji` | Input nilai & keputusan seminar |

---

## Status Code

| Code | Keterangan |
|------|-----------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized |
| 403 | Forbidden |
| 404 | Not Found |
| 422 | Validation Error |

**Response Format:**
```json
{
  "success": true/false,
  "message": "Pesan",
  "data": {}
}
```

---

## 1. Autentikasi

### 1.1 Register

Mendaftarkan user baru. Publik (tanpa token).

**Endpoint:** `POST /api/register`

**Request Body:**
```json
{
  "name": "string (required|max:255)",
  "email": "string (required|email|unique)",
  "password": "string (required|min:8)",
  "role": "string (required|in:mahasiswa,dosen_pembimbing,admin_akademik,kaprodi,penguji)",
  "nomor_induk": "string (nullable|unique)",
  "jurusan": "string (nullable|max:255)",
  "angkatan": "string (nullable|max:20)",
  "no_telepon": "string (nullable|max:20)"
}
```

**Response (201):**
```json
{
  "success": true,
  "message": "Registrasi berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "mahasiswa",
      "nomor_induk": "2021001",
      "jurusan": "Teknik Informatika",
      "angkatan": "2021",
      "no_telepon": "08123456789",
      "avatar": null,
      "created_at": "2026-06-08T12:00:00.000000Z"
    },
    "token": "1|abc123def456..."
  }
}
```

### 1.2 Login

**Endpoint:** `POST /api/login`

**Request Body:**
```json
{
  "email": "string (required|email)",
  "password": "string (required)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Login berhasil",
  "data": {
    "user": { "...user data..." },
    "token": "2|xyz789..."
  }
}
```

### 1.3 Logout

**Endpoint:** `POST /api/logout`  
**Role:** Authenticated

**Headers:** `Authorization: Bearer {token}`

**Response (200):**
```json
{
  "success": true,
  "message": "Logout berhasil",
  "data": null
}
```

### 1.4 Profile Saya

**Endpoint:** `GET /api/me`  
**Role:** Authenticated

**Response (200):** User resource object.

### 1.5 Update Profile

**Endpoint:** `PUT /api/profile`  
**Role:** Authenticated

**Request Body:**
```json
{
  "name": "string (sometimes)",
  "jurusan": "string (sometimes)",
  "angkatan": "string (sometimes)",
  "no_telepon": "string (sometimes)"
}
```

---

## 2. Manajemen User

### 2.1 List Users

**Endpoint:** `GET /api/users`  
**Role:** Authenticated

**Query Params:**
- `role` (string) - filter by role
- `search` (string) - cari by name/email/nomor_induk
- `per_page` (integer, default: 15)

### 2.2 Detail User

**Endpoint:** `GET /api/users/{id}`  
**Role:** Authenticated

### 2.3 List Dosen

**Endpoint:** `GET /api/users/dosen`  
**Role:** Authenticated  
Mengembalikan user dengan role `dosen_pembimbing` dan `penguji`.

### 2.4 List Mahasiswa

**Endpoint:** `GET /api/users/mahasiswa`  
**Role:** Authenticated

### 2.5 Update User

**Endpoint:** `PUT /api/users/{id}`  
**Role:** Authenticated

### 2.6 Delete User

**Endpoint:** `DELETE /api/users/{id}`  
**Role:** Authenticated

---

## 3. Pengajuan Penelitian (Proposal)

### 3.1 List Proposal

**Endpoint:** `GET /api/proposals`  
**Role:** Authenticated

**Filter berdasarkan role:**
- **Mahasiswa:** melihat proposal miliknya sendiri
- **Kaprodi:** melihat semua proposal
- **Dosen Pembimbing:** melihat proposal yang dibimbingnya

**Query Params:**
- `status` (string) - filter: `draft`, `diajukan`, `direview`, `disetujui`, `direvisi`, `ditolak`
- `per_page` (integer, default: 15)

### 3.2 Buat Proposal

**Endpoint:** `POST /api/proposals`  
**Role:** `mahasiswa`

**Request: multipart/form-data**
| Field | Type | Rules |
|-------|------|-------|
| `judul` | string | required, max:500 |
| `abstrak` | text | required |
| `topik` | string | required, max:255 |
| `file` | file | nullable, mimes:pdf,doc,docx, max:20480KB |

**Response (201):** Proposal resource.

### 3.3 Detail Proposal

**Endpoint:** `GET /api/proposals/{proposal}`  
**Role:** Authenticated  
Memuat relasi: mahasiswa, reviewer, pembimbing, progress, bimbingan, seminar.

### 3.4 Update Proposal

**Endpoint:** `PUT /api/proposals/{proposal}`  
**Role:** `mahasiswa` (pemilik)

Jika status sebelumnya `direvisi`, status akan dikembalikan ke `diajukan` untuk review ulang.

### 3.5 Review Proposal (Kaprodi)

**Endpoint:** `POST /api/proposals/{proposal}/review`  
**Role:** `kaprodi`

**Request Body:**
```json
{
  "action": "string (required|in:approve,reject,revision)",
  "catatan": "string (required_if:action,reject,revision)"
}
```

**Action:**
- `approve` → status `disetujui`
- `reject` → status `ditolak`
- `revision` → status `direvisi`

Notifikasi otomatis dikirim ke mahasiswa.

---

## 4. Penetapan Dosen Pembimbing

### 4.1 Assign Pembimbing

**Endpoint:** `POST /api/proposals/{proposal}/supervisors`  
**Role:** `kaprodi`

**Request Body:**
```json
{
  "dosen_id": "integer (required|exists:users,id)"
}
```

**Response (201):** Supervisor resource.

### 4.2 List Pembimbing Proposal

**Endpoint:** `GET /api/proposals/{proposal}/supervisors`  
**Role:** Authenticated

### 4.3 List Tugas Saya (Dosen)

**Endpoint:** `GET /api/my-assignments`  
**Role:** `dosen_pembimbing`

### 4.4 Respon Penugasan

**Endpoint:** `POST /api/supervisors/{supervisor}/respond`  
**Role:** `dosen_pembimbing` (yang ditugaskan)

**Request Body:**
```json
{
  "action": "string (required|in:terima,tolak)",
  "keterangan": "string (nullable)"
}
```

**Status:**
- `terima` → status `diterima`, proposal otomatis `disetujui`
- `tolak` → status `ditolak`

---

## 5. Monitoring Progress Penelitian

### 5.1 List Progress

**Endpoint:** `GET /api/progress`  
**Role:** Authenticated

**Query Params:**
- `proposal_id` (integer)
- `status` (string): `approved`, `revisi`, `on_track`, `tertinggal`
- `per_page` (integer, default: 15)

### 5.2 Tambah Progress

**Endpoint:** `POST /api/progress`  
**Role:** `mahasiswa`

**Request: multipart/form-data**
| Field | Type | Rules |
|-------|------|-------|
| `research_proposal_id` | integer | required, exists |
| `judul_progres` | string | required, max:500 |
| `deskripsi` | text | required |
| `periode` | string | nullable, max:50 |
| `documents[]` | file[] | nullable, max:20480KB each |

### 5.3 Detail Progress

**Endpoint:** `GET /api/progress/{progress}`  
**Role:** Authenticated

### 5.4 Review Progress (Dosen)

**Endpoint:** `POST /api/progress/{progress}/review`  
**Role:** `dosen_pembimbing`

**Request Body:**
```json
{
  "status": "string (required|in:approved,revisi,on_track,tertinggal)",
  "catatan": "string (nullable)"
}
```

**Status:**
- `approved` - Disetujui
- `revisi` - Perlu revisi
- `on_track` - Sesuai rencana
- `tertinggal` - Tertinggal

---

## 6. Bimbingan dan Revisi

### 6.1 List Bimbingan

**Endpoint:** `GET /api/guidance`  
**Role:** Authenticated

**Query Params:**
- `status` (string): `diajukan`, `direspon`, `selesai`
- `proposal_id` (integer)
- `per_page` (integer, default: 15)

**Filter role:**
- **Mahasiswa:** melihat bimbingan miliknya
- **Dosen:** melihat bimbingan yang diberikan

### 6.2 Buat Permohonan Bimbingan

**Endpoint:** `POST /api/guidance`  
**Role:** `mahasiswa`

**Request Body:**
```json
{
  "research_proposal_id": "integer (required|exists)",
  "dosen_id": "integer (required|exists:users,id)",
  "judul": "string (required|max:500)",
  "deskripsi": "text (required)"
}
```

### 6.3 Detail Bimbingan

**Endpoint:** `GET /api/guidance/{guidanceRequest}`  
**Role:** Authenticated  
Memuat relasi: mahasiswa, dosen, dokumen, komentar.

### 6.4 Upload Dokumen Bimbingan

**Endpoint:** `POST /api/guidance/{guidanceRequest}/documents`  
**Role:** Authenticated (mahasiswa/dosen)

**Request: multipart/form-data**
| Field | Type | Rules |
|-------|------|-------|
| `file` | file | required, mimes:pdf,doc,docx,zip, max:20480KB |

Versioning otomatis: version number increment setiap upload.

### 6.5 Tambah Komentar

**Endpoint:** `POST /api/guidance/{guidanceRequest}/comments`  
**Role:** Authenticated (mahasiswa/dosen)

**Request Body:**
```json
{
  "konten": "text (required)",
  "is_revision": "boolean (optional, default: false)"
}
```

Jika `is_revision: true`, notifikasi "Catatan Revisi" dikirim ke lawan.

### 6.6 Approval Dokumen

**Endpoint:** `POST /api/guidance/{guidanceRequest}/approve`  
**Role:** `dosen_pembimbing`

Mengubah status bimbingan menjadi `selesai`.

---

## 7. Seminar / Sidang

### 7.1 List Seminar

**Endpoint:** `GET /api/seminars`  
**Role:** Authenticated

**Query Params:**
- `status` (string): `diajukan`, `disetujui`, `dijadwalkan`, `selesai`, `ditolak`
- `jenis` (string): `seminar_proposal`, `sidang_skripsi`
- `per_page` (integer, default: 15)

### 7.2 Pengajuan Seminar

**Endpoint:** `POST /api/seminars`  
**Role:** `mahasiswa`

**Request: multipart/form-data**
| Field | Type | Rules |
|-------|------|-------|
| `research_proposal_id` | integer | required, exists |
| `jenis` | string | required, in:seminar_proposal,sidang_skripsi |
| `judul` | string | required, max:500 |
| `deskripsi` | text | nullable |
| `file` | file | nullable, mimes:pdf,doc,docx, max:20480KB |

### 7.3 Detail Seminar

**Endpoint:** `GET /api/seminars/{seminar}`  
**Role:** Authenticated

### 7.4 Approve Seminar

**Endpoint:** `POST /api/seminars/{seminar}/approve`  
**Role:** `kaprodi`

### 7.5 Jadwalkan Seminar

**Endpoint:** `POST /api/seminars/{seminar}/schedule`  
**Role:** `kaprodi`, `admin_akademik`

**Request Body:**
```json
{
  "tanggal_seminar": "datetime (required|after:now)",
  "ruangan": "string (required|max:100)"
}
```

Notifikasi otomatis ke mahasiswa dan seluruh penguji.

### 7.6 Assign Penguji

**Endpoint:** `POST /api/seminars/{seminar}/reviewers`  
**Role:** `kaprodi`

**Request Body:**
```json
{
  "dosen_id": "integer (required|exists:users,id)",
  "peran": "string (required|in:ketua_penguji,anggota_penguji,pembimbing)"
}
```

### 7.7 Input Nilai & Keputusan

**Endpoint:** `POST /api/seminars/{seminar}/results`  
**Role:** `penguji`, `dosen_pembimbing`

**Request: multipart/form-data**
| Field | Type | Rules |
|-------|------|-------|
| `nilai` | numeric | required, min:0, max:100 |
| `catatan` | text | nullable |
| `keputusan` | string | required, in:lulus,lulus_dengan_revisi,tidak_lulus |
| `file_berita_acara` | file | nullable, mimes:pdf, max:20480KB |

Jika semua penguji sudah submit, status seminar otomatis menjadi `selesai`.

### 7.8 Validasi Syarat Seminar

**Endpoint:** `POST /api/seminars/{seminar}/validate-syarat`  
**Role:** `kaprodi`, `admin_akademik`

**Request Body:**
```json
{
  "valid": "boolean (optional, default: true)"
}
```

---

## 8. Manajemen Dokumen

### 8.1 Upload Dokumen

**Endpoint:** `POST /api/documents/upload`  
**Role:** Authenticated

**Request: multipart/form-data**
| Field | Type | Rules |
|-------|------|-------|
| `file` | file | required, mimes:pdf,doc,docx,jpg,jpeg,png,zip, max:20480KB |
| `documentable_type` | string | required (class name, e.g. `App\Models\ResearchProposal`) |
| `documentable_id` | integer | required |
| `keterangan` | string | nullable |

Versioning otomatis berdasarkan documentable.

### 8.2 Riwayat Versi Dokumen

**Endpoint:** `GET /api/documents/versions?documentable_type=...&documentable_id=...`  
**Role:** Authenticated

### 8.3 Detail Dokumen

**Endpoint:** `GET /api/documents/{documentVersion}`  
**Role:** Authenticated

### 8.4 Hapus Dokumen

**Endpoint:** `DELETE /api/documents/{documentVersion}`  
**Role:** Authenticated

---

## 9. Notifikasi

### 9.1 List Notifikasi

**Endpoint:** `GET /api/notifications`  
**Role:** Authenticated

**Query Params:**
- `per_page` (integer, default: 20)

### 9.2 Notifikasi Belum Dibaca

**Endpoint:** `GET /api/notifications/unread`  
**Role:** Authenticated

**Response:**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "total": 5,
    "data": [ "...notifications..." ]
  }
}
```

### 9.3 Tandai Dibaca

**Endpoint:** `POST /api/notifications/{notification}/read`  
**Role:** Authenticated (pemilik)

### 9.4 Tandai Semua Dibaca

**Endpoint:** `POST /api/notifications/read-all`  
**Role:** Authenticated

### 9.5 Hapus Notifikasi

**Endpoint:** `DELETE /api/notifications/{notification}`  
**Role:** Authenticated

---

## 10. Dashboard & Laporan

### 10.1 Dashboard (per Role)

**Endpoint:** `GET /api/dashboard`  
**Role:** Authenticated

Response berbeda berdasarkan role:

**Mahasiswa:**
```json
{
  "data": {
    "proposal": { "id": 1, "judul": "...", "status": "disetujui" },
    "statistics": {
      "total_progress": 5,
      "on_track": 3,
      "tertinggal": 2,
      "total_seminar": 1
    },
    "recent_progress": [...]
  }
}
```

**Dosen:**
```json
{
  "data": {
    "statistics": {
      "total_bimbingan": 10,
      "pending_assignments": 2,
      "total_seminar": 3
    },
    "mahasiswa_bimbingan": [...]
  }
}
```

**Kaprodi:**
```json
{
  "data": {
    "statistics": {
      "total_mahasiswa": 50,
      "total_dosen": 15,
      "total_proposals": 30,
      "pending_proposals": 5,
      "approved_proposals": 20,
      "rejected_proposals": 5,
      "pending_seminars": 3,
      "completed_seminars": 10
    },
    "recent_proposals": [...]
  }
}
```

**Admin Akademik:**
```json
{
  "data": {
    "statistics": {
      "total_mahasiswa": 50,
      "total_dosen": 15,
      "total_kaprodi": 1,
      "total_proposals": 30,
      "total_seminars": 15
    },
    "upcoming_seminars": [...]
  }
}
```

### 10.2 Report / Rekap Statistik

**Endpoint:** `GET /api/report`  
**Role:** Authenticated

```json
{
  "data": {
    "total_mahasiswa": 50,
    "total_dosen": 15,
    "total_proposals": 30,
    "proposals_by_status": { "disetujui": 20, "ditolak": 5, "diajukan": 5 },
    "total_progress": 100,
    "progress_by_status": { "on_track": 60, "approved": 20, "revisi": 10, "tertinggal": 10 },
    "total_seminars": 15,
    "seminars_by_status": { "selesai": 10, "dijadwalkan": 3, "diajukan": 2 },
    "seminars_by_jenis": { "seminar_proposal": 10, "sidang_skripsi": 5 }
  }
}
```

### 10.3 Export Laporan

**Endpoint:** `GET /api/export?type=proposals`  
**Role:** Authenticated

**Query Params:**
- `type` (string): `proposals`, `progress`, `seminars`

Mengembalikan array data untuk di-export (format JSON siap diolah menjadi CSV/Excel).

---

## Resource Response Reference

### User Resource
```json
{
  "id": 1,
  "name": "John Doe",
  "email": "john@example.com",
  "role": "mahasiswa",
  "nomor_induk": "2021001",
  "jurusan": "Teknik Informatika",
  "angkatan": "2021",
  "no_telepon": "08123456789",
  "avatar": "http://localhost:8000/storage/avatars/abc.jpg",
  "created_at": "2026-06-08T12:00:00.000000Z"
}
```

### Research Status Values

| Field | Values |
|-------|--------|
| `proposal.status` | `draft`, `diajukan`, `direview`, `disetujui`, `direvisi`, `ditolak` |
| `progress.status` | `approved`, `revisi`, `on_track`, `tertinggal` |
| `supervisor.status` | `menunggu`, `diterima`, `ditolak` |
| `guidance.status` | `diajukan`, `direspon`, `selesai` |
| `seminar.status` | `diajukan`, `disetujui`, `dijadwalkan`, `selesai`, `ditolak` |
| `seminar.jenis` | `seminar_proposal`, `sidang_skripsi` |
| `reviewer.peran` | `ketua_penguji`, `anggota_penguji`, `pembimbing` |
| `result.keputusan` | `lulus`, `lulus_dengan_revisi`, `tidak_lulus` |

---

## Error Response

### Validation Error (422)
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "judul": ["Judul harus diisi"]
  }
}
```

### Unauthorized (401)
```json
{
  "success": false,
  "message": "Email atau password salah"
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "Forbidden: Anda tidak memiliki akses ke resource ini"
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Resource not found"
}
```

---

## Catatan

- **File upload** menggunakan `multipart/form-data`
- **Storage path**: `storage/app/public/` (symlink `public/storage`)
- **Pagination**: semua endpoint index mendukung `?per_page=...`
- **Token**: menggunakan Sanctum, token dikirim via header `Authorization: Bearer`
