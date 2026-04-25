<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <title>Hasil Explore Your Path — EduSkill</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <link href="{{ asset('assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .result-card {
            border-radius: 1.25rem;
            border: 1px solid rgba(84, 74, 245, .14);
            box-shadow: 0 16px 40px rgba(17, 23, 41, 0.12);
        }

        .course-suggestion-card {
            overflow: hidden;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .course-suggestion-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(79, 70, 229, .15);
        }

        .course-thumb {
            width: 100%;
            aspect-ratio: 16 / 9;
            object-fit: cover;
            display: block;
            background-color: #f4f6ff;
        }

        .course-thumb-fallback {
            width: 100%;
            aspect-ratio: 16 / 9;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
        }

        .course-thumb-fallback i {
            font-size: 2.5rem;
            opacity: .85;
        }

        [data-bs-theme="dark"] .result-card {
            background: rgba(33, 37, 41, .92);
            border-color: rgba(129, 140, 248, .32);
            box-shadow: 0 18px 44px rgba(0, 0, 0, .45);
        }

        [data-bs-theme="dark"] .result-card .card.border {
            background: rgba(43, 47, 54, .95);
            border-color: rgba(129, 140, 248, .32) !important;
        }

        [data-bs-theme="dark"] .course-thumb {
            background-color: rgba(54, 59, 68, .95);
        }
    </style>
</head>

<body>
    <div class="position-absolute top-0 end-0 m-3 d-flex gap-2 align-items-center" style="z-index: 1050;">
        <button id="light-dark-mode"
            class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" type="button"
            title="Ganti Mode">
            <i class="ti ti-moon fs-20" id="theme-icon"></i>
        </button>
    </div>

    <div class="auth-bg d-flex min-vh-100 align-items-center py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card p-4 p-md-5 result-card">
                        <h3 class="fw-bold mb-3">Rekomendasi Belajarmu</h3>

                        <div class="alert alert-primary mb-3">
                            <p class="mb-1">Kategori yang paling cocok:</p>
                            <h4 class="mb-0 fw-bold">{{ $categoryLabels[$result['recommended_category']] ?? '-' }}</h4>
                        </div>

                        <p class="text-muted mb-2">{{ $result['explanation'] }}</p>

                        @if (!empty($result['alternative_category']))
                            <p class="mb-3">
                                Alternatif rekomendasi:
                                <strong>{{ $categoryLabels[$result['alternative_category']] ?? '-' }}</strong>
                            </p>
                        @endif

                        @if (!empty($result['suggested_courses']))
                            <div class="mb-4">
                                <h5 class="mb-3">Saran Kursus</h5>
                                <div class="row g-3">
                                    @foreach ($result['suggested_courses'] as $course)
                                        <div class="col-md-6">
                                            <div class="card border h-100 course-suggestion-card">
                                                @if (!empty($course['thumbnail']))
                                                    <img src="{{ asset('uploads/kursus/' . $course['thumbnail']) }}"
                                                        alt="Thumbnail {{ $course['title'] }}"
                                                        class="course-thumb"
                                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="course-thumb-fallback" style="display:none;">
                                                        <i class="ti ti-photo"></i>
                                                    </div>
                                                @else
                                                    <div class="course-thumb-fallback">
                                                        <i class="ti ti-photo"></i>
                                                    </div>
                                                @endif
                                                <div class="card-body">
                                                    <h6 class="mb-1">{{ $course['title'] }}</h6>
                                                    <small class="text-muted d-block mb-2">Level: {{ ucfirst($course['difficulty']) }}</small>
                                                    <small class="text-muted">{{ $course['short_description'] ?: 'Mulai belajar dari kategori yang paling sesuai dengan minatmu.' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="d-flex flex-wrap gap-2 justify-content-between">
                            <a href="{{ route('explore.index') }}" class="btn btn-light">Ulangi Kuesioner</a>
                            <div class="d-flex gap-2">
                                <a href="{{ route('auth.view') }}" class="btn btn-primary">Login</a>
                                <a href="{{ route('auth.register.view') }}" class="btn btn-outline-primary">Register</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>

</html>
