# EduSkill

EduSkill adalah platform pembelajaran berbasis web yang dibangun dengan Laravel dan Blade. Aplikasi ini berfokus pada alur belajar bertahap: pengguna mengikuti kursus, membuka modul secara berurutan, membaca materi, mengerjakan kuis, melihat progres, lalu mendapatkan sertifikat ketika kursus selesai.

## Ringkasan

EduSkill memiliki dua peran utama:

- Admin: mengelola kursus, modul, materi, kuis, pengguna, penilaian esai, peserta kursus, pelanggaran kuis, dan backup.
- User: melihat kursus, mendaftar kursus, belajar materi, mengerjakan kuis, melihat progres, dan mengunduh sertifikat.

Struktur pembelajaran utama:

```text
Kursus -> Modul -> Konten
```

Konten dapat berupa:

- Materi teks atau PDF
- Kuis pilihan ganda
- Kuis esai

## Teknologi

- Laravel 10
- PHP 8.1+
- Blade
- MySQL
- JavaScript, jQuery, AJAX
- Vite
- DataTables
- SweetAlert2
- Intervention Image
- Smalot PDF Parser
- Guzzle HTTP

## Fitur Utama

### Autentikasi dan Profil

- Login dan register
- Reset password
- Verifikasi OTP
- Pengaturan profil
- Pengaturan avatar, email, password, dan preferensi notifikasi

### Dashboard

- Dashboard admin dengan ringkasan data
- Dashboard user dengan informasi kursus dan progres
- Redirect otomatis sesuai role pengguna

### Kursus dan Modul

- CRUD kursus oleh admin
- CRUD modul oleh admin
- Pengurutan modul dan konten
- Thumbnail kursus
- Deskripsi kursus
- Status kursus aktif/nonaktif
- Opsi sertifikat per kursus

### Prasyarat Kursus

Admin dapat mengatur kursus prasyarat. User tidak dapat mendaftar kursus tertentu sebelum menyelesaikan kursus prasyarat.

Implementasi:

- Relasi many-to-many melalui `kursus_prerequisites`
- Validasi prasyarat di halaman detail kursus
- Validasi prasyarat di endpoint enroll
- Tampilan status prasyarat di sisi user

### Alur Belajar Bertahap

Halaman belajar mengunci konten secara berurutan.

Aturan alur:

- Konten pertama terbuka otomatis
- Konten berikutnya terkunci sampai konten sebelumnya selesai
- Materi teks dianggap selesai setelah user menekan tombol `Selanjutnya`
- Kuis dianggap selesai hanya jika user submit dan lulus
- Esai manual tetap menunggu penilaian admin
- Konten setelah esai manual baru terbuka setelah esai dinilai lulus

Tampilan sidebar belajar:

- Centang hijau: konten selesai
- Gembok terbuka: konten dapat dibuka
- Gembok terkunci: konten belum dapat dibuka
- Popup terkunci menampilkan nama konten yang diklik

### Materi Teks dan PDF

Admin dapat membuat materi teks dan mengunggah PDF. PDF ditampilkan di halaman belajar dan dapat digunakan sebagai sumber konteks untuk pembuatan soal otomatis.

### Kuis Pilihan Ganda

Kuis pilihan ganda mendukung:

- Pertanyaan dan opsi jawaban manual
- Penilaian otomatis
- Nilai minimum lulus 70
- Review jawaban setelah lulus
- Retry jika belum lulus
- Update progres setelah lulus

### Kuis Esai

Kuis esai mendukung dua mode penilaian:

- Penilaian otomatis
- Penilaian manual oleh admin

Untuk penilaian manual:

- Jawaban user masuk ke menu admin `Penilaian Esai`
- Admin memberi nilai per jawaban
- Admin dapat memberi feedback per jawaban
- Admin dapat memberi catatan umum
- User dapat melihat hasil penilaian dan feedback
- Jika nilai belum lulus, user hanya melihat tombol `Coba Lagi`
- Jika lulus, user dapat lanjut ke konten berikutnya

### Generate Soal Otomatis

Admin dapat membuat kuis yang soalnya dibuat otomatis berdasarkan materi modul atau PDF.

Cara kerja:

- Admin mengaktifkan opsi generate soal
- Admin menentukan jumlah soal per user
- Sistem membuat soal saat user membuka kuis
- Soal disimpan di attempt user
- User lain atau retry dapat memperoleh variasi soal berbeda
- Jawaban benar tidak dikirim ke frontend sebelum submit

Konfigurasi:

- `OPENAI_API_KEY`
- `OPENAI_MODEL`, default `gpt-4o-mini`
- File konfigurasi: `config/openai.php`
- Service: `app/Services/OpenAIService.php`

### Quiz Integrity Mode

Integrity mode digunakan untuk memantau perilaku user saat mengerjakan kuis.

Fitur:

- Deteksi pindah tab
- Deteksi kehilangan fokus window
- Deteksi keluar dari fullscreen
- Batas maksimal pelanggaran
- Warning ke user
- Auto-submit jika pelanggaran mencapai batas
- Riwayat pelanggaran dapat dilihat admin

Catatan: fitur ini adalah sistem monitoring berbasis browser, bukan proteksi absolut.

### Keystroke Dynamics untuk Esai

Sistem merekam pola ketikan saat user mengerjakan esai, lalu menampilkannya pada halaman detail penilaian admin.

Data yang digunakan:

- Dwell time
- Flight time
- Kecepatan ketik
- Error rate
- Jumlah paste
- Total karakter jawaban
- Jenis perangkat

Tujuan:

