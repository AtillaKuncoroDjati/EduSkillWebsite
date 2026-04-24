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
</head>

<body>
    <div class="auth-bg min-vh-100 py-4">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="card p-4 p-md-5">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="mb-0 fw-bold">Explore Your Path</h3>
                            <a href="{{ route('auth.view') }}" class="btn btn-sm btn-outline-secondary">Skip to Login</a>
                        </div>
                        <p class="text-muted">Jawab 5 pertanyaan berikut untuk mendapatkan rekomendasi kategori belajar.</p>

                        <form method="POST" action="{{ route('explore.submit') }}">
                            @csrf

                            @foreach ($questions as $index => $question)
                                <div class="card border mb-3">
                                    <div class="card-body">
                                        <h5 class="mb-3">{{ $index + 1 }}. {{ $question['text'] }}</h5>
                                        @foreach ($question['options'] as $value => $option)
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio"
                                                    name="answers[{{ $index }}]"
                                                    id="q{{ $index }}_{{ $loop->index }}"
                                                    value="{{ $value }}"
                                                    {{ old('answers.' . $index) === $value ? 'checked' : '' }} required>
                                                <label class="form-check-label" for="q{{ $index }}_{{ $loop->index }}">{{ $option }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                            @if ($errors->any())
                                <div class="alert alert-danger">Lengkapi semua jawaban sebelum lanjut.</div>
                            @endif

                            <div class="d-flex flex-wrap gap-2 justify-content-between">
                                <a href="{{ route('home.index') }}" class="btn btn-light">Kembali</a>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('auth.view') }}" class="btn btn-outline-secondary">Skip to Login</a>
                                    <button type="submit" class="btn btn-primary">Lihat Rekomendasi</button>
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
</body>

</html>
