@extends('template', ['title' => 'Edit Kursus'])

@section('content')
    <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
        <div class="flex-grow-1">
            <h4 class="fs-18 text-uppercase fw-bold mb-0">Edit Kursus</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form method="POST" action="{{ route('admin.kursus.update', $kursus->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-header bg-transparent border-bottom">
                        <ul class="nav nav-tabs card-header-tabs" id="kursusTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pane-dasar"
                                    type="button">
                                    Informasi Dasar
                                </button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-lain" type="button">
                                    Informasi Lain
                                </button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-prasyarat"
                                    type="button">
                                    Prasyarat
                                </button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pane-modul" type="button">
                                    Modul
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content">

                            {{-- ===================== TAB INFORMASI DASAR ===================== --}}
                            <div class="tab-pane fade show active" id="pane-dasar">
                                <div class="row g-3">

                                    <div class="col-12">
                                        <label class="form-label">Thumbnail</label>
                                        <input type="file" name="thumbnail"
                                            class="form-control @error('thumbnail') is-invalid @enderror">

                                        @error('thumbnail')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror

                                        <p class="mt-1 text-muted" style="font-size: 13px;">
                                            Kosongkan jika tidak ingin mengganti thumbnail.
                                        </p>

                                        <img src="/uploads/kursus/{{ $kursus->thumbnail }}" class="rounded mt-2"
                                            width="180">
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Judul <span class="text-danger">*</span></label>
                                        <input type="text" name="title" value="{{ $kursus->title }}"
                                            class="form-control @error('title') is-invalid @enderror">

                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Deskripsi Singkat <span
                                                class="text-danger">*</span></label>
                                        <textarea name="short_description" rows="2" class="form-control @error('short_description') is-invalid @enderror">{{ $kursus->short_description }}</textarea>

                                        @error('short_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                </div>
                            </div>

                            {{-- ===================== TAB INFORMASI LAIN ===================== --}}
                            <div class="tab-pane fade" id="pane-lain">
                                <div class="row g-3">

                                    <div class="col-12">
                                        <label class="form-label">Deskripsi Lengkap <span
                                                class="text-danger">*</span></label>
                                        <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ $kursus->description }}</textarea>

                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Kategori</label>
                                        <select name="category" class="form-control category">
                                            <option></option>
                                            <option value="programming"
                                                {{ $kursus->category == 'programming' ? 'selected' : '' }}>Programming
                                            </option>
                                            <option value="design" {{ $kursus->category == 'design' ? 'selected' : '' }}>
                                                Design</option>
                                            <option value="marketing"
                                                {{ $kursus->category == 'marketing' ? 'selected' : '' }}>Marketing</option>
                                            <option value="business"
                                                {{ $kursus->category == 'business' ? 'selected' : '' }}>Business</option>
                                            <option value="cybersecurity"
                                                {{ $kursus->category == 'cybersecurity' ? 'selected' : '' }}>Cybersecurity
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Tingkat Kesulitan</label>
                                        <select name="difficulty" class="form-control difficulty">
                                            <option></option>
                                            <option value="pemula" {{ $kursus->difficulty == 'pemula' ? 'selected' : '' }}>
                                                Pemula</option>
                                            <option value="menengah"
                                                {{ $kursus->difficulty == 'menengah' ? 'selected' : '' }}>Menengah</option>
                                            <option value="lanjutan"
                                                {{ $kursus->difficulty == 'lanjutan' ? 'selected' : '' }}>Lanjutan</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Sertifikat</label>
                                        <select name="certificate" class="form-control certificate">
                                            <option></option>
                                            <option value="1" {{ $kursus->certificate == 1 ? 'selected' : '' }}>Ya
                                            </option>
                                            <option value="0" {{ $kursus->certificate == 0 ? 'selected' : '' }}>Tidak
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-control status">
                                            <option></option>
                                            <option value="aktif" {{ $kursus->status == 'aktif' ? 'selected' : '' }}>Aktif
                                            </option>
                                            <option value="nonaktif" {{ $kursus->status == 'nonaktif' ? 'selected' : '' }}>
                                                Nonaktif</option>
                                            <option value="arsip" {{ $kursus->status == 'arsip' ? 'selected' : '' }}>Arsip
                                            </option>
                                        </select>
                                    </div>

                                </div>
                            </div>

                            {{-- ===================== TAB PRASYARAT ===================== --}}
                            <div class="tab-pane fade" id="pane-prasyarat">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Kursus Prasyarat</label>
                                        <p class="text-muted small">Pilih kursus yang harus diselesaikan oleh pengguna sebelum dapat mendaftar ke kursus ini. Kosongkan jika tidak ada prasyarat.</p>
                                        @php
                                            $selectedPrerequisites = old('prerequisites', $kursus->prerequisites->pluck('id')->toArray());
                                        @endphp
                                        <select name="prerequisites[]" class="form-control prerequisites-select" multiple>
                                            @foreach ($allKursuses as $k)
                                                <option value="{{ $k->id }}"
                                                    {{ in_array($k->id, $selectedPrerequisites) ? 'selected' : '' }}>
                                                    {{ $k->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- ===================== TAB MODUL ===================== --}}
                            <div class="tab-pane fade" id="pane-modul">
                                <div class="card card-body">
                                    <div id="module-list" data-plugin="dragula">
                                        @foreach ($kursus->modules as $modul)
                                            <div class="card mb-2 p-2" data-id="{{ $modul->id }}">
                                                <div class="row g-2 align-items-center">

                                                    <div class="col-12 col-md-6">
                                                        <strong class="fs-6">{{ $modul->title }}</strong>
                                                    </div>

                                                    <div class="col-12 col-md-6 text-md-end">
                                                        <div class="btn-group w-100 w-md-auto">
                                                            <a href="{{ route('admin.module.detail', $modul->id) }}"
                                                                class="btn btn-sm btn-primary">
                                                                Kelola
                                                            </a>

                                                            <button type="button" class="btn btn-sm btn-warning"
                                                                data-bs-toggle="modal" data-bs-target="#edit-modal"
                                                                data-id="{{ $modul->id }}"
                                                                data-title="{{ $modul->title }}">
                                                                Edit
                                                            </button>

                                                            <button type="button" class="btn btn-sm btn-danger"
                                                                data-bs-toggle="modal" data-bs-target="#delete-modal"
                                                                data-id="{{ $modul->id }}">
                                                                Hapus
                                                            </button>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                </div>

                                <button type="button" class="btn btn-soft-primary mt-3" data-bs-toggle="modal"
                                    data-bs-target="#create-modal">
                                    Tambah Modul
                                </button>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <a href="{{ route('admin.kursus.index') }}" class="btn btn-soft-danger me-2">Kembali</a>
                        <button type="submit" class="btn btn-soft-primary">Simpan</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- ================= MODAL TAMBAH MODUL ================= --}}
    <div id="create-modal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.module.store', $kursus->id) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah Modul Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">Nama Modul</label>
                        <input type="text" name="title" class="form-control" placeholder="Masukkan nama modul">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft-danger" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-soft-primary">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL EDIT MODUL ================= --}}
    <div id="edit-modal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.module.update') }}">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">

                    <div class="modal-header">
                        <h5 class="modal-title">Edit Modul</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <label class="form-label">Nama Modul</label>
                        <input type="text" name="title" id="edit-title" class="form-control">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft-danger" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-soft-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= MODAL DELETE ================= --}}
    <div id="delete-modal" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('admin.module.delete') }}">
                    @csrf
                    <input type="hidden" name="id" id="delete-id">

                    <div class="modal-header">
                        <h5 class="modal-title">Hapus Modul</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p>Anda yakin ingin menghapus modul ini?</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-soft-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendor/dragula/dragula.min.js') }}"></script>

    <script>
        // Drag & Drop Modul
        dragula([document.getElementById("module-list")]).on('drop', function() {
            let orders = {};
            $("#module-list .card").each(function(index) {
                orders[index] = $(this).data("id");
            });

            $.post("{{ route('admin.module.updateOrder') }}", {
                _token: "{{ csrf_token() }}",
                orders: orders
            });
        });

        // Select2
        $('.category').select2({
            placeholder: '- Pilih Kategori -',
            minimumResultsForSearch: Infinity
        });
        $('.difficulty').select2({
            placeholder: '- Pilih Tingkat Kesulitan -',
            minimumResultsForSearch: Infinity
        });
        $('.certificate').select2({
            placeholder: 'Dengan Sertifikat?',
            minimumResultsForSearch: Infinity
        });
        $('.status').select2({
            placeholder: '- Pilih Status -',
            minimumResultsForSearch: Infinity
        });
        $('.prerequisites-select').select2({
            placeholder: '- Pilih Kursus Prasyarat (opsional) -',
            allowClear: true
        });

        document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
            btn.addEventListener('shown.bs.tab', function(e) {
                localStorage.setItem('activeTabKursus', e.target.dataset.bsTarget);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            let activeTab = localStorage.getItem('activeTabKursus');

            if (activeTab) {
                let tabButton = document.querySelector(`[data-bs-target="${activeTab}"]`);
                if (tabButton) {
                    new bootstrap.Tab(tabButton).show();
                }
            }
        });

        // Modal Edit Modul
        $('#edit-modal').on('show.bs.modal', function(e) {
            let button = $(e.relatedTarget);
            $('#edit-id').val(button.data('id'));
            $('#edit-title').val(button.data('title'));
        });

        // Modal Delete Modul
        $('#delete-modal').on('show.bs.modal', function(e) {
            let button = $(e.relatedTarget);
            $('#delete-id').val(button.data('id'));
        });
    </script>
@endpush
