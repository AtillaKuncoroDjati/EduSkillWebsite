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
</head>

<body>
    <div class="auth-bg d-flex min-vh-100 align-items-center py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card p-4 p-md-5">
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
                                            <div class="card border h-100">
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
