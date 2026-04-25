<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <title>Kuesioner Minat — EduSkill</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <link href="{{ asset('assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .questionnaire-shell {
            position: relative;
            overflow: hidden;
        }

        .questionnaire-shell::before,
        .questionnaire-shell::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            z-index: 0;
            pointer-events: none;
        }

        .questionnaire-shell::before {
            width: 260px;
            height: 260px;
            top: -90px;
            right: -80px;
            background: radial-gradient(circle, rgba(84, 74, 245, 0.15) 0%, rgba(84, 74, 245, 0) 70%);
        }

        .questionnaire-shell::after {
            width: 300px;
            height: 300px;
            left: -130px;
            bottom: -140px;
            background: radial-gradient(circle, rgba(11, 191, 210, 0.13) 0%, rgba(11, 191, 210, 0) 70%);
        }

        .questionnaire-card {
            position: relative;
            z-index: 1;
            border-radius: 1.25rem;
            border: 1px solid rgba(84, 74, 245, .14);
            background: rgba(255, 255, 255, .95);
            box-shadow: 0 18px 45px rgba(17, 23, 41, 0.12);
            backdrop-filter: blur(6px);
        }

        .brand-logo {
            height: clamp(60px, 9vw, 84px);
            width: auto;
            filter: drop-shadow(0 6px 14px rgba(0, 0, 0, 0.08));
        }

        .intro-badge {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            font-size: .75rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #4f46e5;
            background-color: rgba(79, 70, 229, .1);
            border: 1px solid rgba(79, 70, 229, .2);
            border-radius: 999px;
            padding: .35rem .75rem;
            font-weight: 700;
        }

        .question-card {
            border: 1px solid rgba(84, 74, 245, .15);
            border-radius: 1rem;
            box-shadow: 0 6px 18px rgba(22, 28, 45, .06);
            transition: transform .22s ease, box-shadow .22s ease, border-color .22s ease;
        }

        .question-card:hover {
            transform: translateY(-2px);
            border-color: rgba(84, 74, 245, .35);
            box-shadow: 0 12px 24px rgba(22, 28, 45, .09);
        }

        .question-number {
            width: 34px;
            height: 34px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            box-shadow: 0 8px 16px rgba(79, 70, 229, .3);
            margin-right: .5rem;
            flex-shrink: 0;
        }

        .option-radio {
            position: absolute;
            opacity: 0;
            inset: 0;
        }

        .option-item {
            display: block;
            cursor: pointer;
        }

        .option-card {
            width: 100%;
            border: 1px solid #d0d7e2;
            border-radius: .85rem;
            padding: .85rem 1rem;
            display: flex;
            align-items: start;
            gap: .65rem;
            transition: all .2s ease;
            background: #fff;
            position: relative;
        }

        .option-card::before {
            content: "";
            width: 20px;
            height: 20px;
            border-radius: 999px;
            border: 2px solid #98a2b3;
            margin-top: .15rem;
            flex-shrink: 0;
            transition: all .2s ease;
            background: #fff;
        }

        .option-card::after {
            content: "✓";
            position: absolute;
            right: .8rem;
            top: 50%;
            transform: translateY(-50%) scale(.6);
            opacity: 0;
            color: #fff;
            font-size: .75rem;
            font-weight: 700;
            width: 20px;
            height: 20px;
            border-radius: 999px;
            background: #4f46e5;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all .22s ease;
            box-shadow: 0 8px 14px rgba(79, 70, 229, .22);
        }

        .option-item:hover .option-card {
            border-color: #6d7afb;
            background: #f4f6ff;
            transform: translateY(-1px);
            box-shadow: 0 10px 16px rgba(79, 70, 229, .1);
        }

        .option-radio:checked + .option-card {
            border-color: #4f46e5;
            border-width: 2px;
            background: linear-gradient(180deg, #eef2ff 0%, #e0e7ff 100%);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, .18), 0 12px 24px rgba(79, 70, 229, .2);
            transform: translateY(-1px);
        }

        .option-radio:checked + .option-card::before {
            border-color: #4f46e5;
            background: radial-gradient(circle, #111827 42%, #fff 45%);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, .18);
        }

        .option-radio:checked + .option-card::after {
            opacity: 1;
            transform: translateY(-50%) scale(1);
        }

        .option-radio:focus-visible + .option-card {
            outline: 3px solid rgba(79, 70, 229, .28);
            outline-offset: 1px;
        }

        .btn-soft {
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .btn-soft:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 16px rgba(18, 23, 38, 0.12);
        }

        [data-bs-theme="dark"] .questionnaire-card {
            background: rgba(33, 37, 41, 0.92);
            border-color: rgba(129, 140, 248, .32);
            box-shadow: 0 18px 44px rgba(0, 0, 0, .45);
        }

        [data-bs-theme="dark"] .intro-badge {
            color: #c7d2fe;
            background-color: rgba(99, 102, 241, .22);
            border-color: rgba(129, 140, 248, .45);
        }

        [data-bs-theme="dark"] .question-card {
            background: rgba(43, 47, 54, .95);
            border-color: rgba(129, 140, 248, .27);
        }

        [data-bs-theme="dark"] .option-card {
            background: rgba(54, 59, 68, .95);
            border-color: rgba(148, 163, 184, .45);
            color: #e9ecef;
        }

        [data-bs-theme="dark"] .option-item:hover .option-card {
            background: rgba(64, 70, 82, .95);
            border-color: rgba(129, 140, 248, .6);
        }

        [data-bs-theme="dark"] .option-radio:checked + .option-card {
            background: linear-gradient(180deg, rgba(79, 70, 229, .35) 0%, rgba(67, 56, 202, .35) 100%);
            border-color: rgba(129, 140, 248, .9);
            box-shadow: 0 0 0 3px rgba(129, 140, 248, .3), 0 12px 24px rgba(14, 17, 30, .35);
        }

        [data-bs-theme="dark"] .option-radio:checked + .option-card::before {
            border-color: #a5b4fc;
            background: radial-gradient(circle, #020617 42%, rgba(43, 47, 54, .95) 45%);
            box-shadow: 0 0 0 4px rgba(129, 140, 248, .25);
        }

        [data-bs-theme="dark"] .option-card::after {
            background: #818cf8;
            box-shadow: 0 10px 18px rgba(129, 140, 248, .25);
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

    <div class="auth-bg min-vh-100 py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="card p-4 p-md-5 questionnaire-card questionnaire-shell">
                        <div class="text-center mb-4">
                            <img src="{{ asset('assets/media/logo/logo-dark.png') }}" alt="EduSkill" class="brand-logo mb-3">
                            <div>
                                <span class="intro-badge"><i class="ti ti-sparkles"></i> Personalized Learning Start</span>
                            </div>
                            <h2 class="fw-bold mt-3 mb-2">Explore Your Path</h2>
                            <p class="text-muted mb-0">Jawab 5 pertanyaan singkat untuk menemukan kategori belajar yang paling cocok untukmu.</p>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                            <small class="fw-semibold text-primary" id="progress-text">Progress: 0/5 pertanyaan terjawab</small>
                            <a href="{{ route('auth.view') }}" class="btn btn-sm btn-outline-secondary btn-soft">Skip to Login</a>
                        </div>
                        <div class="progress mb-4" style="height: 8px;">
                            <div class="progress-bar bg-primary" id="answer-progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>

                        <form method="POST" action="{{ route('explore.submit') }}">
                            @csrf

                            @foreach ($questions as $index => $question)
                                <div class="card question-card mb-3">
                                    <div class="card-body">
                                        <h5 class="mb-3 d-flex align-items-center">
                                            <span class="question-number">{{ $index + 1 }}</span>
                                            <span>{{ $question['text'] }}</span>
                                        </h5>
                                        @foreach ($question['options'] as $option)
                                            <label class="mb-2 position-relative option-item" for="q{{ $index }}_{{ $loop->index }}">
                                                <input class="option-radio"
                                                    name="answers[{{ $index }}]"
                                                    id="q{{ $index }}_{{ $loop->index }}"
                                                    value="{{ $loop->iteration }}"
                                                    {{ old('answers.' . $index) == (string) $loop->iteration ? 'checked' : '' }} required>
                                                <span class="option-card">{{ $option }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            @if ($errors->any())
                                <div class="alert alert-danger">Lengkapi semua jawaban sebelum lanjut.</div>
                            @endif

                            <div class="d-flex flex-wrap gap-2 justify-content-between">
                                <a href="{{ route('home.index') }}" class="btn btn-light btn-soft">Kembali</a>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('auth.view') }}" class="btn btn-outline-secondary btn-soft">Skip to Login</a>
                                    <button type="submit" class="btn btn-primary btn-soft px-4">Lihat Hasil Rekomendasi</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script>
        function updateAnswerProgress() {
            const totalQuestions = {{ count($questions) }};
            const answered = document.querySelectorAll('.question-card input[type="radio"]:checked').length;
            const progress = Math.round((answered / totalQuestions) * 100);

            const progressBar = document.getElementById('answer-progress-bar');
            const progressText = document.getElementById('progress-text');

            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
            progressText.textContent = 'Progress: ' + answered + '/' + totalQuestions + ' pertanyaan terjawab';
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.question-card input[type="radio"]').forEach(function(radio) {
                radio.addEventListener('change', updateAnswerProgress);
            });
            updateAnswerProgress();
        });
    </script>
</body>

</html>
