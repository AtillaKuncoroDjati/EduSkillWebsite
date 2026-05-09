{{-- resources/views/user/kursus/show.blade.php --}}
@extends('template', ['title' => $kursus->title])

@section('content')
    <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
        <div class="flex-grow-1">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('user.kursus.index') }}">Kursus</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $kursus->title }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row g-3">
        {{-- Kolom Kiri: Info Kursus --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    {{-- Thumbnail --}}
                    <div class="position-relative mb-4">
                        <img src="{{ asset('uploads/kursus/' . $kursus->thumbnail) }}" class="img-fluid rounded w-100"
                            style="max-height: 400px; object-fit: cover;" alt="{{ $kursus->title }}">
                    </div>

                    {{-- Title dan Badge --}}
                    <div class="mb-3">
                        <h2 class="fw-bold mb-3">{{ $kursus->title }}</h2>

                        <div class="d-flex flex-wrap gap-2 mb-3">
                            {{-- Category Badge --}}
                            @if ($kursus->category === 'programming')
                                <span class="badge bg-primary">
                                    <i class="ti ti-code"></i> Programming
                                </span>
                            @elseif ($kursus->category === 'design')
                                <span class="badge bg-success">
                                    <i class="ti ti-palette"></i> Design
                                </span>
                            @elseif ($kursus->category === 'marketing')
                                <span class="badge bg-info">
                                    <i class="ti ti-chart-bar"></i> Marketing
                                </span>
                            @elseif ($kursus->category === 'business')
                                <span class="badge bg-warning">
                                    <i class="ti ti-briefcase"></i> Bisnis
                                </span>
                            @elseif ($kursus->category === 'cybersecurity')
                                <span class="badge bg-danger">
                                    <i class="ti ti-shield-lock"></i> Cybersecurity
                                </span>
                            @endif

                            {{-- Difficulty Badge --}}
                            @if ($kursus->difficulty === 'pemula')
                                <span class="badge bg-info">
                                    <i class="ti ti-star"></i> Pemula
                                </span>
                            @elseif ($kursus->difficulty === 'menengah')
                                <span class="badge bg-warning">
                                    <i class="ti ti-star"></i> Menengah
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="ti ti-star"></i> Lanjutan
                                </span>
                            @endif

                            {{-- Certificate Badge --}}
                            @if ($kursus->certificate)
                                <span class="badge bg-primary">
                                    <i class="ti ti-certificate"></i> Dengan Sertifikat
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Short Description --}}
                    <div class="alert alert-light mb-4">
                        <p class="mb-0">{{ $kursus->short_description }}</p>
                    </div>

                    {{-- Prerequisites --}}
                    @if ($kursus->prerequisites->isNotEmpty())
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3"><i class="ti ti-lock me-1"></i> Prasyarat Kursus</h5>
                            <div class="list-group">
                                @foreach ($kursus->prerequisites as $prereq)
                                    @php
                                        $isMet = Auth::check() && !$unmetPrerequisites->contains('id', $prereq->id);
                                    @endphp
                                    <a href="{{ route('user.kursus.show', $prereq->id) }}"
                                        class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                                        @if ($isMet)
                                            <i class="ti ti-circle-check text-success fs-5"></i>
                                        @else
                                            <i class="ti ti-circle-x text-danger fs-5"></i>
                                        @endif
                                        <span>{{ $prereq->title }}</span>
                                        @if ($isMet)
                                            <span class="badge bg-success ms-auto">Selesai</span>
                                        @else
                                            <span class="badge bg-danger ms-auto">Belum Selesai</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Description --}}
                    <div class="mb-4">
                        <h5 class="fw-bold mb-3">Tentang Kursus Ini</h5>
                        <div class="text-muted">
                            {!! nl2br(e($kursus->description)) !!}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Daftar Modul --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ti ti-list"></i> Materi Kursus
                    </h5>
                </div>
                <div class="card-body">
                    @if ($kursus->modules->isEmpty())
                        <div class="text-center py-4 text-muted">
                            <i class="ti ti-folder-off fs-1"></i>
                            <p class="mt-2">
                                Maaf, modul untuk kursus ini belum tersedia. Silakan hubungi administrator untuk informasi
                                lebih lanjut atau coba lagi nanti.
                            </p>
                        </div>
                    @else
                        <div class="accordion" id="accordionModules">
                            @foreach ($kursus->modules as $index => $module)
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading{{ $module->id }}">
                                        <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }}"
                                            type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse{{ $module->id }}"
                                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                            aria-controls="collapse{{ $module->id }}">
                                            <strong>{{ $module->order }}. {{ $module->title }}</strong>
                                            <span class="badge bg-primary ms-2">{{ $module->contents->count() }}
                                                Materi</span>
                                        </button>
                                    </h2>
                                    <div id="collapse{{ $module->id }}"
                                        class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                        aria-labelledby="heading{{ $module->id }}" data-bs-parent="#accordionModules">
                                        <div class="accordion-body">
                                            @if ($module->contents->isEmpty())
                                                <p class="text-muted mb-0">Belum ada konten</p>
                                            @else
                                                <ul class="list-group list-group-flush">
                                                    @foreach ($module->contents as $content)
                                                        @php
                                                            $quizQuestionCount = $content->is_ai_generated
                                                                ? (int) ($content->ai_question_count ?? 5)
                                                                : $content->questions->count();
                                                        @endphp
                                                        <li
                                                            class="list-group-item d-flex justify-content-between align-items-center">
                                                            <div>
                                                                @if ($content->type === 'text')
                                                                    <i class="ti ti-file-text text-primary"></i>
                                                                @else
                                                                    <i class="ti ti-clipboard-list text-warning"></i>
                                                                @endif
                                                                <span
                                                                    class="ms-2">{{ $content->title ?? 'Materi ' . $content->order }}</span>
                                                            </div>
                                                            <div>
                                                                @if ($content->type === 'text')
                                                                    <span
                                                                        class="badge bg-soft-primary text-primary">Teks</span>
                                                                @else
                                                                    <span class="badge bg-soft-warning text-warning">Quiz
                                                                        ({{ $quizQuestionCount }} soal)
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Statistik & Action --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Informasi Kursus</h5>

                    <div class="d-grid gap-3">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded bg-soft-primary">
                                    <i class="ti ti-book fs-4 text-primary"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">{{ $totalModules }} Modul</h6>
                                <small class="text-muted">Total modul pembelajaran</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded bg-soft-success">
                                    <i class="ti ti-file-text fs-4 text-success"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">{{ $totalContents }} Materi</h6>
                                <small class="text-muted">Total konten pembelajaran</small>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <div class="avatar-sm rounded bg-soft-info">
                                    <i class="ti ti-clock fs-4 text-info"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="mb-0">{{ floor($estimatedDuration / 60) }} Jam {{ $estimatedDuration % 60 }}
                                    Menit</h6>
                                <small class="text-muted">Estimasi durasi</small>
                            </div>
                        </div>

                        @if ($kursus->certificate)
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm rounded bg-soft-warning">
                                        <i class="ti ti-certificate fs-4 text-warning"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0">Sertifikat</h6>
                                    <small class="text-muted">Tersedia sertifikat</small>
                                </div>
                            </div>
                        @endif
                    </div>

                    <hr class="my-4">

                    @if ($kursus->modules->isEmpty())
                        @php
                            $whatsappNumber = '6289530695776';
                            $message = "Halo Admin Eduskill,%0A%0ASaya ingin menanyakan tentang kursus *{$kursus->title}*.%0A%0ASaat ini kursus tersebut belum memiliki modul pembelajaran. Apakah modul akan segera ditambahkan?%0A%0AMohon informasinya. Terima kasih.";
                            $whatsappLink = "https://wa.me/{$whatsappNumber}?text={$message}";
                        @endphp

                        <a href="{{ $whatsappLink }}" target="_blank" class="btn btn-success w-100">
                            <i class="ti ti-brand-whatsapp"></i> Hubungi Administrator
                        </a>

                        <div class="alert alert-warning mt-3 mb-0">
                            <small>
                                <i class="ti ti-alert-circle"></i>
                                Modul belum tersedia. Klik tombol di atas untuk menghubungi admin.
                            </small>
                        </div>
                    @else
                        @auth
                            @if ($isEnrolled)
                                <a href="{{ route('user.kursus.learn', $kursus->id) }}" class="btn btn-primary w-100">
                                    <i class="ti ti-player-play me-1"></i> Lanjutkan Belajar
                                </a>

                                @if ($userCourse && $userCourse->progress_percentage > 0)
                                    <div class="mt-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small class="text-muted">Progress Anda</small>
                                            <small
                                                class="text-primary fw-bold">{{ $userCourse->progress_percentage }}%</small>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar"
                                                style="width: {{ $userCourse->progress_percentage }}%"></div>
                                        </div>
                                    </div>
                                @endif
                            @elseif ($isLocked)
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="ti ti-lock me-1"></i> Kursus Terkunci
                                </button>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <small>
                                        <i class="ti ti-alert-circle"></i>
                                        Selesaikan kursus prasyarat di atas untuk membuka kursus ini.
                                    </small>
                                </div>
                            @else
                                <form action="{{ route('user.kursus.enroll', $kursus->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="ti ti-player-play me-1"></i> Mulai Belajar
                                    </button>
                                </form>
                            @endif
                        @else
                            @if ($isLocked)
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="ti ti-lock me-1"></i> Kursus Terkunci
                                </button>
                                <div class="alert alert-warning mt-3 mb-0">
                                    <small>
                                        <i class="ti ti-alert-circle"></i>
                                        Login dan selesaikan kursus prasyarat untuk membuka kursus ini.
                                    </small>
                                </div>
                            @else
                                <a href="{{ route('auth.view') }}" class="btn btn-primary w-100">
                                    <i class="ti ti-login"></i> Login untuk Mulai Belajar
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .avatar-sm {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
@endpush
