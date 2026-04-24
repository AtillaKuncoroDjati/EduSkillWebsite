jQuery.integrity = {
    data: {
        table: null,
        searchInput: null,
        searchButton: null,
        autoSubmittedFilter: 'all',
    },
    init: function () {
        var self = this;
        self.data.searchInput = $('#search-integrity');
        self.data.searchButton = $('.btn-search');

        self.initTable();
        self.setEvents();
    },
    initTable: function () {
        var self = this;

        self.data.table = $('#integrity-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/admin/kursus/integrity/request',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function (d) {
                    d.search = {
                        value: self.data.searchInput.val(),
                        regex: false
                    };
                    d.auto_submitted_filter = self.data.autoSubmittedFilter;
                    return d;
                }
            },
            columns: [
                {
                    data: null,
                    className: 'text-center',
                    render: function (data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'user',
                    render: function (user) {
                        return user ? `<strong>${user.name}</strong><br><small>${user.email}</small>` : '-';
                    }
                },
                {
                    data: 'content',
                    render: function (content) {
                        const kursus = content?.module?.kursus?.title || '-';
                        return kursus;
                    }
                },
                {
                    data: 'content',
                    render: function (content) {
                        return content?.title || 'Kuis';
                    }
                },
                {
                    data: 'violation_count',
                    className: 'text-center',
                    render: function (count) {
                        return `<span class="badge bg-warning text-dark">${count}</span>`;
                    }
                },
                {
                    data: 'is_auto_submitted',
                    className: 'text-center',
                    render: function (auto) {
                        return auto
                            ? '<span class="badge bg-danger">Ya</span>'
                            : '<span class="badge bg-success">Tidak</span>';
                    }
                },
                {
                    data: 'completed_at',
                    className: 'text-center',
                    render: function (date) {
                        if (!date) return '-';
                        return new Date(date).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'id',
                    className: 'text-center',
                    render: function (id) {
                        return `<button class="btn btn-sm btn-soft-primary btn-detail" data-id="${id}">Detail</button>`;
                    }
                }
            ],
            searching: false,
            ordering: false,
            pageLength: 10,
            lengthChange: false,
            language: {
                emptyTable: 'Belum ada pelanggaran integritas tercatat'
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

        $('.filter-auto').on('click', function (e) {
            e.preventDefault();
            self.data.autoSubmittedFilter = $(this).data('status');
            self.data.table.ajax.reload();
        });

        $(document).on('click', '.btn-detail', function () {
            const id = $(this).data('id');
            $('#integrity-detail-body').html('<div class="text-center py-4">Memuat data...</div>');
            $('#integrityDetailModal').modal('show');

            $.get('/admin/kursus/integrity/' + id + '/detail', function (attempt) {
                let html = `
                    <div class="mb-3">
                        <strong>User:</strong> ${attempt.user?.name || '-'}<br>
                        <strong>Kursus:</strong> ${attempt.content?.module?.kursus?.title || '-'}<br>
                        <strong>Kuis:</strong> ${attempt.content?.title || '-'}<br>
                        <strong>Total Pelanggaran:</strong> ${attempt.violation_count}<br>
                        <strong>Auto Submit:</strong> ${attempt.is_auto_submitted ? 'Ya' : 'Tidak'}
                    </div>
                    <hr>
                    <h6>Riwayat Event</h6>
                `;

                if (!attempt.integrity_events || attempt.integrity_events.length === 0) {
                    html += '<p class="text-muted">Tidak ada event.</p>';
                } else {
                    html += '<ul class="list-group">';
                    attempt.integrity_events.forEach(function (event) {
                        html += `
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <span>
                                    <strong>${event.event_type}</strong>
                                    <small class="text-muted d-block">${new Date(event.event_at).toLocaleString('id-ID')}</small>
                                </span>
                                <span class="badge ${event.is_auto_submitted ? 'bg-danger' : 'bg-warning text-dark'}">
                                    Pelanggaran ke-${event.violation_count}
                                </span>
                            </li>
                        `;
                    });
                    html += '</ul>';
                }

                $('#integrity-detail-body').html(html);
            }).fail(function () {
                $('#integrity-detail-body').html('<div class="alert alert-danger">Gagal memuat detail integritas.</div>');
            });
        });
    }
};

$(document).ready(function () {
    jQuery.integrity.init();
});
