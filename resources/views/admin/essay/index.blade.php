@extends('template', ['title' => 'Penilaian Esai'])

@section('content')
    <div class="page-title-head d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
        <h4 class="fs-18 text-uppercase fw-bold mb-0">Penilaian Esai</h4>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-body">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-auto d-flex flex-grow-1 align-items-center gap-2">
                        <input type="text" id="search-essay" class="form-control bg-light border-0"
                            placeholder="Cari pengguna atau kuis..." />
                        <button class="btn btn-soft-dark btn-search">
                            <i class="ti ti-search"></i>
                            <span class="d-none d-md-inline ms-1">Cari</span>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-soft-secondary dropdown-toggle" type="button"
                                data-bs-toggle="dropdown">
                                <i class="ti ti-filter"></i>
                                <span class="d-none d-md-inline ms-1" id="filter-label">Semua Status</span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item filter-status" href="#" data-status="all">Semua</a></li>
                                <li><a class="dropdown-item filter-status" href="#" data-status="pending_review">Menunggu Penilaian</a></li>
                                <li><a class="dropdown-item filter-status" href="#" data-status="graded">Sudah Dinilai</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="essay-table" class="table table-bordered table-striped mb-0"
                        data-list-url="{{ route('admin.essay.list') }}"
                        data-base-url="{{ url('admin/essay') }}">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Pengguna</th>
                                <th>Kursus</th>
                                <th>Kuis Esai</th>
                                <th>Status</th>
                                <th>Nilai</th>
                                <th>Waktu Kirim</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.1/css/dataTables.bootstrap5.min.css">
    <style>
        #essay-table_wrapper .dt-layout-row:first-child {
            margin-bottom: 0.75rem;
        }
        #essay-table_wrapper .dt-layout-row:last-child {
            margin-top: 0.75rem;
        }
        div.dt-length label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0;
            white-space: nowrap;
        }
        div.dt-length select {
            width: auto;
            display: inline-block;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/2.3.1/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.1/js/dataTables.bootstrap5.min.js"></script>
    <script>
        let essayTable;
        let currentStatus = 'all';

        $(document).ready(function() {
            const $table = $('#essay-table');
            const listUrl = $table.data('list-url');
            const baseUrl = $table.data('base-url');

            essayTable = $table.DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: listUrl,
                    type: 'POST',
                    data: function(d) {
                        d._token = '{{ csrf_token() }}';
                        d.status_filter = currentStatus;
                        d.search = { value: $('#search-essay').val() };
                    }
                },
                columns: [
                    { data: null, render: (_, __, ___, meta) => meta.row + 1, orderable: false },
                    { data: 'user_name', render: (d, _, row) => `<div>${d}</div><small class="text-muted">${row.user_email}</small>` },
                    { data: 'kursus_title' },
                    { data: 'content_title' },
                    {
                        data: 'grading_status',
                        render: (d) => d === 'pending_review'
                            ? '<span class="badge bg-warning text-dark"><i class="ti ti-clock me-1"></i>Menunggu Penilaian</span>'
                            : '<span class="badge bg-success"><i class="ti ti-check me-1"></i>Sudah Dinilai</span>'
                    },
                    {
                        data: 'score',
                        render: (d, _, row) => row.grading_status === 'graded'
                            ? `<span class="fw-bold ${d >= 70 ? 'text-success' : 'text-danger'}">${d}</span>`
                            : '<span class="text-muted">-</span>'
                    },
                    { data: 'submitted_at', defaultContent: '-' },
                    {
                        data: 'id',
                        render: (d) => `<a href="${baseUrl}/${d}/show" class="btn btn-sm btn-soft-primary"><i class="ti ti-pencil me-1"></i>Nilai</a>`
                    },
                ],
                pageLength: 20,
                lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
                language: {
                    processing: 'Memuat data...',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
                    infoEmpty: 'Tidak ada data',
                    paginate: {
                        first: '«',
                        last: '»',
                        next: '›',
                        previous: '‹',
                    },
                },
            });

            $('.btn-search').on('click', () => essayTable.ajax.reload());
            $('#search-essay').on('keypress', (e) => { if (e.which === 13) essayTable.ajax.reload(); });

            $('.filter-status').on('click', function(e) {
                e.preventDefault();
                currentStatus = $(this).data('status');
                $('#filter-label').text($(this).text());
                essayTable.ajax.reload();
            });
        });
    </script>
@endpush
