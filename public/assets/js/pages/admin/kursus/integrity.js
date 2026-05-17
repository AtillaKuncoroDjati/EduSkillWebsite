jQuery.integrity = {
    data: {
        table: null,
        searchInput: null,
        searchButton: null,
        autoSubmittedFilter: 'all',
        riskFilter: 'all',
    },
    init: function () {
        var self = this;
        self.data.searchInput = $('#search-integrity');
        self.data.searchButton = $('.btn-search');

        self.initTable();
        self.setEvents();
    },

    // ── Renderers ──────────────────────────────────────────────────────────
    renderKeystrokeBadge: function (flag) {
        switch (flag) {
            case 'suspect':
                return '<span class="badge bg-danger">Suspect</span>';
            case 'caution':
                return '<span class="badge bg-warning text-dark">Caution</span>';
            case 'normal':
                return '<span class="badge bg-success">Normal</span>';
            default:
                return '<span class="text-muted">-</span>';
        }
    },
    renderRiskBadge: function (score) {
        if (score === null || score === undefined) {
            return '<span class="text-muted">-</span>';
        }
        var val = Math.round(score);
        if (val >= 70) {
            return '<span class="badge bg-danger">Tinggi (' + val + ')</span>';
        }
        if (val >= 40) {
            return '<span class="badge bg-warning text-dark">Sedang (' + val + ')</span>';
        }
        return '<span class="badge bg-success">Rendah (' + val + ')</span>';
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
                    d.risk_filter = self.data.riskFilter;
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
                        return content?.module?.kursus?.title || '-';
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
                    data: 'keystroke_flag',
                    className: 'text-center',
                    render: function (flag) {
                        return self.renderKeystrokeBadge(flag);
                    }
                },
                {
                    data: 'integrity_risk_score',
                    className: 'text-center',
                    render: function (score) {
                        return self.renderRiskBadge(score);
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

        $('.filter-risk').on('click', function (e) {
            e.preventDefault();
            self.data.riskFilter = $(this).data('risk');
            self.data.table.ajax.reload();
        });

        $(document).on('click', '.btn-detail', function () {
            const id = $(this).data('id');
            $('#integrity-detail-body').html('<div class="text-center py-4">Memuat data...</div>');
            $('#integrityDetailModal').modal('show');

            $.get('/admin/kursus/integrity/' + id + '/detail', function (attempt) {
                $('#integrity-detail-body').html(self.buildDetailHtml(attempt));
            }).fail(function () {
                $('#integrity-detail-body').html('<div class="alert alert-danger">Gagal memuat detail integritas.</div>');
            });
        });
    },

    // ── Detail modal builder ───────────────────────────────────────────────
    buildDetailHtml: function (attempt) {
        var self = this;

        let html = `
            <div class="mb-3">
                <strong>User:</strong> ${attempt.user?.name || '-'}<br>
                <strong>Kursus:</strong> ${attempt.content?.module?.kursus?.title || '-'}<br>
                <strong>Kuis:</strong> ${attempt.content?.title || '-'}<br>
                <strong>Total Pelanggaran:</strong> ${attempt.violation_count}<br>
                <strong>Auto Submit:</strong> ${attempt.is_auto_submitted ? 'Ya' : 'Tidak'}
                ${attempt.auto_submit_reason ? ' (' + attempt.auto_submit_reason + ')' : ''}
            </div>
        `;

        // Skor risiko terpadu
        html += '<div class="mb-3"><strong>Skor Risiko Integritas:</strong> '
            + self.renderRiskBadge(attempt.integrity_risk_score) + '</div>';

        // Analisis keystroke
        html += '<hr><h6>Analisis Keystroke (Biometrik Ketik)</h6>';
        if (!attempt.keystroke_flag && attempt.keystroke_anomaly_score === null) {
            html += '<p class="text-muted">Tidak ada data keystroke untuk attempt ini (kuis pilihan ganda atau tanpa esai).</p>';
        } else {
            const ks = attempt.keystroke_data || {};
            const paste = ks.paste_indicator || {};
            html += '<ul class="list-group mb-2">';
            html += `<li class="list-group-item d-flex justify-content-between">
                        <span>Status Keystroke</span> ${self.renderKeystrokeBadge(attempt.keystroke_flag)}</li>`;
            html += `<li class="list-group-item d-flex justify-content-between">
                        <span>Skor Anomali</span>
                        <span>${attempt.keystroke_anomaly_score !== null ? Math.round(attempt.keystroke_anomaly_score) + '/100' : '-'}</span></li>`;
            if (ks.baseline_source) {
                html += `<li class="list-group-item d-flex justify-content-between">
                            <span>Sumber Baseline</span>
                            <span>${ks.baseline_source === 'global' ? 'Global (cold-start)' : 'Personal'}</span></li>`;
            }
            if (paste.detected) {
                html += `<li class="list-group-item list-group-item-danger">
                            <i class="ti ti-clipboard-x me-1"></i>
                            <strong>Copy-paste terdeteksi</strong> — alasan: ${paste.reason || '-'}
                            ${paste.pasted_chars ? ' (' + paste.pasted_chars + ' karakter di-paste)' : ''}
                            ${paste.ratio !== undefined ? ' (rasio ketukan ' + paste.ratio + ')' : ''}</li>`;
            }
            html += '</ul>';
        }

        // Riwayat event pelanggaran
        html += '<hr><h6>Riwayat Event Pelanggaran</h6>';
        if (!attempt.integrity_events || attempt.integrity_events.length === 0) {
            html += '<p class="text-muted">Tidak ada event pelanggaran perilaku.</p>';
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

        return html;
    }
};

$(document).ready(function () {
    jQuery.integrity.init();
});