- Membantu admin melihat indikasi copy-paste
- Membantu membandingkan pola ketik user dengan baseline
- Memberi sinyal tambahan saat menilai esai manual

File utama:

- `app/Services/KeystrokeAnalysisService.php`
- `app/Models/KeystrokeBaseline.php`
- `database/migrations/2026_05_09_000001_add_keystroke_support.php`

### Manajemen User dan Suspend

Admin dapat mengelola user dan memberikan suspend.

Fitur:

- Aktif/nonaktif user
- Suspend sementara
- Suspend tanpa batas waktu
- Alasan suspend
- User yang sedang disuspend tidak dapat melanjutkan akses
- Suspend otomatis selesai jika durasinya habis

### Sertifikat

Jika kursus mengaktifkan sertifikat, user dapat melihat dan mengunduh sertifikat setelah semua konten selesai.

File utama:

- `app/Http/Controllers/User/CertificateController.php`
- Template sertifikat di `public/assets/media/certificate-template.png`

### Explore Your Path

Fitur publik untuk membantu pengunjung memilih kategori belajar sebelum login.

Alur:

1. Pengunjung membuka landing page
2. Pengunjung menjawab kuesioner
3. Sistem menghitung kategori rekomendasi
4. Hasil rekomendasi ditampilkan

Kategori:

- Programming
- Design
- Marketing
- Business
- Cybersecurity

### Backup Sistem

Admin dapat membuka menu backup untuk membuat, mengunduh, melihat, dan menghapus file backup database.

## Struktur Data Utama

Entitas penting:

- `users`
- `kursuses`
- `modules`
- `contents`
- `quiz_questions`
- `quiz_options`
- `user_courses`
- `user_content_progress`
- `user_quiz_attempts`
- `user_quiz_answers`
- `user_quiz_integrity_events`
- `keystroke_baselines`
- `kursus_prerequisites`

Relasi utama:

- Satu kursus memiliki banyak modul
- Satu modul memiliki banyak konten
- Satu konten dapat berupa materi atau kuis
- Satu kuis manual memiliki banyak pertanyaan
- Satu pertanyaan pilihan ganda memiliki banyak opsi
- User memiliki enrollment kursus
- User memiliki progres konten
- User memiliki attempt kuis

## File Penting

Routing:

- `routes/web.php`

Controller user:

- `app/Http/Controllers/User/ListKursusController.php`
- `app/Http/Controllers/User/DashboardController.php`
- `app/Http/Controllers/User/CertificateController.php`
- `app/Http/Controllers/User/ProfileController.php`

Controller admin:

- `app/Http/Controllers/Admin/KursusController.php`
- `app/Http/Controllers/Admin/ModuleController.php`
- `app/Http/Controllers/Admin/ContentController.php`
- `app/Http/Controllers/Admin/EssayGradingController.php`
- `app/Http/Controllers/Admin/QuizIntegrityController.php`
- `app/Http/Controllers/Admin/UserController.php`

Service:

- `app/Services/OpenAIService.php`
- `app/Services/KeystrokeAnalysisService.php`

View user:

- `resources/views/user/kursus/show.blade.php`
- `resources/views/user/kursus/learn.blade.php`
- `resources/views/user/dashboard/index.blade.php`
- `resources/views/user/certificate/preview.blade.php`

View admin:

- `resources/views/admin/module/detail.blade.php`
- `resources/views/admin/essay/index.blade.php`
- `resources/views/admin/essay/show.blade.php`
- `resources/views/admin/kursus/integrity.blade.php`
- `resources/views/admin/user/index.blade.php`

View publik:

- `resources/views/public/landing.blade.php`
- `resources/views/public/explore-path.blade.php`
- `resources/views/public/explore-result.blade.php`

## Instalasi Lokal

Persyaratan:

- PHP 8.1 atau lebih baru
- Composer
- Node.js dan npm
- MySQL
- Ekstensi PHP umum untuk Laravel
- Ekstensi GD untuk fitur sertifikat

Langkah instalasi:

```bash
composer install
npm install
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

Jika file `.env` belum tersedia, buat file `.env` sesuai konfigurasi Laravel dan sesuaikan database lokal.

Contoh konfigurasi penting:

```env
APP_NAME=EduSkill
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eduskill
DB_USERNAME=root
DB_PASSWORD=

OPENAI_API_KEY=
OPENAI_MODEL=gpt-4o-mini

CRISP_ENABLED=false
CRISP_WEBSITE_ID=
```

## Testing

Jalankan test:

```bash
php artisan test
```

Cek syntax PHP tertentu:

```bash
php -l app/Http/Controllers/User/ListKursusController.php
```

## Catatan Upload

Folder upload berada di:

- `public/uploads/avatar`
- `public/uploads/kursus`
- `public/uploads/content-pdfs`

File upload dari penggunaan lokal sebaiknya tidak ikut masuk commit kecuali memang diperlukan sebagai aset bawaan.

## Status Terbaru

Fitur yang sudah aktif di versi saat ini:

- Prasyarat kursus
- Alur belajar bertahap dengan status terkunci/terbuka/selesai
- Kuis pilihan ganda dan esai
- Penilaian esai manual oleh admin
- Review hasil esai user
- Generate soal otomatis dari teks/PDF
- Integrity mode dan log pelanggaran
- Keystroke dynamics untuk esai
- Suspend user
- Sertifikat kursus
- Explore Your Path
- Backup database

Prioritas pengembangan berikutnya:

- Memperkuat validasi edge case pada alur belajar bertahap
- Menambah test untuk flow kuis dan penilaian esai
- Merapikan file upload lokal agar tidak tercampur dengan source code
- Memastikan integrasi chat tidak membawa sesi user lama
