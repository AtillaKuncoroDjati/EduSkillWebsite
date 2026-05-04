@extends('template', ['title' => 'Tambah Kursus'])

@section('content')
    <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
        <div class="flex-grow-1">
            <h4 class="fs-18 text-uppercase fw-bold mb-0">Tambah Kursus</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <form method="POST" action="{{ route('admin.kursus.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-header bg-transparent border-bottom">
                        <ul class="nav nav-tabs card-header-tabs" id="kursusTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-dasar" data-bs-toggle="tab"
                                    data-bs-target="#pane-dasar" type="button" role="tab">Informasi Dasar</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-lain" data-bs-toggle="tab" data-bs-target="#pane-lain"
                                    type="button" role="tab">Informasi Lain</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-prasyarat" data-bs-toggle="tab"
                                    data-bs-target="#pane-prasyarat" type="button" role="tab">Prasyarat</button>
                            </li>
                            {{-- <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-modul" data-bs-toggle="tab" data-bs-target="#pane-modul"
                                    type="button" role="tab">Modul</button>
                            </li> --}}
                        </ul>
                    </div>

                    <div class="card-body">
                        <div class="tab-content" id="kursusTabContent">

                            <!-- ================= TAB INFORMASI DASAR ================= -->
                            <div class="tab-pane fade show active" id="pane-dasar" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="thumbnail" class="form-label">Thumbnail <span
                                                class="text-danger">*</span></label>
                                        <input type="file" id="thumbnail" name="thumbnail"
                                            class="form-control @error('thumbnail') is-invalid @enderror">
                                        @error('thumbnail')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="title" class="form-label">Judul <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="title" name="title"
                                            class="form-control @error('title') is-invalid @enderror">
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="short_description" class="form-label">Deskripsi Singkat <span
                                                class="text-danger">*</span></label>
                                        <textarea id="short_description" name="short_description"
                                            class="form-control @error('short_description') is-invalid @enderror" rows="2"></textarea>
                                        @error('short_description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- ================= TAB INFORMASI LAIN ================= -->
                            <div class="tab-pane fade" id="pane-lain" role="tabpanel">
                                <div class="row g-3">

                                    <div class="col-12">
                                        <label for="description" class="form-label">Deskripsi Lengkap <span
                                                class="text-danger">*</span></label>
                                        <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                                            rows="5"></textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                        <select class="form-control category @error('category') is-invalid @enderror"
                                            name="category">
                                            <option></option>
                                            <option value="programming">Programming</option>
                                            <option value="design">Design</option>
                                            <option value="marketing">Marketing</option>
                                            <option value="business">Business</option>
                                            <option value="cybersecurity">Cybersecurity</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Kesulitan <span class="text-danger">*</span></label>
                                        <select class="form-control difficulty @error('difficulty') is-invalid @enderror"
                                            name="difficulty">
                                            <option></option>
                                            <option value="pemula">Pemula</option>
                                            <option value="menengah">Menengah</option>
                                            <option value="lanjutan">Lanjutan</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="form-label">Sertifikat <span class="text-danger">*</span></label>
                                        <select class="form-control certificate @error('certificate') is-invalid @enderror"
                                            name="certificate">
                                            <option></option>
                                            <option value="1">Ya</option>
                                            <option value="0">Tidak</option>
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Status</label>
                                        <select class="form-control status @error('status') is-invalid @enderror"
                                            name="status">
                                            <option></option>
                                            <option value="aktif">Aktif</option>
                                            <option value="nonaktif">Nonaktif</option>
                                            <option value="arsip">Arsip</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- ================= TAB PRASYARAT ================= -->
                            <div class="tab-pane fade" id="pane-prasyarat" role="tabpanel">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Kursus Prasyarat</label>
                                        <p class="text-muted small">Pilih kursus yang harus diselesaikan oleh pengguna sebelum dapat mendaftar ke kursus ini. Kosongkan jika tidak ada prasyarat.</p>
                                        <select name="prerequisites[]" class="form-control prerequisites-select" multiple>
                                            @foreach ($allKursuses as $k)
                                                <option value="{{ $k->id }}"
                                                    {{ in_array($k->id, old('prerequisites', [])) ? 'selected' : '' }}>
                                                    {{ $k->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- <!-- ================= TAB MODUL ================= -->
                            <div class="tab-pane fade" id="pane-modul" role="tabpanel">

                                <div class="row">
                                    <div class="col-12">
                                        <div class="card card-body">
                                            <div id="module-list" data-plugin="dragula">
                                                @foreach ($kursus->modules as $modul)
                                                    <div class="card p-2 mb-2" data-id="{{ $modul->id }}">
                                                        <strong>{{ $modul->title }}</strong>

                                                        <a href="{{ route('admin.module.detail', $modul->id) }}"
                                                            class="btn btn-sm btn-primary float-end">
                                                            Kelola Materi
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#create-modal">
                                    Tambah Modul
                                </button>
                            </div> --}}

                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-end">
                        <button type="reset" class="btn btn-soft-danger me-2">Reset</button>
                        <button type="submit" class="btn btn-soft-primary">Tambah</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- <div id="create-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="create-modalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="create-modalLabel">
                        Tambah Modul Baru
                    </h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.module.store', $kursus->id) }}">
                    @csrf
                    <div class="modal-body">
                        <label for="title" class="form-label">
                            Nama Modul<span class="text-danger ms-1">*</span>
                        </label>
                        <input type="text" id="title" name="title"
                            class="form-control @error('title') is-invalid @enderror" autocomplete="off"
                            placeholder="Masukkan nama modul">

                        @error('title')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                            Tutup
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Tambah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div> --}}
@endsection

@push('styles')
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/dragula/dragula.min.js') }}"></script>

    <script>
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

        $(document).ready(function() {
            $('.category').select2({
                placeholder: '- Pilih Kategori -',
                minimumResultsForSearch: Infinity
            });
        });

        $(document).ready(function() {
            $('.difficulty').select2({
                placeholder: '- Pilih Tingkat Kesulitan -',
                minimumResultsForSearch: Infinity
            });
        });

        $(document).ready(function() {
            $('.certificate').select2({
                placeholder: 'Dengan Sertifikat?',
                minimumResultsForSearch: Infinity
            });
        });

        $(document).ready(function() {
            $('.status').select2({
                placeholder: '- Pilih Status -',
                minimumResultsForSearch: Infinity
            });
        });

        $(document).ready(function() {
            $('.prerequisites-select').select2({
                placeholder: '- Pilih Kursus Prasyarat (opsional) -',
                allowClear: true
            });
        });

        document.addEventListener("DOMContentLoaded", function() {
            let lastTab = localStorage.getItem("kursus_last_tab");

            if (lastTab) {
                let tabTrigger = document.querySelector(`button[data-bs-target="${lastTab}"]`);
                if (tabTrigger) {
                    let tab = new bootstrap.Tab(tabTrigger);
                    tab.show();
                }
            }

            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
                btn.addEventListener("shown.bs.tab", function(event) {
                    localStorage.setItem("kursus_last_tab", event.target.getAttribute(
                        "data-bs-target"));
                });
            });
        });
    </script>
@endpush
