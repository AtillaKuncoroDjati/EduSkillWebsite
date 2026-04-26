<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <title>Sign In &mdash; {{ config('app.name') }}</title>
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
                        <img src="{{ asset('assets/media/logo/logo-dark.png') }}" alt="dark logo" height="60"
                            class="logo-dark">
                        <img src="{{ asset('assets/media/logo/logo.png') }}" alt="logo light" height="60"
                            class="logo-light">
                    </a>

                    <h4 class="fw-semibold mb-2">
                        Halaman Masuk
                    </h4>

                    <p class="text-muted mb-4">
                        Sebelum melanjutkan, silahkan masuk ke akun Anda.
                    </p>

                    <form action="{{ route('auth.login') }}" method="POST" class="text-start mb-3">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="login">Alamat Email atau Username</label>
                            <input type="text" id="login" name="login" class="form-control"
                                placeholder="Masukkan username atau alamat email" autocomplete="off">
                        </div>

                        <div class="mb-3 position-relative">
                            <label class="form-label" for="password">Kata Sandi</label>
                            <div class="input-group">
                                <input type="password" id="password" name="password" class="form-control"
                                    placeholder="Masukkan kata sandi" autocomplete="off">
                                <span class="input-group-text bg-transparent" id="togglePassword"
                                    style="cursor: pointer;">
                                    <i class="ti ti-eye" id="passwordIcon"></i>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="remember" id="remember">
                                <label class="form-check-label" for="remember">Ingat saya</label>
                            </div>

                            <a href="{{ route('password.email') }}" class="text-muted border-bottom border-dashed">
                                Lupa kata sandi?
                            </a>
                        </div>

                        <div class="d-grid">
                            @if (config('recaptcha.site_key'))
                                <button type="submit" class="btn btn-primary g-recaptcha"
                                    data-sitekey="{{ config('recaptcha.site_key') }}" data-callback="onSubmit"
                                    data-action="submit">
                                    Masuk
                                </button>
                            @else
                                <button type="submit" class="btn btn-primary">
                                    Masuk
                                </button>
                            @endif
                        </div>
                    </form>

                    <p class="text-danger fs-14 mb-4">
                        Tidak memiliki akun? <a href="{{ route('auth.register.view') }}"
                            class="fw-semibold text-dark ms-1">Daftar
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
    </script>

    <script>
        function formatRemainingDuration(seconds) {
            if (seconds <= 0) return '0 detik';
            var days = Math.floor(seconds / 86400);
            seconds %= 86400;
            var hours = Math.floor(seconds / 3600);
            seconds %= 3600;
            var minutes = Math.floor(seconds / 60);
            var secs = seconds % 60;
            if (days > 0) return hours > 0 ? days + ' hari ' + hours + ' jam' : days + ' hari';
            if (hours > 0) return minutes > 0 ? hours + ' jam ' + minutes + ' menit' : hours + ' jam';
            if (minutes > 0) return secs > 0 ? minutes + ' menit ' + secs + ' detik' : minutes + ' menit';
            return secs + ' detik';
        }

        $(document).ready(function() {
            @if (\Illuminate\Support\Facades\Session::get('failed_message'))
                var failedAlert = $.alert({
                    title: 'Peringatan',
                    content: @json(\Illuminate\Support\Facades\Session::get('failed_message')),
                    type: 'red',
                    theme: 'material',
                    backgroundDismissAnimation: 'shake',
                    onOpenBefore: function() {
                        this.$title.css("color", "black");
                        this.$content.css("color", "black");
                    }
                });

                @if (\Illuminate\Support\Facades\Session::get('suspended_until_iso'))
                    (function() {
                        var endTime = new Date(@json(\Illuminate\Support\Facades\Session::get('suspended_until_iso'))).getTime();
                        var reason = @json(\Illuminate\Support\Facades\Session::get('suspension_reason_text'));

                        function buildSuspensionMessage(remainingSeconds) {
                            if (remainingSeconds <= 0) {
                                return 'Periode suspensi telah berakhir. Silakan coba login kembali.';
                            }
                            var label = formatRemainingDuration(remainingSeconds);
                            var msg = 'Akun Anda sedang disuspend selama <strong>' + label + '</strong> lagi.';
                            if (reason) msg += ' Alasan: ' + reason + '.';
                            msg += ' Silakan coba lagi setelah periode suspensi berakhir.';
                            return msg;
                        }

                        function tick() {
                            var remaining = Math.floor((endTime - Date.now()) / 1000);
                            failedAlert.setContent(buildSuspensionMessage(remaining));
                            if (failedAlert.$content) failedAlert.$content.css("color", "black");
                            return remaining;
                        }

                        if (tick() > 0) {
                            var interval = setInterval(function() {
                                if (tick() <= 0) clearInterval(interval);
                            }, 1000);
                        }
                    })();
                @endif
            @endif

            @if (\Illuminate\Support\Facades\Session::get('success_message'))
                $.alert({
                    title: 'Informasi',
                    content: @json(\Illuminate\Support\Facades\Session::get('success_message')),
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
