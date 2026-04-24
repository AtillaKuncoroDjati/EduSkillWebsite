@extends('template', ['title' => 'Detail Modul'])

@section('content')
    <div class="page-title-head d-flex align-items-center justify-content-between mb-3">
        <h4 class="fs-18 text-uppercase fw-bold mb-0">{{ $module->title }}</h4>

        <a href="{{ route('admin.kursus.edit', $module->kursus_id) }}#pane-modul" class="btn btn-soft-danger">
            <i class="ti ti-arrow-left me-1"></i> Kembali ke Kursus
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-list me-2"></i>Daftar Materi</h5>

            <button class="btn btn-soft-primary" data-bs-toggle="modal" data-bs-target="#create-material-modal">
                <i class="ti ti-plus me-1"></i> Tambah Materi
            </button>
        </div>

        <div class="card-body">
            <div id="materi-list" data-plugin="dragula">
                @forelse ($module->contents as $index => $materi)
                    <div class="card mb-3 shadow-sm" data-id="{{ $materi->id }}" style="cursor: move;">
                        <div class="card-body">
                            <div class="row align-items-start">
                                {{-- Drag Handle & Number --}}
                                <div class="col-auto d-flex align-items-center">
                                    <div class="drag-handle me-2">
                                        <i class="ti ti-grip-vertical fs-4 text-muted"></i>
                                    </div>
                                    <div class="badge bg-primary rounded-circle"
                                        style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                        {{ $index + 1 }}
                                    </div>
                                </div>

                                {{-- Content Info --}}
                                <div class="col">
                                    <div class="d-flex align-items-center mb-2">
                                        @if ($materi->type === 'text')
                                            <span class="badge bg-info me-2">
                                                <i class="ti ti-file-text"></i> Teks
                                            </span>
                                        @else
                                            <span class="badge bg-warning me-2">
                                                <i class="ti ti-clipboard-list"></i> Kuis
                                            </span>
                                        @endif
                                        <h6 class="mb-0 fw-bold">{{ $materi->title ?: 'Tanpa Judul' }}</h6>
                                    </div>

                                    {{-- Content Preview --}}
                                    <div class="content-preview">
                                        <div class="ql-editor p-0"
                                            style="max-height: 150px; overflow: hidden; position: relative;">
                                            {!! $materi->content !!}
                                            @if (strlen(strip_tags($materi->content)) > 200)
                                                <div
                                                    style="position: absolute; bottom: 0; left: 0; right: 0; height: 50px; background: linear-gradient(to bottom, transparent, white);">
                                                </div>
                                            @endif
                                        </div>
                                        @if (strlen(strip_tags($materi->content)) > 200)
                                            <button class="btn btn-sm btn-link p-0 mt-1 text-decoration-none" type="button"
                                                onclick="togglePreview(this)">
                                                <small>Lihat selengkapnya...</small>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Quiz Info --}}
                                    @if ($materi->type === 'quiz')
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="ti ti-help-circle"></i> {{ $materi->questions->count() }}
                                                Pertanyaan
                                            </small>
                                        </div>
                                    @endif
                                </div>

                                {{-- Action Buttons --}}
                                <div class="col-12 col-md-auto mt-3 mt-md-0">
                                    <div class="d-flex flex-column gap-2">
                                        <button class="btn btn-sm btn-soft-primary btn-edit-material w-100"
                                            data-id="{{ $materi->id }}" data-title="{{ $materi->title }}"
                                            data-type="{{ $materi->type }}">
                                            <i class="ti ti-pencil me-1"></i> Edit
                                        </button>

                                        <form method="POST" action="{{ route('admin.content.delete') }}" class="m-0">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $materi->id }}">
                                            <button type="submit" class="btn btn-sm btn-soft-danger w-100"
                                                onclick="return confirm('Hapus materi ini? Data yang dihapus tidak dapat dikembalikan.')">
                                                <i class="ti ti-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="ti ti-file-off text-muted" style="font-size: 64px;"></i>
                        <h5 class="mt-3 text-muted">Belum Ada Materi</h5>
                        <p class="text-muted mb-3">Silakan tambahkan materi pertama untuk modul ini</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create-material-modal">
                            <i class="ti ti-plus me-1"></i> Tambah Materi Pertama
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ================= MODAL TAMBAH MATERI ================= --}}
    <div id="create-material-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.content.store', $module->id) }}" id="form-create-material">
                    @csrf
                    <input type="hidden" name="content" id="create-content-hidden">

                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Materi Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul Materi</label>
                            <input type="text" name="title" class="form-control" placeholder="Masukkan judul materi">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe Konten</label>
                            <select name="type" id="create-type" class="form-control">
                                <option value="text">Teks</option>
                                <option value="quiz">Kuis</option>
                            </select>
                        </div>

                        <div class="mb-3" id="create-content-label">
                            <label class="form-label" id="create-content-label-text">Konten</label>
                            <div id="create-content-editor" style="height: 150px;"></div>
                        </div>

                        <div id="create-quiz-builder" class="d-none">
                            <h6>Pertanyaan Kuis</h6>
                            <div class="card border mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Pengaturan Integritas Kuis</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="create-integrity-enabled"
                                            name="integrity_mode_enabled" value="1">
                                        <label class="form-check-label" for="create-integrity-enabled">Aktifkan Integrity Mode</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="create-require-fullscreen"
                                            name="require_fullscreen" value="1">
                                        <label class="form-check-label" for="create-require-fullscreen">Wajib Fullscreen saat mengerjakan</label>
                                    </div>
                                    <label class="form-label">Maksimal Pelanggaran</label>
                                    <input type="number" class="form-control" name="max_violations" min="1" max="20" value="3">
                                </div>
                            </div>
                            <div id="create-questions-container"></div>
                            <button type="button" class="btn btn-sm btn-soft-primary mt-2" id="create-add-question">
                                Tambah Pertanyaan
                            </button>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft-danger" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-soft-primary">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL EDIT MATERI ================= --}}
    <div id="edit-material-modal" class="modal fade" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.content.update') }}" id="form-edit-material">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <input type="hidden" name="content" id="edit-content-hidden">

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Materi</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Judul Materi</label>
                            <input type="text" name="title" id="edit-title" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipe Konten</label>
                            <select name="type" id="edit-type" class="form-control">
                                <option value="text">Teks</option>
                                <option value="quiz">Kuis</option>
                            </select>
                        </div>

                        <div class="mb-3" id="edit-content-label">
                            <label class="form-label" id="edit-content-label-text">Konten</label>
                            <div id="edit-content-editor" style="height: 300px;"></div>
                        </div>

                        <div id="edit-quiz-builder" class="d-none">
                            <h6>Pertanyaan Kuis</h6>
                            <div class="card border mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Pengaturan Integritas Kuis</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="edit-integrity-enabled"
                                            name="integrity_mode_enabled" value="1">
                                        <label class="form-check-label" for="edit-integrity-enabled">Aktifkan Integrity Mode</label>
                                    </div>
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="edit-require-fullscreen"
                                            name="require_fullscreen" value="1">
                                        <label class="form-check-label" for="edit-require-fullscreen">Wajib Fullscreen saat mengerjakan</label>
                                    </div>
                                    <label class="form-label">Maksimal Pelanggaran</label>
                                    <input type="number" class="form-control" id="edit-max-violations" name="max_violations" min="1" max="20" value="3">
                                </div>
                            </div>
                            <div id="edit-questions-container"></div>
                            <button type="button" class="btn btn-sm btn-soft-primary mt-2" id="edit-add-question">
                                Tambah Pertanyaan
                            </button>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft-danger" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-soft-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ asset('assets/vendor/quill/quill.core.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .ql-editor {
            min-height: 200px;
        }

        .ql-editor img {
            max-width: 100%;
            height: auto;
        }

        /* Drag Handle Style */
        .drag-handle {
            cursor: move;
            opacity: 0.3;
            transition: opacity 0.2s;
        }

        .card:hover .drag-handle {
            opacity: 1;
        }

        /* Content Preview Style */
        .content-preview .ql-editor {
            border: none !important;
            padding: 0 !important;
        }

        .content-preview .ql-editor p {
            margin-bottom: 0.5rem;
        }

        .content-preview .ql-editor img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin: 8px 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .badge.rounded-circle {
                width: 35px !important;
                height: 35px !important;
                font-size: 14px !important;
            }
        }

        /* Dragula dragging style */
        .gu-mirror {
            opacity: 0.8;
            cursor: grabbing !important;
        }

        .gu-transit {
            opacity: 0.5;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/dragula/dragula.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/quill/quill.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Toggle preview function
        function togglePreview(button) {
            const preview = $(button).prev('.ql-editor');
            const isExpanded = preview.css('max-height') === 'none';

            if (isExpanded) {
                preview.css('max-height', '150px');
                $(button).html('<small>Lihat selengkapnya...</small>');
            } else {
                preview.css('max-height', 'none');
                $(button).html('<small>Tampilkan lebih sedikit</small>');
            }
        }

        let createQuill, editQuill;
        let createQIndex = 0;
        let editQIndex = 0;

        // Initialize Quill for Create Modal
        function initializeCreateQuill() {
            createQuill = new Quill('#create-content-editor', {
                theme: 'snow',
                modules: {
                    toolbar: {
                        container: [
                            [{
                                'header': [1, 2, 3, false]
                            }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{
                                'list': 'ordered'
                            }, {
                                'list': 'bullet'
                            }],
                            [{
                                'color': []
                            }, {
                                'background': []
                            }],
                            [{
                                'align': []
                            }],
                            ['link', 'image'],
                            ['clean']
                        ],
                        handlers: {
                            image: function() {
                                selectLocalImage(createQuill);
                            }
                        }
                    }
                }
            });
        }

        // Initialize Quill for Edit Modal
        function initializeEditQuill() {
            editQuill = new Quill('#edit-content-editor', {
                theme: 'snow',
                modules: {
                    toolbar: {
                        container: [
                            [{
                                'header': [1, 2, 3, false]
                            }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{
                                'list': 'ordered'
                            }, {
                                'list': 'bullet'
                            }],
                            [{
                                'color': []
                            }, {
                                'background': []
                            }],
                            [{
                                'align': []
                            }],
                            ['link', 'image'],
                            ['clean']
                        ],
                        handlers: {
                            image: function() {
                                selectLocalImage(editQuill);
                            }
                        }
                    }
                }
            });
        }

        // Function to adjust editor height based on content type
        function adjustEditorHeight(type, isEditModal = false) {
            const editorSelector = isEditModal ? '#edit-content-editor' : '#create-content-editor';
            const height = type === 'quiz' ? '150px' : '300px';
            $(editorSelector).css('height', height);
        }

        // Function to handle image upload
        function selectLocalImage(quill) {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.setAttribute('accept', 'image/*');
            input.click();

            input.onchange = () => {
                const file = input.files[0];

                if (file) {
                    const formData = new FormData();
                    formData.append('image', file);

                    Swal.fire({
                        title: 'Uploading...',
                        html: 'Please wait while image is being uploaded',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('{{ route('admin.content.uploadImage') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(result => {
                            Swal.close();

                            if (result.success) {
                                const range = quill.getSelection();
                                quill.insertEmbed(range.index, 'image', result.url);
                            } else {
                                Swal.fire('Error', 'Failed to upload image', 'error');
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            Swal.fire('Error', 'Failed to upload image', 'error');
                            console.error('Error:', error);
                        });
                }
            };
        }

        // Initialize on page load
        $(document).ready(function() {
            const drake = dragula([document.getElementById("materi-list")], {
                moves: function(el, container, handle) {
                    return handle.classList.contains('drag-handle') ||
                        handle.closest('.drag-handle') !== null;
                }
            });

            drake.on('drop', function() {
                let orders = {};
                $("#materi-list .card").each(function(index) {
                    orders[index] = $(this).data("id");

                    // Update number badge
                    $(this).find('.badge.rounded-circle').text(index + 1);
                });

                $.post("{{ route('admin.content.updateOrder') }}", {
                    _token: "{{ csrf_token() }}",
                    orders: orders
                }).done(function() {
                    // Optional: Show success toast
                    console.log('Order updated successfully');
                });
            });
        });

        // ==================== CREATE MODAL ====================

        // Create Modal Events
        $('#create-material-modal').on('shown.bs.modal', function() {
            if (!createQuill) {
                initializeCreateQuill();
            }

            // Reset form
            $('#create-type').val('text').trigger('change');
            createQIndex = 0;
            $('#create-questions-container').html('');
            $('#create-integrity-enabled').prop('checked', false);
            $('#create-require-fullscreen').prop('checked', false);
            $('input[name="max_violations"]').val(3);
            createQuill.setText('');
        });

        $('#create-type').on('change', function() {
            const type = $(this).val();
            if (type === 'quiz') {
                $('#create-quiz-builder').removeClass('d-none');
                $('#create-content-label-text').text('Deskripsi / Petunjuk Kuis');
            } else {
                $('#create-quiz-builder').addClass('d-none');
                $('#create-content-label-text').text('Konten');
            }
            adjustEditorHeight(type, false);
        });

        $('#create-add-question').on('click', function() {
            const currentQuestionCount = $('#create-questions-container .question-block').length;

            const qHtml = `
            <div class="card mb-3 question-block" data-index="${currentQuestionCount}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <strong>Pertanyaan ${currentQuestionCount + 1}</strong>
                    <button type="button" class="btn btn-sm btn-danger remove-question">
                        <i class="ti ti-trash"></i> Hapus
                    </button>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Pertanyaan</label>
                        <input type="text" name="questions[${currentQuestionCount}][text]" class="form-control" placeholder="Masukkan pertanyaan" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jawaban (centang untuk jawaban benar)</label>
                        <div class="options-container"></div>
                    </div>
                    <button type="button" class="btn btn-sm btn-soft-secondary add-create-option" data-q="${currentQuestionCount}">
                        <i class="ti ti-plus"></i> Tambah Jawaban
                    </button>
                </div>
            </div>
        `;
            $('#create-questions-container').append(qHtml);
        });

        // Event untuk ADD OPTION di CREATE MODAL
        $(document).on('click', '.add-create-option', function() {
            let q = $(this).data('q');
            let optionCount = $(`.question-block[data-index="${q}"] .option-block`).length;

            const optHtml = `
            <div class="input-group mb-2 option-block">
                <span class="input-group-text">
                    <input type="checkbox" name="questions[${q}][options][${optionCount}][is_correct]" class="form-check-input mt-0">
                </span>
                <input type="text" class="form-control"
                    name="questions[${q}][options][${optionCount}][text]"
                    placeholder="Isi jawaban" required>
                <button type="button" class="btn btn-soft-danger remove-option">
                    <i class="ti ti-x"></i>
                </button>
            </div>
        `;
            $(`.question-block[data-index="${q}"] .options-container`).append(optHtml);
        });

        // Submit Create Form
        $('#form-create-material').on('submit', function(e) {
            const content = createQuill.root.innerHTML;
            $('#create-content-hidden').val(content);

            const type = $('#create-type').val();
            if (type === 'quiz') {
                const questionCount = $('#create-questions-container .question-block').length;
                if (questionCount === 0) {
                    e.preventDefault();
                    Swal.fire('Error', 'Quiz harus memiliki minimal 1 pertanyaan', 'error');
                    return false;
                }
            }
        });

        // ==================== EDIT MODAL ====================

        // Edit Modal Events
        $('.btn-edit-material').on('click', function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            const type = $(this).data('type');

            $('#edit-id').val(id);
            $('#edit-title').val(title);
            $('#edit-type').val(type);

            // PENTING: Reset container dan index
            $('#edit-questions-container').html('');
            editQIndex = 0; // RESET INDEX!

            // Update label berdasarkan type
            if (type === 'quiz') {
                $('#edit-content-label-text').text('Deskripsi / Petunjuk Kuis');
                $('#edit-quiz-builder').removeClass('d-none');
            } else {
                $('#edit-content-label-text').text('Konten');
                $('#edit-quiz-builder').addClass('d-none');
            }

            // Adjust editor height
            adjustEditorHeight(type, true);

            // Load content via AJAX
            $.get("{{ url('admin/kursus/content') }}/" + id + "/quiz-data", function(res) {
                if (!editQuill) {
                    initializeEditQuill();
                }

                setTimeout(() => {
                    editQuill.root.innerHTML = res.content || '';
                    $('#edit-integrity-enabled').prop('checked', !!res.integrity_mode_enabled);
                    $('#edit-require-fullscreen').prop('checked', !!res.require_fullscreen);
                    $('#edit-max-violations').val(res.max_violations || 3);

                    if (type === 'quiz') {
                        res.questions.forEach((q, i) => {
                            let qHtml = `
                            <div class="card mb-3 edit-question-block" data-index="${i}">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <strong>Pertanyaan ${i + 1}</strong>
                                    <button type="button" class="btn btn-sm btn-danger remove-question">
                                        <i class="ti ti-trash"></i> Hapus
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Pertanyaan</label>
                                        <input type="text" name="questions[${i}][text]" value="${q.question}"
                                            class="form-control" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Jawaban (centang untuk jawaban benar)</label>
                                        <div class="edit-options-container">
                        `;

                            q.options.forEach((opt, oi) => {
                                // Convert to boolean to handle string "1" or "0" from backend
                                const isChecked = (opt.is_correct == 1 || opt
                                    .is_correct === true || opt.is_correct ===
                                    'true');

                                qHtml += `
                                <div class="input-group mb-2 option-block">
                                    <span class="input-group-text">
                                        <input type="checkbox" name="questions[${i}][options][${oi}][is_correct]"
                                            class="form-check-input mt-0" ${isChecked ? 'checked' : ''}>
                                    </span>
                                    <input type="text" name="questions[${i}][options][${oi}][text]"
                                        value="${opt.option_text.replace(/"/g, '&quot;')}" class="form-control" required>
                                    <button type="button" class="btn btn-outline-danger remove-option">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>
                            `;
                            });

                            qHtml += `
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-soft-secondary add-edit-option" data-q="${i}">
                                        <i class="ti ti-plus"></i> Tambah Jawaban
                                    </button>
                                </div>
                            </div>
                        `;

                            $('#edit-questions-container').append(qHtml);
                        });

                        // Set editQIndex setelah semua pertanyaan dimuat
                        editQIndex = res.questions.length;
                    }

                    $('#edit-material-modal').modal('show');
                }, 100);
            });
        });

        $('#edit-type').on('change', function() {
            const type = $(this).val();
            if (type === 'quiz') {
                $('#edit-quiz-builder').removeClass('d-none');
                $('#edit-content-label-text').text('Deskripsi / Petunjuk Kuis');
            } else {
                $('#edit-quiz-builder').addClass('d-none');
                $('#edit-content-label-text').text('Konten');
            }
            adjustEditorHeight(type, true);
        });

        $('#edit-add-question').on('click', function() {
            const currentQuestionCount = $('#edit-questions-container .edit-question-block').length;

            const qHtml = `
            <div class="card mb-3 edit-question-block" data-index="${currentQuestionCount}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <strong>Pertanyaan ${currentQuestionCount + 1}</strong>
                    <button type="button" class="btn btn-sm btn-danger remove-question">
                        <i class="ti ti-trash"></i> Hapus
                    </button>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Pertanyaan</label>
                        <input type="text" name="questions[${currentQuestionCount}][text]" class="form-control" placeholder="Masukkan pertanyaan" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Jawaban (centang untuk jawaban benar)</label>
                        <div class="edit-options-container"></div>
                    </div>
                    <button type="button" class="btn btn-sm btn-soft-secondary add-edit-option" data-q="${currentQuestionCount}">
                        <i class="ti ti-plus"></i> Tambah Jawaban
                    </button>
                </div>
            </div>
        `;
            $('#edit-questions-container').append(qHtml);
        });

        // Event untuk ADD OPTION di EDIT MODAL
        $(document).on('click', '.add-edit-option', function() {
            let q = $(this).data('q');
            let optionCount = $(`.edit-question-block[data-index="${q}"] .option-block`).length;

            const optHtml = `
            <div class="input-group mb-2 option-block">
                <span class="input-group-text">
                    <input type="checkbox" name="questions[${q}][options][${optionCount}][is_correct]" class="form-check-input mt-0">
                </span>
                <input type="text" class="form-control"
                    name="questions[${q}][options][${optionCount}][text]"
                    placeholder="Isi jawaban" required>
                <button type="button" class="btn btn-soft-danger remove-option">
                    <i class="ti ti-x"></i>
                </button>
            </div>
        `;
            $(`.edit-question-block[data-index="${q}"] .edit-options-container`).append(optHtml);
        });

        // Submit Edit Form
        $('#form-edit-material').on('submit', function(e) {
            const content = editQuill.root.innerHTML;
            $('#edit-content-hidden').val(content);

            const type = $('#edit-type').val();
            if (type === 'quiz') {
                const questionCount = $('#edit-questions-container .edit-question-block').length;
                if (questionCount === 0) {
                    e.preventDefault();
                    Swal.fire('Error', 'Quiz harus memiliki minimal 1 pertanyaan', 'error');
                    return false;
                }
            }
        });

        // ==================== SHARED EVENTS ====================

        // Remove option
        $(document).on('click', '.remove-option', function() {
            $(this).closest('.option-block').remove();
        });

        // Remove question dengan re-indexing
        $(document).on('click', '.remove-question', function() {
            const questionBlock = $(this).closest('.question-block, .edit-question-block');
            const container = questionBlock.parent();

            Swal.fire({
                title: 'Konfirmasi',
                text: `Hapus pertanyaan ini?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    questionBlock.remove();

                    // Re-index semua pertanyaan yang tersisa
                    container.find('.question-block, .edit-question-block').each(function(index) {
                        // Update data-index
                        $(this).attr('data-index', index);

                        // Update nomor di header
                        $(this).find('.card-header strong').text('Pertanyaan ' + (index + 1));

                        // Update name attribute untuk input pertanyaan
                        $(this).find('input[type="text"]').first().attr('name',
                            `questions[${index}][text]`);

                        // Update name attribute untuk semua options
                        $(this).find('.option-block').each(function(optIndex) {
                            $(this).find('input[type="checkbox"]').attr('name',
                                `questions[${index}][options][${optIndex}][is_correct]`);
                            $(this).find('input[type="text"]').attr('name',
                                `questions[${index}][options][${optIndex}][text]`);
                        });

                        // Update data-q pada button add-option
                        $(this).find('.add-create-option, .add-edit-option').attr('data-q', index);
                    });
                }
            });
        });
    </script>
@endpush
