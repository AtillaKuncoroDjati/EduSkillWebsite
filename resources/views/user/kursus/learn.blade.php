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
                                        $state = $contentAccess[$content->id] ?? ['is_completed' => false, 'is_unlocked' => false];
                                        $isCompleted = $state['is_completed'];
                                        $isUnlocked = $state['is_unlocked'];
                                        $contentTypeLabel = $content->type === 'text' ? 'Teks' : 'Quiz';
                                        $statusLabel = $isCompleted ? 'Selesai' : ($isUnlocked ? 'Terbuka - ' . $contentTypeLabel : 'Terkunci');
                                        $statusClass = $isCompleted ? 'text-success' : ($isUnlocked ? 'text-primary' : 'text-muted');
                                        $iconClass = $isCompleted
                                            ? 'ti ti-circle-check text-success fs-5'
                                            : ($isUnlocked ? 'ti ti-lock-open text-primary fs-5' : 'ti ti-lock text-muted fs-5');
                                    @endphp
                                    <a href="#"
                                        class="d-flex align-items-center text-decoration-none content-item p-3 border-bottom {{ !$isUnlocked ? 'content-locked' : '' }}"
                                        data-content-id="{{ $content->id }}" data-content-type="{{ $content->type }}"
                                        data-content-locked="{{ $isUnlocked ? '0' : '1' }}"
                                        data-content-completed="{{ $isCompleted ? '1' : '0' }}"
                                        aria-disabled="{{ $isUnlocked ? 'false' : 'true' }}">
                                        <div class="flex-shrink-0 me-2">
                                            <i class="content-status-icon {{ $iconClass }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <small class="d-block {{ $isUnlocked ? 'text-dark' : 'text-muted' }}">
                                                {{ $content->title ?? 'Materi ' . $content->order }}
                                            </small>
                                            <small class="content-status-label {{ $statusClass }}">
                                                {{ $statusLabel }}
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
                                            $state = $contentAccess[$content->id] ?? ['is_completed' => false, 'is_unlocked' => false];
                                            $isCompleted = $state['is_completed'];
                                            $isUnlocked = $state['is_unlocked'];
                                            $contentTypeLabel = $content->type === 'text' ? 'Teks' : 'Quiz';
                                            $statusLabel = $isCompleted ? 'Selesai' : ($isUnlocked ? 'Terbuka - ' . $contentTypeLabel : 'Terkunci');
                                            $statusClass = $isCompleted ? 'text-success' : ($isUnlocked ? 'text-primary' : 'text-muted');
                                            $iconClass = $isCompleted
                                                ? 'ti ti-circle-check text-success fs-5'
                                                : ($isUnlocked ? 'ti ti-lock-open text-primary fs-5' : 'ti ti-lock text-muted fs-5');
                                        @endphp
                                        <a href="#"
                                            class="d-flex align-items-center text-decoration-none content-item p-3 border-bottom {{ !$isUnlocked ? 'content-locked' : '' }}"
                                            data-content-id="{{ $content->id }}" data-content-type="{{ $content->type }}"
                                            data-content-locked="{{ $isUnlocked ? '0' : '1' }}"
                                            data-content-completed="{{ $isCompleted ? '1' : '0' }}"
                                            aria-disabled="{{ $isUnlocked ? 'false' : 'true' }}">
                                            <div class="flex-shrink-0 me-2">
                                                <i class="content-status-icon {{ $iconClass }}"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <small class="d-block {{ $isUnlocked ? 'text-dark' : 'text-muted' }}">
                                                    {{ $content->title ?? 'Materi ' . $content->order }}
                                                </small>
                                                <small class="content-status-label {{ $statusClass }}">
                                                    {{ $statusLabel }}
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
        /* Cegah seleksi/copy soal kuis & essay (jawaban tetap bisa diketik) */
        .quiz-wrapper,
        .quiz-wrapper h3,
        .quiz-wrapper h6,
        .quiz-wrapper .card-body,
        .quiz-wrapper .form-check-label,
        .quiz-wrapper .alert {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            -webkit-touch-callout: none;
        }
        /* Tetap izinkan input pengguna pada textarea esai */
        .quiz-wrapper textarea,
        .quiz-wrapper input[type="text"] {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }

        .essay-review-answer {
            min-height: 110px;
            line-height: 1.6;
            resize: vertical;
            overflow-y: auto;
        }

        .content-item {
            transition: all 0.2s;
        }

        .content-item:hover {
            background-color: #f8f9fa;
        }

        .content-item.content-locked {
            cursor: not-allowed;
            background-color: #f8f9fa;
        }

        .content-item.content-locked:hover {
            background-color: #f1f3f5;
        }

        .bg-soft-primary {
            background-color: rgba(13, 110, 253, 0.1);
        }

        .content-text {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .content-text img,
        .content-text .ql-editor img {
            max-width: 100% !important;
            height: auto !important;
            display: block;
        }

        .content-text .ql-align-center img {
            margin-left: auto;
            margin-right: auto;
        }

        .content-text .ql-align-right img {
            margin-left: auto;
            margin-right: 0;
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
        let currentContentType = null;
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

        function getContentTitleForMessage(target) {
            const item = target && target.nodeType ? $(target) : getPrimaryContentItem(target);
            return item.find('.flex-grow-1 small:first').text().trim() || 'Konten ini';
        }

        function showLockedContentMessage(target) {
            const contentTitle = getContentTitleForMessage(target);

            Swal.fire({
                icon: 'info',
                title: contentTitle + ' terkunci',
                text: 'Selesaikan konten sebelumnya agar bisa membuka konten berikutnya.',
                confirmButtonColor: '#0d6efd'
            });
        }

        function getPrimaryContentItem(contentId) {
            const items = $('.content-item[data-content-id="' + contentId + '"]');
            const visible = items.filter(':visible').first();
            return visible.length ? visible : items.first();
        }

        function isContentLocked(contentId) {
            const item = getPrimaryContentItem(contentId);
            return item.length && item.attr('data-content-locked') === '1';
        }

        function updateProgressBar(progress) {
            if (progress === undefined || progress === null) return;
            $('.progress-bar').css('width', progress + '%');
            $('.progress-bar').parent().siblings('small').text('Progress: ' + progress + '%');
        }

        function setContentItemUnlocked(contentId) {
            $('.content-item[data-content-id="' + contentId + '"]').each(function() {
                const item = $(this);
                const typeLabel = item.attr('data-content-type') === 'text' ? 'Teks' : 'Quiz';

                item.attr('data-content-locked', '0')
                    .attr('aria-disabled', 'false')
                    .removeClass('content-locked');

                item.find('.content-status-icon')
                    .removeClass()
                    .addClass('content-status-icon ti ti-lock-open text-primary fs-5');

                item.find('.content-status-label')
                    .removeClass('text-muted text-success text-primary')
                    .addClass('text-primary')
                    .text('Terbuka - ' + typeLabel);

                item.find('.flex-grow-1 small:first')
                    .removeClass('text-muted')
                    .addClass('text-dark');
            });
        }

        function unlockNextContentItem(contentId) {
            const currentItem = getPrimaryContentItem(contentId);
            const nextItem = getNextContentItem(currentItem);
            if (!nextItem.length) return;

            setContentItemUnlocked(nextItem.attr('data-content-id'));
        }

        function setContentItemCompleted(contentId) {
            $('.content-item[data-content-id="' + contentId + '"]').each(function() {
                const item = $(this);

                item.attr('data-content-completed', '1')
                    .attr('data-content-locked', '0')
                    .attr('aria-disabled', 'false')
                    .removeClass('content-locked');

                item.find('.content-status-icon')
                    .removeClass()
                    .addClass('content-status-icon ti ti-circle-check text-success fs-5');

                item.find('.content-status-label')
                    .removeClass('text-muted text-primary text-success')
                    .addClass('text-success')
                    .text('Selesai');

                item.find('.flex-grow-1 small:first')
                    .removeClass('text-muted')
                    .addClass('text-dark');
            });

            unlockNextContentItem(contentId);
        }

        function closeMobileSidebarFromItem(item) {
            const offcanvasEl = item.closest('.offcanvas');
            if (!offcanvasEl || !window.bootstrap) return;

            const instance = bootstrap.Offcanvas.getInstance(offcanvasEl) || new bootstrap.Offcanvas(offcanvasEl);
            instance.hide();
        }

        function loadContent(contentId, contentType, options = {}) {
            if (!options.ignoreLock && isContentLocked(contentId)) {
                showLockedContentMessage(contentId);
                return;
            }

            currentContentId = contentId;
            currentContentType = contentType;
            resetIntegrityState();

            $('.content-item').removeClass('bg-soft-primary');
            $('[data-content-id="' + contentId + '"]').addClass('bg-soft-primary');

            $('#welcome-screen').hide();
            $('#content-display').show();

            $('#content-display').html(
                '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-3 text-muted">Memuat konten...</p></div>'
            );

            const url = '/user/daftar-kursus/{{ $kursus->id }}/content/' + contentId
                + (options.retry ? '?retry=1' : '');

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    if (contentType === 'text') {
                        renderTextContent(response);
                    } else if (contentType === 'quiz') {
                        renderQuizContent(response);
                    }
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.error)
                        ? xhr.responseJSON.error
                        : 'Gagal memuat konten (HTTP ' + xhr.status + ')';
                    const isLocked = xhr.status === 423 || (xhr.responseJSON && xhr.responseJSON.locked);
                    const alertClass = isLocked ? 'warning' : 'danger';
                    const icon = isLocked ? 'ti-lock' : 'ti-alert-circle';
                    $('#content-display').html(
                        '<div class="alert alert-' + alertClass + '"><i class="ti ' + icon + '"></i> ' + msg + '</div>'
                    );
                }
            });
        }

        function renderTextContent(data) {
            let html = '<div class="content-wrapper">';
            html += '<h3 class="mb-4">' + escapeHtml(data.title || 'Materi') + '</h3>';

            if (data.pdf_url) {
                // PDF toolbar: download button
                html += '<div class="d-flex align-items-center justify-content-between mb-2">';
                html += '<span class="text-muted small"><i class="ti ti-file-type-pdf text-danger me-1"></i>Dokumen PDF</span>';
                html += '<a href="' + data.pdf_url + '" download class="btn btn-sm btn-outline-danger">';
                html += '<i class="ti ti-download me-1"></i>Download PDF</a>';
                html += '</div>';

                // Embedded PDF viewer
                html += '<div class="border rounded overflow-hidden mb-3" style="height:75vh;">';
                html += '<embed src="' + data.pdf_url + '#toolbar=1&navpanes=0" type="application/pdf" width="100%" height="100%">';
                html += '</div>';

                // Deskripsi/catatan (opsional, di bawah PDF)
                const desc = (data.content || '').replace(/<p><br><\/p>/g, '').trim();
                if (desc && desc !== '<p></p>') {
                    html += '<div class="card mt-3"><div class="card-body"><div class="content-text">';
                    html += desc;
                    html += '</div></div></div>';
                }
            } else {
                html += '<div class="card"><div class="card-body"><div class="content-text">';
                html += data.content || '<p class="text-muted">Tidak ada konten</p>';
                html += '</div></div></div>';
            }

            html += '<div class="d-flex justify-content-between mt-4">';
            html += '<button class="btn btn-outline-secondary" onclick="previousContent()"><i class="ti ti-arrow-left"></i> Sebelumnya</button>';
            html += '<button class="btn btn-primary" onclick="nextContent()">Selanjutnya <i class="ti ti-arrow-right"></i></button>';
            html += '</div></div>';

            $('#content-display').html(html);
        }

        function renderQuizContent(data) {
            if (data.pending_review) {
                renderEssayPendingReview(data);
                return;
            }

            if (data.already_passed || (data.quiz_type === 'essay' && data.grading_status === 'graded' && !data.already_passed)) {
                renderQuizReview(data);
                return;
            }

            if (data.quiz_type === 'essay') {
                renderEssayForm(data);
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
                    '<button class="btn btn-warning" type="button" onclick="startQuizWithIntegrity(\'' + data.id + '\', ' +
                    integrity.require_fullscreen + ', ' + integrity.max_violations + ')">Saya Mengerti & Mulai Kuis</button>';
                html += '</div></div>';
                html += '<div id="quiz-form-wrapper" style="display:none;">';
            }

            html += '<form id="quiz-form" onsubmit="submitQuiz(event, \'' + data.id + '\')">';

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
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.error)
                        ? xhr.responseJSON.error
                        : 'Gagal memulai attempt kuis.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: msg
                    });
                }
            });
        }

        function renderEssayForm(data) {
            const integrity = data.integrity_settings || { enabled: false, require_fullscreen: false, max_violations: 0 };

            let html = '<div class="quiz-wrapper">';
            html += '<h3 class="mb-4">' + escapeHtml(data.title || 'Esai') + '</h3>';
            const isManualGrading = data.grading_type === 'manual';
            const gradingNote = isManualGrading
                ? 'Jawab pertanyaan esai berikut. Jawaban Anda akan dinilai oleh admin dalam 24 jam.'
                : 'Jawab ' + data.questions.length + ' pertanyaan esai berikut. Jawaban Anda akan dinilai oleh AI. Nilai minimal 70% untuk lulus.';
            html += '<div class="alert alert-info"><i class="ti ti-writing me-2"></i>' + gradingNote + '</div>';

            if (integrity.enabled) {
                html += '<div class="card border-warning mb-3" id="integrity-rules-card"><div class="card-body">';
                html += '<h5 class="mb-3"><i class="ti ti-shield-lock me-2"></i>Quiz Integrity Mode</h5>';
                html += '<ul class="mb-3"><li>Perpindahan tab akan dipantau.</li><li>Kehilangan fokus browser akan dipantau.</li>';
                if (integrity.require_fullscreen) html += '<li>Fullscreen wajib selama kuis berlangsung.</li>';
                html += '<li>Kuis dapat otomatis dikirim jika pelanggaran mencapai batas.</li></ul>';
                html += '<p class="mb-3" id="integrity-counter">Pelanggaran: 0/' + integrity.max_violations + '</p>';
                html += '<button class="btn btn-warning" type="button" onclick="startQuizWithIntegrity(\'' + data.id + '\', ' + integrity.require_fullscreen + ', ' + integrity.max_violations + ')">Saya Mengerti & Mulai Kuis</button>';
                html += '</div></div>';
                html += '<div id="quiz-form-wrapper" style="display:none;">';
            }

            html += '<form id="quiz-form" onsubmit="submitEssay(event, \'' + data.id + '\')">';
            data.questions.forEach(function(q, i) {
                html += '<div class="card mb-3"><div class="card-body">';
                html += '<h6 class="mb-3"><span class="badge bg-primary me-2">' + (i + 1) + '</span>' + escapeHtml(q.question) + '</h6>';
                html += '<textarea class="form-control" name="essay_' + i + '" rows="4" placeholder="Tulis jawaban Anda di sini..." required></textarea>';
                html += '</div></div>';
            });
            html += '<div class="d-flex justify-content-between mt-4">';
            html += '<button type="button" class="btn btn-outline-secondary" onclick="previousContent()"><i class="ti ti-arrow-left"></i> Sebelumnya</button>';
            const submitLabel = isManualGrading ? 'Submit' : 'Submit & Nilai dengan AI';
            html += '<button type="submit" class="btn btn-success" id="essay-submit-btn"><i class="ti ti-send"></i> ' + submitLabel + '</button>';
            html += '</div></form>';

            if (integrity.enabled) html += '</div>';
            html += '</div>';
            $('#content-display').html(html);

            // Reset capture and attach to every essay textarea for this attempt
            keystrokeCapture.reset();
            document.querySelectorAll('#quiz-form textarea').forEach(function(ta) {
                keystrokeCapture.attach(ta);
            });

            integrityState.enabled = !!integrity.enabled;
            integrityState.requireFullscreen = !!integrity.require_fullscreen;
            integrityState.maxViolations = integrity.max_violations || 0;

            if (!integrity.enabled) {
                startQuizAttempt(data.id, false);
            }
        }

        function submitEssay(event, contentId) {
            event.preventDefault();
            const form = document.getElementById('quiz-form');
            const formData = new FormData(form);
            const answers = {};
            for (const [key, val] of formData.entries()) {
                answers[key] = val;
            }

            $('#essay-submit-btn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Mengirim jawaban...');

            // Collect keystroke stats before detaching listeners
            const keystrokeStats = keystrokeCapture.getStats();
            keystrokeCapture.detach();

            $.ajax({
                url: '/user/daftar-kursus/{{ $kursus->id }}/quiz/' + contentId + '/submit',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    attempt_id: currentQuizAttemptId,
                    answers: answers,
                    keystroke_data: keystrokeStats,
                },
                success: function(res) {
                    stopIntegrityMonitoring();

                    // Manual grading: show pending state
                    if (res.pending_review) {
                        renderEssayPendingReview({ title: '' });
                        return;
                    }

                    const passed = res.is_passed;
                    const color  = passed ? 'success' : 'warning';
                    const icon   = passed ? 'ti-circle-check' : 'ti-circle-x';

                    let html = '<div class="quiz-review-wrapper">';
                    html += '<h3 class="mb-4">Hasil Esai</h3>';
                    html += '<div class="alert alert-' + color + ' mb-4"><i class="ti ' + icon + ' me-2"></i>';
                    html += passed ? 'Selamat! Anda lulus esai ini.' : 'Nilai Anda belum mencapai 70. Silakan coba lagi.';
                    html += '</div>';

                    html += '<div class="row g-3 mb-4">';
                    html += '<div class="col-md-4"><div class="card border-' + color + '"><div class="card-body text-center">';
                    html += '<i class="ti ti-star fs-2 text-' + color + '"></i>';
                    html += '<h4 class="mt-2 mb-0 text-' + color + '">' + res.score + '%</h4>';
                    html += '<small class="text-muted">Nilai Rata-rata</small></div></div></div>';
                    html += '<div class="col-md-4"><div class="card"><div class="card-body text-center">';
                    html += '<i class="ti ti-check fs-2 text-success"></i>';
                    html += '<h4 class="mt-2 mb-0">' + res.correct_answers + '/' + res.total_questions + '</h4>';
                    html += '<small class="text-muted">Soal >=70</small></div></div></div>';
                    html += '</div>';

                    html += '<h5 class="mb-3">Detail Penilaian AI</h5>';
                    (res.essay_answers || []).forEach(function(ea, i) {
                        const sc = ea.score >= 70 ? 'success' : 'danger';
                        html += '<div class="card mb-3 border-' + sc + '">';
                        html += '<div class="card-header bg-light d-flex justify-content-between"><strong>Soal ' + (i+1) + '</strong>';
                        html += '<span class="badge bg-' + sc + '">' + ea.score + '/100</span></div>';
                        html += '<div class="card-body">';
                        html += '<p class="fw-bold mb-1">' + escapeHtml(ea.question) + '</p>';
                        html += renderEssayAnswerBox(ea.answer);
                        html += '<div class="alert alert-' + sc + ' mb-0 py-2"><i class="ti ti-robot me-1"></i><strong>Feedback AI:</strong> ' + escapeHtml(ea.feedback) + '</div>';
                        html += '</div></div>';
                    });

                    html += '<div class="d-flex justify-content-between mt-4">';
                    html += '<button type="button" class="btn btn-outline-secondary" onclick="previousContent()"><i class="ti ti-arrow-left"></i> Sebelumnya</button>';
                    html += '<button type="button" class="btn btn-primary" onclick="nextContent()">Selanjutnya <i class="ti ti-arrow-right"></i></button>';
                    html += '</div></div>';

                    $('#content-display').html(html);
                    resizeEssayReviewAnswers();
                    if (res.is_passed) {
                        setContentItemCompleted(contentId);
                    }
                    updateProgressBar(res.progress);
                },
                error: function(xhr) {
                    $('#essay-submit-btn').prop('disabled', false).html('<i class="ti ti-send"></i> Submit & Nilai dengan AI');
                    const msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Gagal menilai esai.';
                    Swal.fire('Error', msg, 'error');
                }
            });
        }

        function renderQuizReview(data) {
            if (data.quiz_type === 'essay') {
                renderEssayReview(data);
                return;
            }
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
                        setContentItemCompleted(contentId);
                        updateProgressBar(response.progress);
                    }
                },
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.error)
                        ? xhr.responseJSON.error
                        : 'Gagal menyimpan progress materi.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: msg
                    });
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
                error: function(xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.error)
                        ? xhr.responseJSON.error
                        : 'Terjadi kesalahan saat submit quiz';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: msg
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
            html += '<h3 class="mt-4 mb-3">' + (result.is_passed ? 'Quiz Selesai!' : 'Belum Lulus') + '</h3>';
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
                setContentItemCompleted(currentContentId);
                updateProgressBar(result.progress);
            }
        }

        function startFirstContent() {
            let firstContent = $('.content-item[data-content-locked="0"][data-content-completed="0"]').first();
            if (!firstContent.length) {
                firstContent = $('.content-item[data-content-locked="0"]').first();
            }
            if (!firstContent.length) return;

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

        function goToNextContent() {
            const currentItem = getPrimaryContentItem(currentContentId);
            const nextItem = getNextContentItem(currentItem);

            if (nextItem.length) {
                const contentId = nextItem.data('content-id');
                const contentType = nextItem.data('content-type');

                if (nextItem.attr('data-content-locked') === '1') {
                    showLockedContentMessage(nextItem[0]);
                    return;
                }

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
                    html: 'Anda telah menyelesaikan <strong>semua materi</strong> dalam kursus ini!',
                    title: 'Selamat!',
                    confirmButtonText: 'Kembali ke Detail Kursus',
                    confirmButtonColor: '#0d6efd'
                }).then(() => {
                    window.location.href = '{{ route('user.kursus.show', $kursus->id) }}';
                });
            }
        }

        function nextContent() {
            if (currentContentType !== 'text') {
                goToNextContent();
                return;
            }

            markAsComplete(currentContentId).then(function() {
                goToNextContent();
            });
        }

        function nextContentAfterQuiz() {
            goToNextContent();
        }

        function renderEssayPendingReview(data) {
            let html = '<div class="quiz-review-wrapper">';
            html += '<h3 class="mb-4">' + escapeHtml(data.title || 'Esai') + '</h3>';
            html += '<div class="alert alert-warning mb-4">';
            html += '<i class="ti ti-clock me-2"></i><strong>Jawaban kamu telah dikirim.</strong><br>';
            html += 'Tunggu penilaian admin dalam 24 jam. Kamu akan mendapat notifikasi saat sudah dinilai.';
            html += '</div>';
            html += '<div class="text-center py-4">';
            html += '<i class="ti ti-hourglass" style="font-size:64px;color:#f0ad4e;"></i>';
            html += '<h5 class="mt-3 text-muted">Sedang Menunggu Penilaian Admin</h5>';
            html += '</div>';
            html += '<div class="d-flex justify-content-between mt-4">';
            html += '<button type="button" class="btn btn-outline-secondary" onclick="previousContent()"><i class="ti ti-arrow-left"></i> Sebelumnya</button>';
            html += '<span class="text-muted small align-self-center"><i class="ti ti-lock me-1"></i>Materi berikutnya terbuka setelah esai dinilai lulus.</span>';
            html += '</div></div>';
            $('#content-display').html(html);
        }

        function renderEssayReview(data) {
            const attempt = data.attempt;
            const isManual = data.grading_type === 'manual';
            const canRetry = isManual && !data.already_passed;
            const scoreClass = attempt.score >= 70 ? 'success' : 'danger';
            const essayAnswers = data.quiz_details || [];

            let html = '<div class="quiz-review-wrapper">';
            html += '<h3 class="mb-4">' + escapeHtml(data.title || 'Esai') + '</h3>';
            html += '<div class="alert alert-' + (data.already_passed ? 'success' : 'warning') + ' mb-4">';
            html += '<i class="ti ti-circle-check me-2"></i>Esai dinilai pada ' + (attempt.completed_at || '-');
            html += data.already_passed ? ' - <strong>Lulus</strong>' : ' - <strong>Belum Lulus (nilai minimum 70)</strong>';
            if (canRetry) {
                html += '<div class="mt-2 mb-0"><i class="ti ti-refresh me-1"></i>Anda dapat mengulang kuis ini untuk memperbaiki nilai. Klik tombol <strong>Coba Lagi</strong> di bawah.</div>';
            }
            html += '</div>';

            html += '<div class="row g-3 mb-4">';
            html += '<div class="col-md-4"><div class="card border-' + scoreClass + '"><div class="card-body text-center">';
            html += '<i class="ti ti-star fs-2 text-' + scoreClass + '"></i>';
            html += '<h4 class="mt-2 mb-0 text-' + scoreClass + '">' + attempt.score + '%</h4>';
            html += '<small class="text-muted">Nilai Rata-rata</small></div></div></div>';
            html += '<div class="col-md-4"><div class="card"><div class="card-body text-center">';
            html += '<h4 class="mt-2 mb-0">' + attempt.correct_answers + '/' + attempt.total_questions + '</h4>';
            html += '<small class="text-muted">Soal >=70</small></div></div></div>';
            html += '</div>';

            if (isManual && attempt.admin_notes) {
                html += '<div class="alert alert-info mb-3"><i class="ti ti-note me-2"></i><strong>Catatan Admin:</strong> ' + escapeHtml(attempt.admin_notes) + '</div>';
            }

            const feedbackLabel = isManual ? 'Feedback Admin' : 'Feedback AI';
            const feedbackIcon  = isManual ? 'ti-user-check' : 'ti-robot';

            html += '<h5 class="mb-3">Detail Penilaian ' + (isManual ? 'Admin' : 'AI') + '</h5>';
            const pairs = Object.values(essayAnswers);
            pairs.forEach(function(ea, i) {
                const sc = (ea.score >= 70) ? 'success' : 'danger';
                html += '<div class="card mb-3 border-' + sc + '">';
                html += '<div class="card-header bg-light d-flex justify-content-between"><strong>Soal ' + (i+1) + '</strong>';
                html += '<span class="badge bg-' + sc + '">' + (ea.score !== undefined ? ea.score + '/100' : '-') + '</span></div>';
                html += '<div class="card-body">';
                html += '<p class="fw-bold mb-1">' + escapeHtml(ea.question || '') + '</p>';
                html += renderEssayAnswerBox(ea.answer);
                if (ea.feedback) {
                    html += '<div class="alert alert-' + sc + ' mb-0 py-2"><i class="ti ' + feedbackIcon + ' me-1"></i><strong>' + feedbackLabel + ':</strong> ' + escapeHtml(ea.feedback) + '</div>';
                }
                html += '</div></div>';
            });

            html += '<div class="d-flex justify-content-between mt-4">';
            html += '<button type="button" class="btn btn-outline-secondary" onclick="previousContent()"><i class="ti ti-arrow-left"></i> Sebelumnya</button>';
            html += '<div class="d-flex gap-2">';
            if (canRetry) {
                html += '<button type="button" class="btn btn-warning" onclick="retryEssay(\'' + data.id + '\')"><i class="ti ti-refresh"></i> Coba Lagi</button>';
            }
            if (data.already_passed) {
                html += '<button type="button" class="btn btn-primary" onclick="nextContent()">Selanjutnya <i class="ti ti-arrow-right"></i></button>';
            }
            html += '</div>';
            html += '</div></div>';
            $('#content-display').html(html);
            resizeEssayReviewAnswers();
        }

        function renderEssayAnswerBox(answer) {
            return '<div class="essay-review-answer-wrap mb-2">'
                + '<small class="text-muted">Jawaban Anda:</small>'
                + '<textarea class="form-control bg-light essay-review-answer mt-1" rows="4" readonly aria-label="Jawaban Anda">'
                + escapeHtml(answer || '-')
                + '</textarea>'
                + '</div>';
        }

        function resizeEssayReviewAnswers() {
            document.querySelectorAll('.essay-review-answer').forEach(function(textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = (textarea.scrollHeight + 4) + 'px';
            });
        }

        function retryEssay(contentId) {
            Swal.fire({
                icon: 'question',
                title: 'Coba Lagi Kuis Esai?',
                text: 'Anda akan mengerjakan ulang kuis ini. Jawaban sebelumnya tetap tersimpan sebagai riwayat.',
                showCancelButton: true,
                confirmButtonText: 'Ya, Coba Lagi',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#f59f00',
            }).then(function(result) {
                if (result.isConfirmed) {
                    loadContent(contentId, 'quiz', { retry: true });
                }
            });
        }

        function previousContent() {
            const currentItem = getPrimaryContentItem(currentContentId);
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
            $(document).on('click', '.content-item', function(e) {
                e.preventDefault();

                if ($(this).attr('data-content-locked') === '1') {
                    showLockedContentMessage(this);
                    return false;
                }

                const contentId = $(this).attr('data-content-id');
                const contentType = $(this).attr('data-content-type');
                loadContent(contentId, contentType);
                closeMobileSidebarFromItem(this);
                return false;
            });

            const urlParams = new URLSearchParams(window.location.search);
            const contentId = urlParams.get('content');

            if (contentId) {
                const contentItem = getPrimaryContentItem(contentId);
                if (contentItem.length) {
                    const contentType = contentItem.data('content-type');
                    loadContent(contentId, contentType);
                }
            }

            // Cegah copy/cut/right-click pada area soal kuis & essay
            const isInsideQuizQuestion = (el) => {
                const wrap = el && el.closest && el.closest('.quiz-wrapper');
                if (!wrap) return false;
                const tag = (el.tagName || '').toLowerCase();
                // Izinkan textarea jawaban dan input teks
                if (tag === 'textarea') return false;
                if (tag === 'input' && (el.type === 'text' || el.type === 'number')) return false;
                return true;
            };

            $(document).on('contextmenu copy cut dragstart', function(e) {
                if (isInsideQuizQuestion(e.target)) {
                    e.preventDefault();
                    return false;
                }
            });

            $(document).on('keydown', function(e) {
                if (!isInsideQuizQuestion(e.target)) return;
                const k = (e.key || '').toLowerCase();
                if ((e.ctrlKey || e.metaKey) && (k === 'c' || k === 'x' || k === 'a' || k === 's' || k === 'p')) {
                    e.preventDefault();
                    return false;
                }
            });
        });

        // ── Keystroke Dynamics Capture ──────────────────────────────────────────
        // Records dwell time (key-hold), flight time (inter-key gap), typing speed,
        // and error rate silently while the student types essay answers.
        // Stats are sent with the submission for server-side anomaly analysis.

        class KeystrokeCapture {
            constructor() {
                this._events      = [];
                this._downTimes   = {};
                this._lastUpTime  = null;
                this._attached    = [];
                this._pasteCount  = 0;
                this._pastedChars = 0;
                this._textareas   = [];
            }

            attach(el) {
                const onDown  = (e) => this._onDown(e);
                const onUp    = (e) => this._onUp(e);
                const onPaste = (e) => this._onPaste(e);
                el.addEventListener('keydown', onDown);
                el.addEventListener('keyup',   onUp);
                el.addEventListener('paste',   onPaste);
                this._attached.push({ el, onDown, onUp, onPaste });
                if (el.tagName && el.tagName.toLowerCase() === 'textarea') {
                    this._textareas.push(el);
                }
            }

            detach() {
                for (const { el, onDown, onUp, onPaste } of this._attached) {
                    el.removeEventListener('keydown', onDown);
                    el.removeEventListener('keyup',   onUp);
                    el.removeEventListener('paste',   onPaste);
                }
                this._attached = [];
            }

            reset() {
                this.detach();
                this._events      = [];
                this._downTimes   = {};
                this._lastUpTime  = null;
                this._pasteCount  = 0;
                this._pastedChars = 0;
                this._textareas   = [];
            }

            _onPaste(e) {
                this._pasteCount++;
                const txt = (e.clipboardData || window.clipboardData)?.getData('text') || '';
                this._pastedChars += txt.length;
            }

            _onDown(e) {
                if (!this._downTimes[e.code]) {
                    this._downTimes[e.code] = performance.now();
                }
            }

            _onUp(e) {
                const t0 = this._downTimes[e.code];
                if (t0 === undefined) return;
                const t1    = performance.now();
                const dwell = t1 - t0;
                delete this._downTimes[e.code];

                const flight = this._lastUpTime !== null ? t1 - this._lastUpTime : null;
                this._lastUpTime = t1;

                this._events.push({
                    code:    e.code,
                    dwell:   dwell,
                    flight:  flight,
                    isError: e.code === 'Backspace' || e.code === 'Delete',
                });
            }

            getStats() {
                const events = this._events;
                const totalAnswerChars = this._textareas
                    .reduce((sum, ta) => sum + (ta.value || '').length, 0);

                // Hanya skip jika benar-benar tidak ada aktivitas: tidak ketik & tidak paste & jawaban kosong
                if (events.length === 0 && this._pasteCount === 0 && totalAnswerChars === 0) {
                    return null;
                }

                const valid = (arr, lo, hi) => arr.filter(v => v !== null && v >= lo && v < hi);
                const mean  = (arr) => arr.length ? arr.reduce((a, b) => a + b, 0) / arr.length : 0;
                const std   = (arr, m) => arr.length > 1
                    ? Math.sqrt(arr.reduce((a, b) => a + (b - m) ** 2, 0) / arr.length)
                    : 0;

                const dwells  = valid(events.map(e => e.dwell),  10, 1000);
                const flights = valid(events.map(e => e.flight), 10, 3000);
                const errors  = events.filter(e => e.isError).length;
                const chars   = events.filter(e => !e.isError).length;

                // Metrik biometrik hanya valid jika ada cukup data ketukan
                const hasEnoughBiometric = dwells.length >= 15;

                let mDwell = null, mFlight = null, sDwell = null, sFlight = null, speedCps = null;
                if (hasEnoughBiometric) {
                    mDwell  = mean(dwells);
                    mFlight = mean(flights);
                    sDwell  = std(dwells, mDwell);
                    sFlight = std(flights, mFlight);
                    const totalTime = (dwells.reduce((a, b) => a + b, 0) + flights.reduce((a, b) => a + b, 0)) / 1000;
                    speedCps = totalTime > 0.5 ? chars / totalTime : 0;
                }

                return {
                    mean_dwell:        mDwell  !== null ? +mDwell.toFixed(1)  : null,
                    std_dwell:         sDwell  !== null ? +sDwell.toFixed(1)  : null,
                    mean_flight:       mFlight !== null ? +mFlight.toFixed(1) : null,
                    std_flight:        sFlight !== null ? +sFlight.toFixed(1) : null,
                    mean_speed_cps:    speedCps !== null ? +speedCps.toFixed(2) : null,
                    std_speed_cps:     0,
                    error_rate:        chars > 0 ? +(errors / (chars + errors)).toFixed(3) : 0,
                    total_keystrokes:  events.length,
                    paste_count:       this._pasteCount,
                    pasted_chars:      this._pastedChars,
                    total_answer_chars: totalAnswerChars,
                    device_type:       /Mobi|Android/i.test(navigator.userAgent) ? 'mobile' : 'desktop',
                };
            }
        }

        // Single instance reused across the quiz session
        let keystrokeCapture = new KeystrokeCapture();
    </script>
@endpush
