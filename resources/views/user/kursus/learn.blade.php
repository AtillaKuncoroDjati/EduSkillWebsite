@extends('template', ['title' => 'Belajar - ' . $kursus->title])

@section('content')
    <div class="row g-0">
        <div class="d-lg-none position-fixed bottom-0 start-0 p-3" style="z-index: 1050;">
            <button class="btn btn-primary shadow-lg" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
                <i class="ti ti-menu-2 me-1"></i> Daftar Materi
            </button>
        </div>

        <div class="col-lg-3 border-end d-none d-lg-block" style="height: calc(100vh - 70px); overflow-y: auto;">
            <div class="p-3 border-bottom bg-light sticky-top">
                <a href="{{ route('user.kursus.show', $kursus->id) }}" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="ti ti-arrow-left"></i> Kembali
                </a>
                <h6 class="mb-1 fw-bold">{{ $kursus->title }}</h6>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-primary" role="progressbar"
                        style="width: {{ $userCourse->progress_percentage }}%"></div>
                </div>
                <small class="text-muted">Progress: {{ $userCourse->progress_percentage }}%</small>
            </div>

            <div class="accordion accordion-flush" id="accordionModules">
                @foreach ($kursus->modules as $moduleIndex => $module)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingModule{{ $module->id }}">
                            <button class="accordion-button {{ $moduleIndex === 0 ? '' : 'collapsed' }}" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseModule{{ $module->id }}"
                                aria-expanded="{{ $moduleIndex === 0 ? 'true' : 'false' }}">
                                <strong class="small">{{ $module->order }}. {{ $module->title }}</strong>
                            </button>
                        </h2>
                        <div id="collapseModule{{ $module->id }}"
                            class="accordion-collapse collapse {{ $moduleIndex === 0 ? 'show' : '' }}"
                            data-bs-parent="#accordionModules">
                            <div class="accordion-body p-0">
                                @foreach ($module->contents as $content)
                                    @php
                                        $isCompleted = $contentProgress[$content->id] ?? false;
                                    @endphp
                                    <a href="#"
                                        class="d-flex align-items-center text-decoration-none content-item p-3 border-bottom"
                                        data-content-id="{{ $content->id }}" data-content-type="{{ $content->type }}"
                                        onclick="loadContent('{{ $content->id }}', '{{ $content->type }}'); return false;">
                                        <div class="flex-shrink-0 me-2">
                                            @if ($isCompleted)
                                                <i class="ti ti-circle-check text-success fs-5"></i>
                                            @else
                                                @if ($content->type === 'text')
                                                    <i class="ti ti-file-text text-muted"></i>
                                                @else
                                                    <i class="ti ti-clipboard-list text-warning"></i>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <small class="d-block text-dark">
                                                {{ $content->title ?? 'Materi ' . $content->order }}
                                            </small>
                                            <small class="text-muted">
                                                {{ $content->type === 'text' ? 'Teks' : 'Quiz' }}
                                            </small>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarOffcanvas"
            aria-labelledby="sidebarOffcanvasLabel" style="width: 300px;">
            <div class="offcanvas-header border-bottom bg-light">
                <div class="flex-grow-1">
                    <h6 class="mb-1 fw-bold" id="sidebarOffcanvasLabel">{{ $kursus->title }}</h6>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar"
                            style="width: {{ $userCourse->progress_percentage }}%"></div>
                    </div>
                    <small class="text-muted">Progress: {{ $userCourse->progress_percentage }}%</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body p-0">
                <div class="p-3 border-bottom">
                    <a href="{{ route('user.kursus.show', $kursus->id) }}" class="btn btn-sm btn-outline-secondary w-100">
                        <i class="ti ti-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="accordion accordion-flush" id="accordionModulesMobile">
                    @foreach ($kursus->modules as $moduleIndex => $module)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingModuleMobile{{ $module->id }}">
                                <button class="accordion-button {{ $moduleIndex === 0 ? '' : 'collapsed' }}" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#collapseModuleMobile{{ $module->id }}"
                                    aria-expanded="{{ $moduleIndex === 0 ? 'true' : 'false' }}">
                                    <strong class="small">{{ $module->order }}. {{ $module->title }}</strong>
                                </button>
                            </h2>
                            <div id="collapseModuleMobile{{ $module->id }}"
                                class="accordion-collapse collapse {{ $moduleIndex === 0 ? 'show' : '' }}"
                                data-bs-parent="#accordionModulesMobile">
                                <div class="accordion-body p-0">
                                    @foreach ($module->contents as $content)
                                        @php
                                            $isCompleted = $contentProgress[$content->id] ?? false;
                                        @endphp
                                        <a href="#"
                                            class="d-flex align-items-center text-decoration-none content-item p-3 border-bottom"
                                            data-content-id="{{ $content->id }}" data-content-type="{{ $content->type }}"
                                            data-bs-dismiss="offcanvas"
                                            onclick="loadContent('{{ $content->id }}', '{{ $content->type }}'); return false;">
                                            <div class="flex-shrink-0 me-2">
                                                @if ($isCompleted)
                                                    <i class="ti ti-circle-check text-success fs-5"></i>
                                                @else
                                                    @if ($content->type === 'text')
                                                        <i class="ti ti-file-text text-muted"></i>
                                                    @else
                                                        <i class="ti ti-clipboard-list text-warning"></i>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <small class="d-block text-dark">
                                                    {{ $content->title ?? 'Materi ' . $content->order }}
                                                </small>
                                                <small class="text-muted">
                                                    {{ $content->type === 'text' ? 'Teks' : 'Quiz' }}
                                                </small>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-9">
            @if ($userCourse->status === 'completed' && $kursus->certificate)
                <div class="alert alert-success shadow-sm mb-4">
                    <div class="row align-items-center">
                        <div class="col-auto">
                            <i class="ti ti-trophy" style="font-size: 48px;"></i>
                        </div>
                        <div class="col">
                            <h5 class="alert-heading mb-2">
                                <i class="ti ti-confetti"></i> Selamat! Kursus Telah Selesai
                            </h5>
                            <p class="mb-3">
                                Anda telah berhasil menyelesaikan kursus <strong>{{ $kursus->title }}</strong>
                                pada tanggal {{ $userCourse->completed_at->locale('id')->translatedFormat('d F Y') }}.
                            </p>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('user.certificate.preview', $userCourse->id) }}"
                                    class="btn btn-success">
                                    <i class="ti ti-eye me-1"></i>Preview Sertifikat
                                </a>
                                <a href="{{ route('user.certificate.download', $userCourse->id) }}"
                                    class="btn btn-primary">
                                    <i class="ti ti-download me-1"></i>Download Sertifikat
                                </a>
                                <a href="{{ route('user.kursus.index') }}" class="btn btn-soft-secondary">
                                    <i class="ti ti-book me-1"></i>Jelajahi Kursus Lain
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="p-4" id="content-area" style="height: calc(100vh - 70px); overflow-y: auto;">
                <div id="welcome-screen">
                    <div class="text-center py-5">
                        <i class="ti ti-book-2 text-primary" style="font-size: 80px;"></i>
                        <h3 class="mt-4 mb-3">Selamat Datang di {{ $kursus->title }}</h3>
                        <p class="text-muted">Pilih materi dari sidebar untuk mulai belajar</p>

                        <div class="row g-3 mt-4 justify-content-center">
                            <div class="col-md-4">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <i class="ti ti-book text-primary fs-2"></i>
                                        <h5 class="mt-3 mb-2">{{ $kursus->modules->count() }} Modul</h5>
                                        <small class="text-muted">Total modul pembelajaran</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <i class="ti ti-file-text text-success fs-2"></i>
                                        <h5 class="mt-3 mb-2">
                                            {{ $kursus->modules->sum(fn($m) => $m->contents->count()) }} Materi
                                        </h5>
                                        <small class="text-muted">Total konten</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border">
                                    <div class="card-body text-center">
                                        <i class="ti ti-chart-line text-info fs-2"></i>
                                        <h5 class="mt-3 mb-2">{{ $userCourse->progress_percentage }}%</h5>
                                        <small class="text-muted">Progress Anda</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button class="btn btn-primary" onclick="startFirstContent()">
                                <i class="ti ti-player-play me-1"></i> Mulai Belajar Sekarang
                            </button>
                        </div>
                    </div>
                </div>

                <div id="content-display" style="display: none;"></div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .content-item {
            transition: all 0.2s;
        }

        .content-item:hover {
            background-color: #f8f9fa;
        }

        .bg-soft-primary {
            background-color: rgba(13, 110, 253, 0.1);
        }

        .sticky-top {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        @media (max-width: 991.98px) {
            #content-area {
                padding-bottom: 80px !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentContentId = null;
        let currentQuizAttemptId = null;
        let integrityState = {
            active: false,
            enabled: false,
            requireFullscreen: false,
            maxViolations: 0,
            currentViolations: 0,
            autoSubmitted: false,
        };

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function resetIntegrityState() {
            currentQuizAttemptId = null;
            integrityState = {
                active: false,
                enabled: false,
                requireFullscreen: false,
                maxViolations: 0,
                currentViolations: 0,
                autoSubmitted: false,
            };
        }

        function updateViolationCounter() {
            const counter = $('#integrity-counter');
            if (!counter.length) return;
            counter.text('Pelanggaran: ' + integrityState.currentViolations + '/' + integrityState.maxViolations);
        }

        function requestFullscreenMode() {
            if (!integrityState.requireFullscreen) return Promise.resolve();

            const el = document.documentElement;
            if (document.fullscreenElement) return Promise.resolve();

            if (el.requestFullscreen) {
                return el.requestFullscreen().catch(() => Promise.reject());
            }
            return Promise.reject();
        }

        function startIntegrityMonitoring() {
            if (!integrityState.enabled) return;
            integrityState.active = true;

            document.addEventListener('visibilitychange', handleVisibilityViolation);
            window.addEventListener('blur', handleWindowBlurViolation);
            document.addEventListener('fullscreenchange', handleFullscreenViolation);
        }

        function stopIntegrityMonitoring() {
            integrityState.active = false;
            document.removeEventListener('visibilitychange', handleVisibilityViolation);
            window.removeEventListener('blur', handleWindowBlurViolation);
            document.removeEventListener('fullscreenchange', handleFullscreenViolation);
        }

        function handleVisibilityViolation() {
            if (!integrityState.active || !integrityState.enabled || integrityState.autoSubmitted) return;
            if (document.hidden) {
                logIntegrityViolation('tab_switch');
            }
        }

        function handleWindowBlurViolation() {
            if (!integrityState.active || !integrityState.enabled || integrityState.autoSubmitted) return;
            logIntegrityViolation('window_blur');
        }

        function handleFullscreenViolation() {
            if (!integrityState.active || !integrityState.enabled || !integrityState.requireFullscreen || integrityState.autoSubmitted)
                return;
            if (!document.fullscreenElement) {
                logIntegrityViolation('fullscreen_exit');
            }
        }

        function logIntegrityViolation(type) {
            if (!currentQuizAttemptId) return;

            $.ajax({
                url: '/user/daftar-kursus/{{ $kursus->id }}/quiz/' + currentContentId + '/integrity-log',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    attempt_id: currentQuizAttemptId,
                    event_type: type
                },
                success: function(response) {
                    if (!response.success) return;

                    integrityState.currentViolations = response.violation_count || integrityState.currentViolations;
                    updateViolationCounter();

                    if (response.is_auto_submitted) {
                        integrityState.autoSubmitted = true;
                        stopIntegrityMonitoring();
                        showQuizResult(response);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Kuis Otomatis Dikirim',
                            text: response.message ||
                                'Kuis dikirim otomatis karena melebihi batas pelanggaran integritas.',
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'warning',
                        title: 'Peringatan Integritas',
                        html: 'Aktivitas mencurigakan terdeteksi.<br><strong>Pelanggaran: ' +
                            integrityState.currentViolations + '/' + integrityState.maxViolations + '</strong>',
                    });
                }
            });
        }

        function loadContent(contentId, contentType) {
            currentContentId = contentId;
            resetIntegrityState();

            $('.content-item').removeClass('bg-soft-primary');
            $('[data-content-id="' + contentId + '"]').addClass('bg-soft-primary');

            $('#welcome-screen').hide();
            $('#content-display').show();

            $('#content-display').html(
                '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Memuat konten...</p></div>'
            );

            $.ajax({
                url: '/user/daftar-kursus/{{ $kursus->id }}/content/' + contentId,
                type: 'GET',
                success: function(response) {
                    if (contentType === 'text') {
                        renderTextContent(response);
                    } else if (contentType === 'quiz') {
                        renderQuizContent(response);
                    }
                },
                error: function() {
                    $('#content-display').html(
                        '<div class="alert alert-danger"><i class="ti ti-alert-circle"></i> Gagal memuat konten</div>'
                    );
                }
            });
        }

        function renderTextContent(data) {
            let html = '<div class="content-wrapper">';
            html += '<h3 class="mb-4">' + escapeHtml(data.title || 'Materi') + '</h3>';
            html += '<div class="card"><div class="card-body"><div class="content-text">';
            html += data.content || '<p class="text-muted">Tidak ada konten</p>';
            html += '</div></div></div>';
            html += '<div class="d-flex justify-content-between mt-4">';
            html +=
                '<button class="btn btn-outline-secondary" onclick="previousContent()"><i class="ti ti-arrow-left"></i> Sebelumnya</button>';
            html +=
                '<button class="btn btn-primary" onclick="nextContent()">Selanjutnya <i class="ti ti-arrow-right"></i></button>';
            html += '</div></div>';

            $('#content-display').html(html);
        }

        function renderQuizContent(data) {
            if (data.already_passed) {
                renderQuizReview(data);
                return;
            }

            const integrity = data.integrity_settings || {
                enabled: false,
                require_fullscreen: false,
                max_violations: 0
            };

            let html = '<div class="quiz-wrapper">';
            html += '<h3 class="mb-4">' + escapeHtml(data.title || 'Quiz') + '</h3>';
            html += '<div class="alert alert-info"><i class="ti ti-info-circle"></i> Quiz ini terdiri dari ' + data
                .questions.length + ' pertanyaan. Minimal nilai 70% untuk lulus.</div>';

            if (integrity.enabled) {
                html += '<div class="card border-warning mb-3" id="integrity-rules-card"><div class="card-body">';
                html += '<h5 class="mb-3"><i class="ti ti-shield-lock me-2"></i>Quiz Integrity Mode</h5>';
                html += '<ul class="mb-3">';
                html += '<li>Perpindahan tab akan dipantau.</li>';
                html += '<li>Kehilangan fokus browser akan dipantau.</li>';
                if (integrity.require_fullscreen) {
                    html += '<li>Fullscreen wajib selama kuis berlangsung.</li>';
                }
                html += '<li>Pelanggaran akan dihitung dan disimpan.</li>';
                html += '<li>Kuis dapat otomatis dikirim jika pelanggaran mencapai batas.</li>';
                html += '</ul>';
                html += '<p class="mb-3" id="integrity-counter">Pelanggaran: 0/' + integrity.max_violations + '</p>';
                html +=
                    '<button class="btn btn-warning" type="button" onclick="startQuizWithIntegrity('' + data.id + '', ' +
                    integrity.require_fullscreen + ', ' + integrity.max_violations + ')">Saya Mengerti & Mulai Kuis</button>';
                html += '</div></div>';
                html += '<div id="quiz-form-wrapper" style="display:none;">';
            }

            html += '<form id="quiz-form" onsubmit="submitQuiz(event, '' + data.id + '')">';

            data.questions.forEach(function(question, index) {
                html += '<div class="card mb-3"><div class="card-body">';
                html += '<h6 class="mb-3"><span class="badge bg-primary me-2">' + (index + 1) + '</span>' +
                    escapeHtml(question.question) + '</h6>';

                question.options.forEach(function(option) {
                    html += '<div class="form-check mb-2">';
                    html += '<input class="form-check-input" type="radio" name="question_' + question.id +
                        '" id="option_' + option.id + '" value="' + option.id + '" required>';
                    html += '<label class="form-check-label" for="option_' + option.id + '">' + escapeHtml(
                        option.option_text) + '</label>';
                    html += '</div>';
                });

                html += '</div></div>';
            });

            html += '<div class="d-flex justify-content-between mt-4">';
            html +=
                '<button type="button" class="btn btn-outline-secondary" onclick="previousContent()"><i class="ti ti-arrow-left"></i> Sebelumnya</button>';
            html += '<button type="submit" class="btn btn-success"><i class="ti ti-send"></i> Submit Jawaban</button>';
            html += '</div></form>';

            if (integrity.enabled) {
                html += '</div>';
            }

            html += '</div>';
            $('#content-display').html(html);

            integrityState.enabled = !!integrity.enabled;
            integrityState.requireFullscreen = !!integrity.require_fullscreen;
            integrityState.maxViolations = integrity.max_violations || 0;

            if (!integrity.enabled) {
                startQuizAttempt(data.id, false);
            }
        }

        function startQuizWithIntegrity(contentId, requireFullscreen, maxViolations) {
            startQuizAttempt(contentId, true).then(function() {
                integrityState.requireFullscreen = !!requireFullscreen;
                integrityState.maxViolations = maxViolations || 3;

                requestFullscreenMode().then(function() {
                    $('#integrity-rules-card').hide();
                    $('#quiz-form-wrapper').show();
                    startIntegrityMonitoring();
                    updateViolationCounter();
                }).catch(function() {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Fullscreen Diperlukan',
                        text: 'Silakan izinkan mode fullscreen untuk memulai kuis ini.'
                    });
                });
            });
        }

        function startQuizAttempt(contentId, integrityEnabled) {
            return $.ajax({
                url: '/user/daftar-kursus/{{ $kursus->id }}/quiz/' + contentId + '/start',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    currentQuizAttemptId = response.attempt_id;
                    integrityState.currentViolations = response.violation_count || 0;
                    integrityState.enabled = integrityEnabled;
                    integrityState.maxViolations = response.integrity_settings?.max_violations || integrityState.maxViolations;
                    integrityState.requireFullscreen = response.integrity_settings?.require_fullscreen || integrityState.requireFullscreen;
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Gagal memulai attempt kuis.'
                    });
                }
            });
        }

        function renderQuizReview(data) {
            const attempt = data.attempt;
            const scoreClass = attempt.score >= 70 ? 'success' : 'danger';

            let html = '<div class="quiz-review-wrapper">';
            html += '<h3 class="mb-4">' + escapeHtml(data.title || 'Quiz') + '</h3>';
            html += '<div class="alert alert-success mb-4"><div class="d-flex align-items-center">';
            html += '<i class="ti ti-circle-check fs-2 me-3"></i><div>';
            html += '<h5 class="mb-1">Quiz Sudah Diselesaikan</h5>';
            html += '<p class="mb-0">Anda telah menyelesaikan quiz ini pada ' + attempt.completed_at + '</p>';
            html += '</div></div></div>';

            html += '<div class="row g-3 mb-4">';
            html += '<div class="col-md-3"><div class="card border-' + scoreClass + '"><div class="card-body text-center">';
            html += '<i class="ti ti-star fs-2 text-' + scoreClass + '"></i>';
            html += '<h4 class="mt-2 mb-0 text-' + scoreClass + '">' + attempt.score + '%</h4>';
            html += '<small class="text-muted">Nilai Anda</small></div></div></div>';

            html += '<div class="col-md-3"><div class="card border-success"><div class="card-body text-center">';
            html += '<i class="ti ti-check fs-2 text-success"></i>';
            html += '<h4 class="mt-2 mb-0 text-success">' + attempt.correct_answers + '</h4>';
            html += '<small class="text-muted">Jawaban Benar</small></div></div></div>';

            html += '<div class="col-md-3"><div class="card border-danger"><div class="card-body text-center">';
            html += '<i class="ti ti-x fs-2 text-danger"></i>';
            html += '<h4 class="mt-2 mb-0 text-danger">' + (attempt.total_questions - attempt.correct_answers) + '</h4>';
            html += '<small class="text-muted">Jawaban Salah</small></div></div></div>';

            html += '<div class="col-md-3"><div class="card border-info"><div class="card-body text-center">';
            html += '<i class="ti ti-list fs-2 text-info"></i>';
            html += '<h4 class="mt-2 mb-0 text-info">' + attempt.total_questions + '</h4>';
            html += '<small class="text-muted">Total Soal</small></div></div></div>';
            html += '</div>';

            html += '<h5 class="mb-3"><i class="ti ti-clipboard-text me-2"></i>Review Jawaban</h5>';

            data.quiz_details.forEach(function(item, index) {
                const borderClass = item.user_is_correct ? 'border-success' : 'border-danger';
                html += '<div class="card mb-3 ' + borderClass + '"><div class="card-body">';
                html += '<div class="d-flex justify-content-between align-items-start mb-3">';
                html += '<h6 class="mb-0"><span class="badge bg-primary me-2">' + (index + 1) + '</span>' +
                    escapeHtml(item.question) + '</h6>';
                html += item.user_is_correct ?
                    '<span class="badge bg-success"><i class="ti ti-check"></i> Benar</span>' :
                    '<span class="badge bg-danger"><i class="ti ti-x"></i> Salah</span>';
                html += '</div>';

                item.options.forEach(function(option) {
                    let classes = 'p-2 rounded mb-2';
                    let icon = '';
                    let badge = '';

                    if (option.is_correct) {
                        classes += ' bg-success bg-opacity-10 border border-success';
                        icon = '<i class="ti ti-check text-success me-2"></i>';
                        badge = '<span class="badge bg-success ms-2">Jawaban Benar</span>';
                    } else if (option.is_selected && !option.is_correct) {
                        classes += ' bg-danger bg-opacity-10 border border-danger';
                        icon = '<i class="ti ti-x text-danger me-2"></i>';
                        badge = '<span class="badge bg-danger ms-2">Jawaban Anda</span>';
                    }

                    html += '<div class="' + classes + '">' + icon + escapeHtml(option.option_text) +
                        badge + '</div>';
                });

                html += '</div></div>';
            });

            html += '<div class="d-flex justify-content-between mt-4">';
            html +=
                '<button class="btn btn-outline-secondary" onclick="previousContent()"><i class="ti ti-arrow-left"></i> Sebelumnya</button>';
            html +=
                '<button class="btn btn-primary" onclick="nextContent()">Selanjutnya <i class="ti ti-arrow-right"></i></button>';
            html += '</div></div>';

            $('#content-display').html(html);
        }

        function markAsComplete(contentId) {
            return $.ajax({
                url: '/user/daftar-kursus/{{ $kursus->id }}/content/' + contentId + '/complete',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('[data-content-id="' + contentId + '"] i').removeClass().addClass(
                            'ti ti-circle-check text-success fs-5');
                        if (response.progress) {
                            $('.progress-bar').css('width', response.progress + '%');
                            $('.progress-bar').parent().siblings('small').text('Progress: ' + response
                                .progress + '%');
                        }
                    }
                }
            });
        }

        function submitQuiz(event, contentId) {
            event.preventDefault();
            stopIntegrityMonitoring();

            const formData = new FormData(event.target);
            const answers = {};

            for (let [key, value] of formData.entries()) {
                answers[key] = value;
            }

            $.ajax({
                url: '/user/daftar-kursus/{{ $kursus->id }}/quiz/' + contentId + '/submit',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    answers: answers,
                    attempt_id: currentQuizAttemptId
                },
                success: function(response) {
                    if (response.success) {
                        showQuizResult(response);
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat submit quiz'
                    });
                }
            });
        }

        function showQuizResult(result) {
            integrityState.autoSubmitted = !!result.is_auto_submitted;
            stopIntegrityMonitoring();

            let html = '<div class="text-center py-5">';
            html += result.is_passed ? '<i class="ti ti-circle-check text-success" style="font-size: 80px;"></i>' :
                '<i class="ti ti-circle-x text-danger" style="font-size: 80px;"></i>';
            html += '<h3 class="mt-4 mb-3">' + (result.is_passed ? 'Quiz Selesai! 🎉' : 'Belum Lulus') + '</h3>';
            html += !result.is_passed ?
                '<p class="text-muted">Nilai minimal untuk lulus adalah 70%. Silakan coba lagi.</p>' :
                '<p class="text-muted">Selamat! Anda telah menyelesaikan quiz ini.</p>';

            if (result.is_auto_submitted) {
                html += '<div class="alert alert-warning">' + (result.message ||
                    'Kuis dikirim otomatis karena pelanggaran integritas mencapai batas.') + '</div>';
            }

            html += '<div class="row g-3 mt-4 justify-content-center">';
            html +=
                '<div class="col-md-3"><div class="card border"><div class="card-body text-center"><h4 class="mb-0 text-primary">' +
                result.score + '%</h4><small class="text-muted">Nilai</small></div></div></div>';
            html +=
                '<div class="col-md-3"><div class="card border"><div class="card-body text-center"><h4 class="mb-0 text-success">' +
                result.correct_answers + '</h4><small class="text-muted">Benar</small></div></div></div>';
            html +=
                '<div class="col-md-3"><div class="card border"><div class="card-body text-center"><h4 class="mb-0 text-danger">' +
                (result.total_questions - result.correct_answers) +
                '</h4><small class="text-muted">Salah</small></div></div></div>';
            html +=
                '<div class="col-md-3"><div class="card border"><div class="card-body text-center"><h4 class="mb-0 text-info">' +
                result.total_questions + '</h4><small class="text-muted">Total Soal</small></div></div></div>';
            html += '</div>';

            html += '<div class="mt-4">';
            html += result.is_passed ?
                '<button class="btn btn-primary btn-lg" onclick="nextContentAfterQuiz()">Lanjut ke Materi Berikutnya <i class="ti ti-arrow-right ms-1"></i></button>' :
                '<button class="btn btn-warning btn-lg" onclick="loadContent(currentContentId, \'quiz\')"><i class="ti ti-reload me-1"></i> Ulangi Quiz</button>';
            html += '</div></div>';

            $('#content-display').html(html);

            if (result.is_passed) {
                $('[data-content-id="' + currentContentId + '"] i').removeClass().addClass(
                    'ti ti-circle-check text-success fs-5');
                if (result.progress) {
                    $('.progress-bar').css('width', result.progress + '%');
                    $('.progress-bar').parent().siblings('small').text('Progress: ' + result.progress + '%');
                }
            }
        }

        function startFirstContent() {
            const firstContent = $('.content-item').first();
            const contentId = firstContent.data('content-id');
            const contentType = firstContent.data('content-type');
            loadContent(contentId, contentType);
        }

        function getNextContentItem(currentItem) {
            let nextItem = currentItem.nextAll('.content-item').first();
            if (nextItem.length > 0) return nextItem;

            let currentAccordionBody = currentItem.closest('.accordion-body');
            let nextAccordionBody = currentAccordionBody.closest('.accordion-item').next('.accordion-item').find(
                '.accordion-body');

            if (nextAccordionBody.length > 0) {
                nextItem = nextAccordionBody.find('.content-item').first();
                let nextAccordionButton = nextAccordionBody.closest('.accordion-item').find('.accordion-button');
                if (nextAccordionButton.hasClass('collapsed')) {
                    nextAccordionButton.click();
                }
            }
            return nextItem;
        }

        function getPreviousContentItem(currentItem) {
            let prevItem = currentItem.prevAll('.content-item').first();
            if (prevItem.length > 0) return prevItem;

            let currentAccordionBody = currentItem.closest('.accordion-body');
            let prevAccordionBody = currentAccordionBody.closest('.accordion-item').prev('.accordion-item').find(
                '.accordion-body');

            if (prevAccordionBody.length > 0) {
                prevItem = prevAccordionBody.find('.content-item').last();
                let prevAccordionButton = prevAccordionBody.closest('.accordion-item').find('.accordion-button');
                if (prevAccordionButton.hasClass('collapsed')) {
                    prevAccordionButton.click();
                }
            }
            return prevItem;
        }

        function nextContent() {
            markAsComplete(currentContentId).then(function() {
                const currentItem = $('[data-content-id="' + currentContentId + '"]');
                const nextItem = getNextContentItem(currentItem);

                if (nextItem.length) {
                    const contentId = nextItem.data('content-id');
                    const contentType = nextItem.data('content-type');
                    loadContent(contentId, contentType);

                    setTimeout(function() {
                        nextItem[0].scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });
                    }, 300);
                } else {
                    Swal.fire({
                        icon: 'success',
                        title: 'Selamat! 🎉',
                        html: 'Anda telah menyelesaikan <strong>semua materi</strong> dalam kursus ini!',
                        confirmButtonText: 'Kembali ke Detail Kursus',
                        confirmButtonColor: '#0d6efd'
                    }).then(() => {
                        window.location.href = '{{ route('user.kursus.show', $kursus->id) }}';
                    });
                }
            });
        }

        function nextContentAfterQuiz() {
            const currentItem = $('[data-content-id="' + currentContentId + '"]');
            const nextItem = getNextContentItem(currentItem);

            if (nextItem.length) {
                const contentId = nextItem.data('content-id');
                const contentType = nextItem.data('content-type');
                loadContent(contentId, contentType);

                setTimeout(function() {
                    nextItem[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }, 300);
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Selamat! 🎉',
                    html: 'Anda telah menyelesaikan <strong>semua materi</strong> dalam kursus ini!',
                    confirmButtonText: 'Kembali ke Detail Kursus',
                    confirmButtonColor: '#0d6efd'
                }).then(() => {
                    window.location.href = '{{ route('user.kursus.show', $kursus->id) }}';
                });
            }
        }

        function previousContent() {
            const currentItem = $('[data-content-id="' + currentContentId + '"]');
            const prevItem = getPreviousContentItem(currentItem);

            if (prevItem.length) {
                const contentId = prevItem.data('content-id');
                const contentType = prevItem.data('content-type');
                loadContent(contentId, contentType);

                setTimeout(function() {
                    prevItem[0].scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }, 300);
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Informasi',
                    text: 'Ini adalah materi pertama',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }

        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const contentId = urlParams.get('content');

            if (contentId) {
                const contentItem = $('[data-content-id="' + contentId + '"]');
                if (contentItem.length) {
                    const contentType = contentItem.data('content-type');
                    loadContent(contentId, contentType);
                }
            }
        });
    </script>
@endpush
