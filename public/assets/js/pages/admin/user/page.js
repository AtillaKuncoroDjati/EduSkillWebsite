jQuery.index = {
    data: {
        table: null,
        searchInput: null,
        searchButton: null,
        statusFilter: 'all',
        permissionFilter: 'all',
        suspendUserId: null,
    },
    init: function () {
        var self = this;

        self.data.searchInput = $('#search-user');
        self.data.searchButton = $('.btn-search');

        self.initTable();
        self.setEvents();
    },
    initTable: function () {
        var self = this;

        self.data.table = $('#ohmytable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/admin/pengguna/request',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function (d) {
                    d.search = {
                        value: self.data.searchInput.val(),
                        regex: false
                    };
                    d.status_filter = self.data.statusFilter;
                    d.permission_filter = self.data.permissionFilter;
                    return d;
                }
            },
            columns: [
                {
                    data: null,
                    className: 'text-center fw-bold align-middle',
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    },
                },
                {
                    data: 'avatar',
                    className: 'text-center align-middle',
                    render: function (avatar) {
                        const avatarUrl = avatar && avatar !== 'default-avatar.png'
                            ? '/uploads/avatar/' + avatar
                            : '/uploads/avatar/default-avatar.png';
                        return `<img src="${avatarUrl}" class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">`;
                    }
                },
                {
                    data: 'name',
                    className: 'align-middle',
                    render: function (name) {
                        return '<strong>' + name + '</strong>';
                    }
                },
                {
                    data: null,
                    className: 'align-middle',
                    render: function (data) {
                        let html = '<div class="small">';
                        html += '<div class="mb-1"><i class="ti ti-at"></i> <strong>Email:</strong> ' + (data.email || '-') + '</div>';
                        html += '<div class="mb-1"><i class="ti ti-user-circle"></i> <strong>Username:</strong> ' + (data.username || '-') + '</div>';
                        html += '<div><i class="ti ti-phone"></i> <strong>Telepon:</strong> ' + (data.phone || '-') + '</div>';
                        html += '</div>';
                        return html;
                    }
                },
                {
                    data: 'permission',
                    className: 'text-center align-middle',
                    render: function (permission) {
                        return permission === 'admin'
                            ? '<span class="badge bg-danger"><i class="ti ti-shield-check"></i> Admin</span>'
                            : '<span class="badge bg-primary"><i class="ti ti-user"></i> User</span>';
                    }
                },
                {
                    data: null,
                    className: 'text-center align-middle',
                    render: function (data) {
                        const isSuspended = data.is_suspended && data.suspended_until
                            && new Date(data.suspended_until) > new Date();

                        if (isSuspended) {
                            const until = new Date(data.suspended_until);
                            const formatted = until.toLocaleDateString('id-ID', {
                                day: '2-digit', month: 'short', year: 'numeric',
                                hour: '2-digit', minute: '2-digit'
                            });
                            return `<span class="badge bg-warning text-dark" title="Suspended hingga ${formatted}">
                                <i class="ti ti-clock-pause"></i> Suspended
                            </span>`;
                        }

                        const badges = {
                            'aktif': '<span class="badge bg-success">Aktif</span>',
                            'nonaktif': '<span class="badge bg-warning text-dark">Nonaktif</span>',
                            'banned': '<span class="badge bg-danger">Banned</span>'
                        };
                        return badges[data.status] || '<span class="badge bg-secondary">Unknown</span>';
                    }
                },
                {
                    data: 'created_at',
                    className: 'text-center align-middle',
                    render: function (date) {
                        const d = new Date(date);
                        return d.toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                    }
                },
                {
                    data: null,
                    className: 'text-center align-middle',
                    render: function (data) {
                        const isSuspended = data.is_suspended && data.suspended_until
                            && new Date(data.suspended_until) > new Date();

                        let suspendBtn = '';
                        if (isSuspended) {
                            suspendBtn = `<button class='btn btn-sm btn-soft-success btn-unsuspend w-100'
                                data-id='${data.id}' data-name='${data.name}'>
                                <i class='ti ti-player-play me-1'></i>Cabut Suspend
                            </button>`;
                        } else {
                            suspendBtn = `<button class='btn btn-sm btn-soft-warning btn-suspend w-100'
                                data-id='${data.id}' data-name='${data.name}'>
                                <i class='ti ti-clock-pause me-1'></i>Suspend
                            </button>`;
                        }

                        return `
                            <div class='d-grid gap-1'>
                                <button class='btn btn-sm btn-soft-warning btn-edit w-100'
                                    data-id='${data.id}'>
                                    <i class='ti ti-pencil me-1'></i>Edit
                                </button>
                                ${suspendBtn}
                                <button class='btn btn-sm btn-soft-danger btn-hapus w-100'
                                    data-id='${data.id}'
                                    data-name='${data.name}'>
                                    <i class='ti ti-trash me-1'></i>Hapus
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            scrollY: '500px',
            scrollCollapse: true,
            paging: true,
            pageLength: 10,
            lengthChange: false,
            searching: false,
            ordering: false,
            autoWidth: false,
            language: {
                emptyTable: "Tidak ada data pengguna",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pengguna",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 pengguna",
                paginate: {
                    next: "Next",
                    previous: "Prev"
                }
            }
        });
    },
    setEvents: function () {
        var self = this;

        self.data.searchButton.on('click', function () {
            self.data.table.ajax.reload();
        });

        self.data.searchInput.keyup(function (e) {
            if (e.keyCode === 13) {
                self.data.searchButton.click();
            }
        });

        $('.filter-status').on('click', function (e) {
            e.preventDefault();
            self.data.statusFilter = $(this).data('status');
            self.data.table.ajax.reload();
        });

        $('.filter-permission').on('click', function (e) {
            e.preventDefault();
            self.data.permissionFilter = $(this).data('permission');
            self.data.table.ajax.reload();
        });

        $(document).on('click', '.btn-edit', function () {
            var id = $(this).data('id');

            Swal.fire({
                title: 'Loading...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.get('/admin/pengguna/' + id + '/detail', function (user) {
                Swal.close();

                $('#edit-id').val(user.id);
                $('#edit-name').val(user.name);
                $('#edit-username').val(user.username || '');
                $('#edit-email').val(user.email);
                $('#edit-phone').val(user.phone || '');
                $('#edit-permission').val(user.permission);
                $('#edit-status').val(user.status);

                const avatarUrl = user.avatar && user.avatar !== 'default-avatar.png'
                    ? '/uploads/avatar/' + user.avatar
                    : '/uploads/avatar/default-avatar.png';
                $('#edit-avatar-preview').attr('src', avatarUrl);

                $('#modalEditUser').modal('show');
            }).fail(function () {
                Swal.fire('Error', 'Gagal memuat data user', 'error');
            });
        });

        $(document).on('click', '.btn-suspend', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');

            self.data.suspendUserId = id;
            $('#suspend-user-name').text(name);
            $('#suspend-duration').val('');
            $('#suspend-reason').val('');
            $('#suspend-reason-count').text('0');
            $('.suspend-preset').removeClass('active btn-secondary').addClass('btn-outline-secondary');
            $('#modalSuspendUser').modal('show');
        });

        $(document).on('click', '.suspend-preset', function () {
            var minutes = $(this).data('minutes');
            $('.suspend-preset').removeClass('active btn-secondary').addClass('btn-outline-secondary');
            $(this).removeClass('btn-outline-secondary').addClass('btn-secondary active');
            $('#suspend-duration').val(minutes);
        });

        $('#suspend-duration').on('input', function () {
            $('.suspend-preset').removeClass('active btn-secondary').addClass('btn-outline-secondary');
        });

        $('#suspend-reason').on('input', function () {
            $('#suspend-reason-count').text($(this).val().length);
        });

        $('#btn-confirm-suspend').on('click', function () {
            var duration = parseInt($('#suspend-duration').val());
            var reason = $('#suspend-reason').val().trim();

            if (!duration || duration < 1) {
                Swal.fire('Perhatian', 'Pilih atau masukkan durasi suspensi terlebih dahulu.', 'warning');
                return;
            }
            if (duration > 43200) {
                Swal.fire('Perhatian', 'Durasi maksimal adalah 43200 menit (30 hari).', 'warning');
                return;
            }

            $('#modalSuspendUser').modal('hide');

            Swal.fire({
                title: 'Memproses...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: '/admin/pengguna/suspend',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: {
                    id: self.data.suspendUserId,
                    duration: duration,
                    reason: reason,
                },
                success: function (res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function () {
                        self.data.table.ajax.reload(null, false);
                    });
                },
                error: function (xhr) {
                    var msg = 'Gagal mensuspend pengguna.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire('Gagal', msg, 'error');
                }
            });
        });

        $(document).on('click', '.btn-unsuspend', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');

            $.confirm({
                title: 'Cabut Suspensi',
                type: 'green',
                columnClass: 'medium',
                content: 'Cabut suspensi untuk pengguna <strong>' + name + '</strong>? Pengguna akan dapat login kembali.',
                buttons: {
                    cancel: {
                        text: 'Batal',
                        btnClass: 'btn-secondary',
                        keys: ['esc']
                    },
                    confirm: {
                        text: 'Ya, Cabut!',
                        btnClass: 'btn-success',
                        keys: ['enter'],
                        action: function () {
                            Swal.fire({
                                title: 'Memproses...',
                                allowOutsideClick: false,
                                didOpen: () => { Swal.showLoading(); }
                            });

                            $.ajax({
                                url: '/admin/pengguna/unsuspend',
                                type: 'POST',
                                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                data: { id: id },
                                success: function (res) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: res.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    }).then(function () {
                                        self.data.table.ajax.reload(null, false);
                                    });
                                },
                                error: function (xhr) {
                                    var msg = 'Gagal mencabut suspensi.';
                                    if (xhr.responseJSON && xhr.responseJSON.message) {
                                        msg = xhr.responseJSON.message;
                                    }
                                    Swal.fire('Gagal', msg, 'error');
                                }
                            });
                        }
                    }
                }
            });
        });

        $(document).on('click', '.btn-hapus', function () {
            var id = $(this).data('id');
            var name = $(this).data('name');

            $.confirm({
                title: 'Konfirmasi Hapus',
                type: 'red',
                columnClass: 'medium',
                content: 'Apakah Anda yakin ingin menghapus pengguna <strong>' + name + '</strong>? <br/><br/>' +
                    '<span class="text-danger">Data yang telah dihapus tidak dapat dikembalikan!</span>',
                buttons: {
                    cancel: {
                        text: 'Batal',
                        btnClass: 'btn-secondary',
                        keys: ['esc']
                    },
                    confirm: {
                        text: 'Ya, Hapus!',
                        btnClass: 'btn-danger',
                        keys: ['enter'],
                        action: function () {
                            $('#id-delete').val(id);
                            $('#form-delete').submit();
                        }
                    }
                }
            });
        });
    }
};

$(document).ready(function () {
    jQuery.index.init();
});
