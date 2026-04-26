@extends('template', ['title' => 'Daftar Pengguna'])

@section('content')
    <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column gap-2">
        <div class="flex-grow-1">
            <h4 class="fs-18 text-uppercase fw-bold mb-0">Daftar Pengguna</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card card-body shadow-sm">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-auto d-flex flex-grow-1 align-items-center">
                        <input type="text" id="search-user" class="form-control bg-light border-0 me-2"
                            placeholder="Cari nama, email, username, atau telepon..." />

                        <button class="btn btn-soft-dark btn-search me-2">
                            <i class="ti ti-search"></i>
                            <span class="d-none d-md-inline ms-1">Cari</span>
                        </button>

                        <div class="dropdown me-2">
                            <button class="btn btn-soft-secondary dropdown-toggle" type="button" id="filterStatusUser"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-filter"></i>
                                <span class="d-none d-md-inline ms-1">Status</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterStatusUser">
                                <li><a class="dropdown-item filter-status" href="#" data-status="all">Semua Status</a>
                                </li>
                                <li><a class="dropdown-item filter-status" href="#" data-status="aktif">Aktif</a></li>
                                <li><a class="dropdown-item filter-status" href="#"
                                        data-status="nonaktif">Nonaktif</a></li>
                                <li><a class="dropdown-item filter-status" href="#" data-status="banned">Banned</a>
                                </li>
                            </ul>
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-soft-secondary dropdown-toggle" type="button" id="filterPermission"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-shield"></i>
                                <span class="d-none d-md-inline ms-1">Role</span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterPermission">
                                <li><a class="dropdown-item filter-permission" href="#" data-permission="all">Semua
                                        Role</a></li>
                                <li><a class="dropdown-item filter-permission" href="#"
                                        data-permission="admin">Admin</a></li>
                                <li><a class="dropdown-item filter-permission" href="#"
                                        data-permission="user">User</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-md-auto">
                        <button type="button" class="btn btn-soft-success w-100" data-bs-toggle="modal"
                            data-bs-target="#modalTambahUser">
                            <i class="ti ti-user-plus me-1"></i>Tambah Pengguna
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="ohmytable" class="table table-bordered table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" width="5%">#</th>
                                <th class="text-center" width="8%">Avatar</th>
                                <th class="text-left" width="20%">Nama Lengkap</th>
                                <th class="text-left" width="25%">Informasi Kontak</th>
                                <th class="text-center" width="10%">Hak Akses</th>
                                <th class="text-center" width="10%">Status</th>
                                <th class="text-center" width="12%">Terdaftar</th>
                                <th class="text-center" width="10%">
                                    <i class="ti ti-settings fs-18"></i>
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <div class="modal fade" id="modalTambahUser" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.user.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-user-plus me-2"></i>Tambah Pengguna Baru
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" name="phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hak Akses <span class="text-danger">*</span></label>
                                <select name="permission" class="form-control" required>
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                    <option value="banned">Banned</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Avatar</label>
                                <input type="file" name="avatar" class="form-control" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft-danger" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-soft-primary">
                            <i class="ti ti-device-floppy me-1"></i>Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="modalEditUser" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('admin.user.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="edit-id">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ti ti-pencil me-2"></i>Edit Pengguna
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12 text-center mb-3">
                                <img id="edit-avatar-preview" src="" class="rounded-circle"
                                    style="width: 100px; height: 100px; object-fit: cover;">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="edit-name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" id="edit-username" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="edit-email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nomor Telepon</label>
                                <input type="text" name="phone" id="edit-phone" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Password Baru</label>
                                <input type="password" name="password" class="form-control">
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Konfirmasi Password</label>
                                <input type="password" name="password_confirmation" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hak Akses <span class="text-danger">*</span></label>
                                <select name="permission" id="edit-permission" class="form-control" required>
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="edit-status" class="form-control" required>
                                    <option value="aktif">Aktif</option>
                                    <option value="nonaktif">Nonaktif</option>
                                    <option value="banned">Banned</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Ubah Avatar</label>
                                <input type="file" name="avatar" class="form-control" accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF. Maksimal 2MB</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-soft-danger" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-soft-primary">
                            <i class="ti ti-device-floppy me-1"></i>Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Suspend --}}
    <div class="modal fade" id="modalSuspendUser" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ti ti-clock-pause me-2"></i>Suspend Pengguna
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Suspend akun: <strong id="suspend-user-name"></strong></p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Durasi Suspensi</label>
                        <div class="d-flex flex-wrap gap-2 mb-2" id="suspend-presets">
                            <button type="button" class="btn btn-sm btn-outline-secondary suspend-preset" data-minutes="30">30 Menit</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary suspend-preset" data-minutes="60">1 Jam</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary suspend-preset" data-minutes="360">6 Jam</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary suspend-preset" data-minutes="720">12 Jam</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary suspend-preset" data-minutes="1440">1 Hari</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary suspend-preset" data-minutes="4320">3 Hari</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary suspend-preset" data-minutes="10080">7 Hari</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary suspend-preset" data-minutes="43200">30 Hari</button>
                        </div>
                        <div class="input-group">
                            <input type="number" id="suspend-duration" class="form-control" placeholder="Atau masukkan menit kustom" min="1" max="43200">
                            <span class="input-group-text">menit</span>
                        </div>
                        <small class="text-muted">Maksimal 43200 menit (30 hari)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan Suspensi <span class="text-muted fw-normal">(opsional)</span></label>
                        <textarea id="suspend-reason" class="form-control" rows="3" placeholder="Contoh: Melanggar ketentuan penggunaan platform..." maxlength="500"></textarea>
                        <small class="text-muted"><span id="suspend-reason-count">0</span>/500 karakter</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-soft-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning" id="btn-confirm-suspend">
                        <i class="ti ti-clock-pause me-1"></i>Suspend
                    </button>
                </div>
            </div>
        </div>
    </div>

    <form id="form-delete" style="display: none;" action="{{ route('admin.user.delete') }}" method="POST">
        @csrf
        <input id="id-delete" name="id" type="hidden">
    </form>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.1/css/dataTables.dataTables.min.css">
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/jquery-confirm/jquery-confirm.min.js') }}"></script>
    <script src="https://cdn.datatables.net/2.3.1/js/dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('assets/js/pages/admin/user/page.js') }}"></script>
@endpush
