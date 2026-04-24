@extends('template', ['title' => 'Monitoring Integritas Kuis'])

@section('content')
    <div class="page-title-head d-flex justify-content-between align-items-center w-100 flex-wrap gap-2">
        <h4 class="fs-18 text-uppercase fw-bold mb-0">Monitoring Integritas Kuis</h4>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-body">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-auto d-flex flex-grow-1 align-items-center">
                        <input type="text" id="search-integrity" class="form-control bg-light border-0 me-2"
                            placeholder="Cari user atau kuis..." />

                        <button class="btn btn-soft-dark btn-search me-2">
                            <i class="ti ti-search"></i>
                            <span class="d-none d-md-inline ms-1">Cari</span>
                        </button>

                        <div class="dropdown">
                            <button class="btn btn-soft-secondary dropdown-toggle" type="button" id="filterAutoSubmit"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-filter"></i>
                                <span class="d-none d-md-inline ms-1">Auto Submit</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterAutoSubmit">
                                <li><a class="dropdown-item filter-auto" href="#" data-status="all">Semua</a></li>
                                <li><a class="dropdown-item filter-auto" href="#" data-status="yes">Ya</a></li>
                                <li><a class="dropdown-item filter-auto" href="#" data-status="no">Tidak</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="integrity-table" class="table table-bordered table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Pengguna</th>
                                <th>Kursus</th>
                                <th>Kuis</th>
                                <th>Pelanggaran</th>
                                <th>Auto Submit</th>
                                <th>Waktu Selesai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="integrityDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Riwayat Pelanggaran Integritas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="integrity-detail-body">
                    <div class="text-center py-4">Memuat data...</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.1/css/dataTables.dataTables.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/2.3.1/js/dataTables.min.js"></script>
    <script src="{{ asset('assets/js/pages/admin/kursus/integrity.js') }}"></script>
@endpush
