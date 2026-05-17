/**
 * Admin Notifications — polling notifikasi untuk admin EduSkill.
 * Memberi tahu admin saat ada esai menunggu penilaian & pelanggaran integritas kuis.
 * Mekanisme: polling berkala (tanpa websocket), dengan suara + popup yang bisa dimatikan.
 */
(function () {
    'use strict';

    var cfg = window.AdminNotifConfig;
    if (!cfg) return;

    // ── Preferensi pengguna (disimpan di localStorage) ──────────────────────
    function pref(key, def) {
        var v = localStorage.getItem(key);
        return v === null ? def : v === '1';
    }
    function setPref(key, val) {
        localStorage.setItem(key, val ? '1' : '0');
    }

    var soundEnabled = pref('adminNotifSound', true);
    var popupEnabled = pref('adminNotifPopup', true);

    // ── Audio ───────────────────────────────────────────────────────────────
    var audioAlarm = new Audio(cfg.soundAlarm);  // alarm.mp3 — untuk pelanggaran
    var audioNilai = new Audio(cfg.soundNilai);  // notification_nilai.mp3 — untuk esai
    audioAlarm.preload = 'auto';
    audioNilai.preload = 'auto';

    // Browser memblokir suara otomatis sampai ada interaksi pengguna.
    // Saat admin pertama kali klik / menekan tombol di mana saja, "bangunkan"
    // kedua audio (putar lalu langsung jeda) agar siap dibunyikan oleh poller.
    var audioUnlocked = false;
    function unlockAudio() {
        if (audioUnlocked) return;
        audioUnlocked = true;
        [audioAlarm, audioNilai].forEach(function (a) {
            var p = a.play();
            if (p && p.then) {
                p.then(function () { a.pause(); a.currentTime = 0; })
                 .catch(function () {});
            }
        });
    }
    document.addEventListener('click', unlockAudio, { once: true });
    document.addEventListener('keydown', unlockAudio, { once: true });

    function playSound(type) {
        if (!soundEnabled) return;
        var a = (type === 'integrity_violation') ? audioAlarm : audioNilai;
        try {
            a.currentTime = 0;
            // Sebagian browser memblokir autoplay sebelum ada interaksi — abaikan jika gagal
            var p = a.play();
            if (p && p.catch) p.catch(function () {});
        } catch (e) { /* abaikan */ }
    }

    // ── Toast popup ─────────────────────────────────────────────────────────
    function ensureToastContainer() {
        var c = document.getElementById('admin-notif-toasts');
        if (!c) {
            c = document.createElement('div');
            c.id = 'admin-notif-toasts';
            c.style.cssText = 'position:fixed;top:80px;right:20px;z-index:1080;width:330px;max-width:90vw;';
            document.body.appendChild(c);
        }
        return c;
    }

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : s;
        return d.innerHTML;
    }

    function showToast(item) {
        if (!popupEnabled) return;
        var c = ensureToastContainer();
        var isViolation = item.type === 'integrity_violation';
        var border = isViolation ? '#dc3545' : '#198754';
        var icon = isViolation ? 'ti-alert-triangle text-danger' : 'ti-clipboard-check text-success';

        var el = document.createElement('div');
        el.className = 'card shadow-sm mb-2';
        el.style.cssText = 'border-left:4px solid ' + border + ';cursor:pointer;';
        el.innerHTML =
            '<div class="card-body p-2 d-flex align-items-start">' +
            '<i class="ti ' + icon + ' fs-3 me-2"></i>' +
            '<div class="flex-grow-1">' +
            '<strong class="d-block small">' + escapeHtml(item.title) + '</strong>' +
            '<span class="small text-muted">' + escapeHtml(item.message) + '</span>' +
            '</div>' +
            '<button type="button" class="btn-close ms-1" aria-label="Tutup"></button>' +
            '</div>';

        el.querySelector('.btn-close').addEventListener('click', function (e) {
            e.stopPropagation();
            removeToast(el);
        });
        el.addEventListener('click', function () {
            if (item.link) {
                markRead(item.id, function () { window.location.href = item.link; });
            }
        });

        c.appendChild(el);
        setTimeout(function () { removeToast(el); }, 8000);
    }

    function removeToast(el) {
        if (el && el.parentNode) el.parentNode.removeChild(el);
    }

    // ── Render daftar di dropdown lonceng ───────────────────────────────────
    function renderList(items) {
        var list = document.getElementById('admin-notif-list');
        var empty = document.getElementById('admin-notif-empty');
        if (!list) return;

        list.querySelectorAll('.admin-notif-item').forEach(function (n) { n.remove(); });

        if (!items.length) {
            if (empty) empty.classList.remove('d-none');
            return;
        }
        if (empty) empty.classList.add('d-none');

        items.forEach(function (item) {
            var isViolation = item.type === 'integrity_violation';
            var icon = isViolation ? 'ti-alert-triangle text-danger' : 'ti-clipboard-check text-success';

            var a = document.createElement('a');
            a.href = item.link || '#';
            a.className = 'admin-notif-item dropdown-item d-flex align-items-start py-2 ' +
                (item.is_read ? '' : 'bg-light');
            a.style.whiteSpace = 'normal';
            a.innerHTML =
                '<i class="ti ' + icon + ' fs-4 me-2"></i>' +
                '<div class="flex-grow-1">' +
                '<strong class="d-block small">' + escapeHtml(item.title) +
                (item.is_read ? '' : ' <span class="badge bg-danger" style="font-size:8px;">baru</span>') +
                '</strong>' +
                '<span class="small text-muted d-block">' + escapeHtml(item.message) + '</span>' +
                '<span class="small text-muted">' + escapeHtml(item.time) + '</span>' +
                '</div>';

            a.addEventListener('click', function (e) {
                e.preventDefault();
                markRead(item.id, function () {
                    if (item.link) window.location.href = item.link;
                });
            });
            list.appendChild(a);
        });
    }

    function updateBadge(count) {
        var badge = document.getElementById('admin-notif-badge');
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }

    // ── AJAX ────────────────────────────────────────────────────────────────
    function markRead(id, done) {
        fetch(cfg.readUrlBase + '/' + id + '/read', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' }
        }).then(function () { if (done) done(); })
          .catch(function () { if (done) done(); });
    }

    function markAllRead() {
        fetch(cfg.readAllUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': cfg.csrf, 'Accept': 'application/json' }
        }).then(function () { poll(); });
    }

    var announced = {};   // signature notifikasi yang sudah "diumumkan" (suara/popup)
    var firstPoll = true;

    function poll() {
        fetch(cfg.pollUrl, {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function (data) {
                var items = data.items || [];
                renderList(items);
                updateBadge(data.unread_count || 0);

                // Notifikasi "baru" dideteksi lewat signature (id + isi pesan),
                // bukan timestamp — lebih andal saat pesan diperbarui.
                var fresh = [];
                items.forEach(function (it) {
                    var sig = it.id + '||' + it.message;
                    if (!announced[sig]) {
                        announced[sig] = true;
                        // Saat polling pertama: catat tanpa membunyikan notif lama
                        if (!firstPoll && !it.is_read) fresh.push(it);
                    }
                });
                firstPoll = false;

                if (fresh.length) {
                    var hasViolation = fresh.some(function (it) {
                        return it.type === 'integrity_violation';
                    });
                    playSound(hasViolation ? 'integrity_violation' : 'essay_submitted');
                    fresh.slice(0, 3).forEach(showToast);
                }
            })
            .catch(function (e) {
                console.warn('[admin-notif] polling gagal:', e.message);
            });
    }

    // ── Inisialisasi ────────────────────────────────────────────────────────
    function init() {
        var soundToggle = document.getElementById('admin-notif-sound-toggle');
        var popupToggle = document.getElementById('admin-notif-popup-toggle');
        var readAllBtn = document.getElementById('admin-notif-readall');

        if (soundToggle) {
            soundToggle.checked = soundEnabled;
            soundToggle.addEventListener('change', function () {
                soundEnabled = soundToggle.checked;
                setPref('adminNotifSound', soundEnabled);
            });
        }
        if (popupToggle) {
            popupToggle.checked = popupEnabled;
            popupToggle.addEventListener('change', function () {
                popupEnabled = popupToggle.checked;
                setPref('adminNotifPopup', popupEnabled);
            });
        }
        if (readAllBtn) {
            readAllBtn.addEventListener('click', function (e) {
                e.preventDefault();
                markAllRead();
            });
        }

        poll();
        setInterval(poll, cfg.pollInterval || 15000);
    }

    // Jalankan langsung jika DOM sudah siap; jika belum, tunggu DOMContentLoaded.
    // Pola ini mencegah init gagal saat skrip dimuat setelah DOM selesai diparse.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
