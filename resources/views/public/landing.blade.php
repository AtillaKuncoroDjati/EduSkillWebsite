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
</head>

<body>
    <div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center">
        <div class="container py-4">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-9">
                    <div class="card p-4 p-md-5 text-center">
                        <h2 class="fw-bold mb-3">Explore Your Path</h2>
                        <p class="text-muted mb-4">Temukan kategori belajar paling cocok untukmu lewat 5 pertanyaan singkat.</p>

                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                            <a href="{{ route('explore.index') }}" class="btn btn-primary px-4">
                                <i class="ti ti-compass me-1"></i>Explore Your Path
                            </a>
                            <a href="{{ route('auth.view') }}" class="btn btn-outline-secondary px-4">Login</a>
                            <a href="{{ route('auth.register.view') }}" class="btn btn-outline-primary px-4">Register</a>
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
