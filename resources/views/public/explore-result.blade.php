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

        .recommended-box {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem 1.4rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: #fff;
            text-align: left;
        }

        .recommended-icon {
            flex-shrink: 0;
            width: 58px;
            height: 58px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .18);
            font-size: 1.9rem;
        }

        .recommended-eyebrow {
            display: block;
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            opacity: .85;
        }

        .score-row {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin-bottom: .55rem;
        }

        .score-label {
            flex: 0 0 132px;
            font-size: .82rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: .35rem;
        }

        .score-label i {
            color: #4f46e5;
        }

        .score-track {
            flex: 1;
            height: 10px;
            border-radius: 999px;
            background: #eceef3;
            overflow: hidden;
        }

        .score-fill {
            height: 100%;
            border-radius: 999px;
            background: #c7cbd4;
            transition: width .4s ease;
        }

        .score-fill.is-top {
            background: linear-gradient(90deg, #4f46e5, #6366f1);
        }

        .score-value {
            flex: 0 0 32px;
            text-align: right;
            font-size: .78rem;
            font-weight: 700;
            color: #6b7280;
        }

        @media (max-width: 480px) {
            .score-label {
                flex-basis: 104px;
                font-size: .75rem;
            }
        }

        [data-bs-theme="dark"] .score-track {
            background: rgba(255, 255, 255, .08);
        }

        [data-bs-theme="dark"] .score-fill {
            background: rgba(148, 163, 184, .5);
        }

        [data-bs-theme="dark"] .score-label i {
            color: #a5b4fc;
        }

        [data-bs-theme="dark"] .score-value {
            color: #9ca3af;
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
                        <h3 class="fw-bold mb-1">Rekomendasi Belajarmu</h3>
                        <p class="text-muted mb-4">Disusun dari jawaban kuesioner minatmu.</p>

                        <div class="recommended-box mb-4">
                            <div class="recommended-icon">
                                <i class="ti {{ $categoryIcons[$result['recommended_category']] ?? 'ti-compass' }}"></i>
                            </div>
                            <div>
                                <span class="recommended-eyebrow">Kategori paling cocok</span>
                                <h3 class="fw-bold mb-1">{{ $categoryLabels[$result['recommended_category']] ?? '-' }}</h3>
                                <p class="mb-0 small">{{ $result['explanation'] }}</p>
                            </div>
                        </div>

                        @if (!empty($result['alternative_category']))
                            <p class="mb-4">
                                <i class="ti ti-arrow-badge-right text-primary me-1"></i>
                                Alternatif yang juga cocok:
                                <strong>{{ $categoryLabels[$result['alternative_category']] ?? '-' }}</strong>
                            </p>
                        @endif

                        @if (!empty($result['scores']))
                            <h5 class="mb-3">Rincian Minatmu</h5>
                            <div class="mb-4">
                                @php $topScore = max(1, max($result['scores'])); @endphp
                                @foreach ($result['scores'] as $cat => $score)
                                    <div class="score-row">
                                        <div class="score-label">
                                            <i class="ti {{ $categoryIcons[$cat] ?? 'ti-point' }}"></i>{{ $categoryLabels[$cat] ?? $cat }}
                                        </div>
                                        <div class="score-track">
                                            <div class="score-fill {{ $cat === $result['recommended_category'] ? 'is-top' : '' }}"
                                                style="width: {{ round(($score / $topScore) * 100) }}%"></div>
                                        </div>
                                        <div class="score-value">{{ $score }}/5</div>
                                    </div>
                                @endforeach
                            </div>
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
                        @else
                            <div class="alert alert-light border d-flex align-items-start gap-2 mb-4">
                                <i class="ti ti-info-circle text-primary fs-5"></i>
                                <span>Belum ada kursus aktif untuk kategori ini. Daftar sekarang &mdash; kami akan
                                    memberitahumu begitu kursus kategori ini tersedia.</span>
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
