<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <title>EduSkill — Explore Your Path</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <link href="{{ asset('assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .explore-hero {
            position: relative;
            overflow: hidden;
        }

        .explore-hero::before,
        .explore-hero::after {
            content: "";
            position: absolute;
            border-radius: 999px;
            z-index: 0;
            pointer-events: none;
        }

        .explore-hero::before {
            width: 280px;
            height: 280px;
            top: -90px;
            left: -110px;
            background: radial-gradient(circle, rgba(84, 74, 245, 0.18) 0%, rgba(84, 74, 245, 0) 70%);
        }

        .explore-hero::after {
            width: 300px;
            height: 300px;
            bottom: -130px;
            right: -120px;
            background: radial-gradient(circle, rgba(11, 191, 210, 0.16) 0%, rgba(11, 191, 210, 0) 70%);
        }

        .explore-card {
            position: relative;
            z-index: 1;
            border: 1px solid rgba(84, 74, 245, 0.15);
            border-radius: 1.25rem;
            box-shadow: 0 14px 40px rgba(23, 29, 51, 0.12);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(6px);
            transition: transform .28s ease, box-shadow .28s ease, border-color .28s ease;
        }

        .explore-card:hover {
            transform: translateY(-4px);
            border-color: rgba(84, 74, 245, 0.28);
            box-shadow: 0 20px 50px rgba(23, 29, 51, 0.16);
        }

        .explore-badge {
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
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .brand-logo {
            height: clamp(70px, 10vw, 94px);
            width: auto;
            filter: drop-shadow(0 6px 14px rgba(0, 0, 0, 0.08));
        }

        .hero-title {
            font-size: clamp(1.7rem, 4.1vw, 2.6rem);
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        .hero-subtitle {
            max-width: 620px;
            margin: 0 auto 1.25rem;
        }

        .cta-btn {
            transition: transform .22s ease, box-shadow .22s ease, background-color .22s ease;
        }

        .cta-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(18, 23, 38, 0.12);
        }

        .cta-btn-primary {
            box-shadow: 0 12px 22px rgba(79, 70, 229, 0.26);
        }

        .cta-btn-primary:hover {
            box-shadow: 0 16px 30px rgba(79, 70, 229, 0.35);
        }

        @media (max-width: 575.98px) {
            .explore-card {
                border-radius: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center explore-hero">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-md-10">
                    <div class="card p-4 p-md-5 text-center explore-card">
                        <div class="mb-3">
                            <img src="{{ asset('assets/media/logo/logo-dark.png') }}" alt="EduSkill" class="brand-logo">
                        </div>
                        <span class="explore-badge">
                            <i class="ti ti-sparkles"></i> Personalized Learning Start
                        </span>
                        <h1 class="fw-bold mb-3 hero-title">Temukan Jalur Belajarmu Bersama EduSkill</h1>
                        <p class="text-muted fs-15 hero-subtitle">
                            Jawab 5 pertanyaan singkat untuk menemukan kategori belajar yang paling cocok untukmu.
                        </p>

                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center align-items-stretch">
                            <a href="{{ route('explore.index') }}" class="btn btn-primary px-4 py-2 cta-btn cta-btn-primary">
                                <i class="ti ti-compass me-1"></i>Explore Your Path
                            </a>
                            <a href="{{ route('auth.view') }}" class="btn btn-outline-secondary px-4 py-2 cta-btn">Login</a>
                            <a href="{{ route('auth.register.view') }}" class="btn btn-outline-primary px-4 py-2 cta-btn">Register</a>
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
