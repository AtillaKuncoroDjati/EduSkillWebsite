<div class="sidenav-menu">

    <!-- Brand Logo -->
    <a href="{{ route('user.dashboard') }}" class="logo">
        <span class="logo-light">
            <span class="logo-lg">
                <img src="{{ asset('assets/media/logo/logo.png') }}" alt="logo">
            </span>
            <span class="logo-sm text-center">
                <img src="{{ asset('assets/media/logo/logo-sm.png') }}" alt="small logo">
            </span>
        </span>

        <span class="logo-dark">
            <span class="logo-lg">
                <img src="{{ asset('assets/media/logo/logo-dark.png') }}" alt="dark logo">
            </span>
            <span class="logo-sm text-center">
                <img src="{{ asset('assets/media/logo/logo-sm.png') }}" alt="small logo">
            </span>
        </span>
    </a>

    <!-- Sidebar Hover Menu Toggle Button -->
    <button class="button-sm-hover">
        <i class="ti ti-circle align-middle"></i>
    </button>

    <!-- Full Sidebar Menu Close Button -->
    <button class="button-close-fullsidebar">
        <i class="ti ti-x align-middle"></i>
    </button>

    <div data-simplebar>

        <!--- Sidenav Menu -->
        <ul class="side-nav">
            <!-- 1. Dashboard (always first) -->
            <li class="side-nav-item">
                <a href="{{ route('user.dashboard') }}" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-dashboard"></i></span>
                    <span class="menu-text"> Dashboard </span>
                </a>
            </li>

            <!-- 2. User Features -->
            <li class="side-nav-title mt-2">Menu Utama</li>

            @if (auth()->user()->permission === 'user')
                <li class="side-nav-item">
                    <a href="{{ route('user.kursus.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-book"></i></span>
                        <span class="menu-text"> Daftar Kursus </span>
                    </a>
                </li>
            @endif

            @if (auth()->user()->permission === 'admin')
                <li class="side-nav-item">
                    <a href="{{ route('admin.kursus.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-book"></i></span>
                        <span class="menu-text"> Kursus </span>
                    </a>
                </li>

                <li class="side-nav-item">
                    <a href="{{ route('admin.user.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-users"></i></span>
                        <span class="menu-text"> Pengguna </span>
                    </a>
                </li>


                <li class="side-nav-item">
                    <a href="{{ route('admin.kursus.integrity.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-shield-x"></i></span>
                        <span class="menu-text"> Integritas Kuis </span>
                    </a>
                </li>
            @endif

            <!-- 3. System Utilities -->
            <li class="side-nav-title mt-2">Sistem & Utilitas</li>

            @if (auth()->user()->permission === 'admin')
                <li class="side-nav-item">
                    <a href="{{ route('system.backup.index') }}" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-database"></i></span>
                        <span class="menu-text"> Backup Database </span>
                    </a>
                </li>
            @endif

            <li class="side-nav-item">
                @if (auth()->user()->permission === 'admin')
                    <a href="{{ asset('assets/pdf/manual-book-admin.pdf') }}" target="_blank" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-book-2"></i></span>
                        <span class="menu-text"> Panduan Sistem </span>
                    </a>
                @else
                    <a href="{{ asset('assets/pdf/manual-book.pdf') }}" target="_blank" class="side-nav-link">
                        <span class="menu-icon"><i class="ti ti-book-2"></i></span>
                        <span class="menu-text"> Panduan Sistem </span>
                    </a>
                @endif
            </li>

            <li class="side-nav-item">
                <a href="{{ route('clear.cache') }}" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-trash-x"></i></span>
                    <span class="menu-text"> Bersihkan Cache </span>
                </a>
            </li>
        </ul>

        <div class="clearfix"></div>
    </div>
</div>
