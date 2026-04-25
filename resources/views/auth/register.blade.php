<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title>Sign Up &mdash; {{ config('app.name') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <meta name="color-scheme" content="light">

    <link rel="shortcut icon" href="{{ asset('assets/media/favicon/favicon.ico') }}" type="image/x-icon" />

    <!-- Primary Meta Tags -->
    <meta name="keywords"
        content="KembangIn Digital Nusantara, Software House, IT Consultant, Konsultan IT, Pengembangan Software, Jasa Pembuatan Aplikasi, Pembuatan Website, Sistem Informasi, Solusi IT, Digital Transformation, Web Development, Internet of Things, IoT, IT Support, Teknologi Informasi, Perusahaan IT Terbaik, IT Solution Provider, Aplikasi Custom, Software Development Company, Konsultan Digital, Transformasi Digital, IT Services, Software Engineering, Enterprise Solution, Aplikasi Bisnis, Startup Development, IT Infrastructure, Konsultan IT Lumajang, Konsultan IT Jember, Software House Indonesia, Eduskill, Belajaro, Kuncoro, Raply, Nopal" />
    <meta name="author" content="EduSkill" />
    <meta name="title" content="EduSkill — Learning Platform" />
    <meta name="description" content="EduSkill is a platform learning for student and family friendly" />

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://eduskill.me" />
    <meta property="og:author" content="EduSkill" />
    <meta property="og:title" content="EduSkill — Learning Platform" />
    <meta property="og:description" content="EduSkill is a platform learning for student and family friendly." />
    <meta property="og:image" content="{{ asset('assets/media/meta-thumb.png') }}" />

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image" />
    <meta property="twitter:url" content="https://eduskill.me" />
    <meta property="twitter:title" content="EduSkill — Learning Platform" />
    <meta property="twitter:description" content="EduSkill is a platform learning for student and family friendly." />
    <meta property="twitter:image" content="{{ asset('assets/media/meta-thumb.png') }}" />

    <!-- Theme Config Js -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <!-- Vendor css -->
    <link href="{{ asset('assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App css -->
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-style" />
    <!-- Icons css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Plugins css -->
    <link href="{{ asset('assets/plugins/jquery-confirm/jquery-confirm.min.css') }}" rel="stylesheet" />
</head>

<body>
    <div class="position-absolute top-0 end-0 m-3 d-flex gap-2 align-items-center">
        <!-- Tombol Light/Dark Mode -->
        <button id="light-dark-mode"
            class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center" type="button"
            title="Ganti Mode">
            <i class="ti ti-moon fs-20" id="theme-icon"></i>
        </button>
    </div>

    <div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center">
        <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
            <div class="col-xl-4 col-lg-5 col-md-6">
                <div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
                    <a href="{{ route('home.index') }}" class="auth-brand mb-3">
                        <img src="{{ asset('assets/media/logo/logo-dark.png') }}" alt="dark logo" height="100"
                            class="logo-dark">
                        <img src="{{ asset('assets/media/logo/logo.png') }}" alt="logo light" height="100"
                            class="logo-light">
                    </a>

                    <h4 class="fw-semibold mb-2">
                        Halaman Pendaftaran
                    </h4>

                    <p class="text-muted mb-4">
                        Silahkan isi formulir di bawah untuk membuat akun baru.
                    </p>

                    <form action="{{ route('auth.register') }}" method="POST" class="text-start mb-3">
                        @csrf
                        <div class="row g-3">
                            <!-- Nama Lengkap -->
                            <div class="col-md-12">
                                <label class="form-label" for="name">
                                    Nama Lengkap <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="name" name="name"
                                    class="form-control @error('name')
                                    is-invalid
                                @enderror"
                                    placeholder="Masukkan nama lengkap" autocomplete="off" value="{{ old('name') }}">

                                @error('name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6">
                                <label class="form-label" for="email">
                                    Alamat Email <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    placeholder="Masukkan alamat email" autocomplete="off"
                                    value="{{ old('email') }}">

                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Nomor Telepon -->
                            <div class="col-md-6">
                                <label class="form-label" for="phone">Nomor Telepon</label>
                                <input type="number" id="phone" name="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    placeholder="Masukkan nomor telepon" autocomplete="off"
                                    value="{{ old('phone') }}">

                                @error('phone')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Kata Sandi -->
                            <div class="col-md-12 position-relative">
                                <label class="form-label" for="password">
                                    Kata Sandi <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" id="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror"
                                        placeholder="Masukkan kata sandi" autocomplete="off">
                                    <span class="input-group-text bg-transparent" id="togglePassword"
                                        style="cursor: pointer;">
                                        <i class="ti ti-eye" id="passwordIcon"></i>
                                    </span>
                                    @error('password')
                                        <div class="invalid-feedback d-block">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Konfirmasi Kata Sandi -->
                            <div class="col-md-12 position-relative">
                                <label class="form-label" for="password_confirmation">
                                    Konfirmasi Kata Sandi <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="form-control @error('password_confirmation') is-invalid @enderror"
                                        placeholder="Masukkan kembali kata sandi" autocomplete="off">
                                    <span class="input-group-text bg-transparent" id="togglePasswordConfirm"
                                        style="cursor: pointer;">
                                        <i class="ti ti-eye" id="confirmIcon"></i>
                                    </span>
                                </div>

                                @error('password_confirmation')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Tombol Submit -->
                            <div class="col-12 d-grid">
                                @if (config('recaptcha.site_key'))
                                    <button type="submit" class="btn btn-primary g-recaptcha"
                                        data-sitekey="{{ config('recaptcha.site_key') }}" data-callback="onSubmit"
                                        data-action="submit">
                                        Daftar
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-primary">
                                        Daftar
                                    </button>
                                @endif
                            </div>
                        </div>
                    </form>

                    <p class="text-danger fs-14 mb-4">
                        Sudah memiliki akun? <a href="{{ route('auth.view') }}"
                            class="fw-semibold text-dark ms-1">Masuk
                            sekarang</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Vendor js -->
    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <!-- Plugins js -->
    <script src="{{ asset('assets/plugins/jquery-confirm/jquery-confirm.min.js') }}"></script>
    @if (config('recaptcha.site_key'))
        <!-- Google reCAPTCHA -->
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
    <!-- Page js -->
    <script>
        function onSubmit(token) {
            document.querySelector("form").submit();
        }

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('passwordIcon');

        togglePassword.addEventListener('click', () => {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            passwordIcon.className = isHidden ? 'ti ti-eye-off' : 'ti ti-eye';
        });

        const togglePasswordConfirm = document.getElementById('togglePasswordConfirm');
        const confirmInput = document.getElementById('password_confirmation');
        const confirmIcon = document.getElementById('confirmIcon');

        togglePasswordConfirm.addEventListener('click', () => {
            const isHidden = confirmInput.type === 'password';
            confirmInput.type = isHidden ? 'text' : 'password';
            confirmIcon.className = isHidden ? 'ti ti-eye-off' : 'ti ti-eye';
        });
    </script>

    <script>
        $(document).ready(function() {
            @if (\Illuminate\Support\Facades\Session::get('failed_message'))
                $.alert({
                    title: 'Peringatan',
                    content: '{{ \Illuminate\Support\Facades\Session::get('failed_message') }}',
                    type: 'red',
                    theme: 'material',
                    backgroundDismissAnimation: 'shake',
                    onOpenBefore: function() {
                        this.$title.css("color", "black");
                        this.$content.css("color", "black");
                    }
                });
            @endif

            @if (\Illuminate\Support\Facades\Session::get('success_message'))
                $.alert({
                    title: 'Informasi',
                    content: '{{ \Illuminate\Support\Facades\Session::get('success_message') }}',
                    type: 'green',
                    theme: 'material',
                    backgroundDismissAnimation: 'shake',
                    onOpenBefore: function() {
                        this.$title.css("color", "black");
                        this.$content.css("color", "black");
                    }
                });
            @endif
        });
    </script>
</body>

</html>
