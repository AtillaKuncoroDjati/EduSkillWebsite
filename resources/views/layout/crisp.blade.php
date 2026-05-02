@php
    $crispId = config('crisp.website_id');
    $crispEnabled = config('crisp.enabled') && !empty($crispId);
    $authUser = auth()->user();
    $showWidget = $crispEnabled && $authUser && $authUser->permission === 'user';
@endphp

@if ($showWidget)
<script>
    window.$crisp = window.$crisp || [];
    window.CRISP_WEBSITE_ID = @json($crispId);

    (function () {
        var currentUserId = @json((string) $authUser->id);
        var currentAppSessionId = @json((string) session()->getId());
        var storageUserKey = "eduskill_crisp_user_id";
        var storageSessionKey = "eduskill_crisp_app_session_id";
        var previousUserId = null;
        var previousSessionId = null;

        try {
            previousUserId = localStorage.getItem(storageUserKey);
            previousSessionId = localStorage.getItem(storageSessionKey);
        } catch (e) {
            previousUserId = null;
            previousSessionId = null;
        }

        var userSwitched = previousUserId && previousUserId !== currentUserId;
        var loginSessionChanged = previousSessionId && previousSessionId !== currentAppSessionId;

        if (userSwitched || loginSessionChanged) {
            $crisp.push(["do", "session:reset"]);
        }

        try {
            localStorage.setItem(storageUserKey, currentUserId);
            localStorage.setItem(storageSessionKey, currentAppSessionId);
        } catch (e) {
            // Abaikan jika localStorage tidak tersedia.
        }

        /* ── Identitas pengguna ── */
        $crisp.push(["set", "user:email", [@json($authUser->email)]]);
        $crisp.push(["set", "user:nickname", [@json($authUser->name)]]);
        @if ($authUser->avatar && $authUser->avatar !== 'default-avatar.png')
        $crisp.push(["set", "user:avatar", [@json(asset('uploads/avatar/' . $authUser->avatar))]]);
        @endif

        /* ── Data sesi tambahan (terlihat di dashboard Crisp admin) ── */
        $crisp.push(["set", "session:data", [[
            ["user_id", @json($authUser->id)],
            ["platform", "EduSkill"],
            ["role", "user"]
        ]]]);
    })();

    /* ── Template "Tinggalkan Pesan" ───────────────────────────────────
     * Jika chat dibuka dan admin belum membalas dalam 8 detik,
     * pre-fill input dengan template tinggalkan pesan agar user
     * tetap bisa mengirim pesan meski admin sedang offline.
     * ──────────────────────────────────────────────────────────────── */
    (function () {
        var leaveTimer         = null;
        var operatorHasReplied = false;

        var LEAVE_MSG_TEMPLATE =
            "Halo Admin EduSkill! 👋\n\n" +
            "Saya menyadari Anda sedang tidak tersedia saat ini.\n" +
            "Berikut pesan yang ingin saya sampaikan:\n\n" +
            "[Tulis pertanyaan atau pesan Anda di sini]\n\n" +
            "Nama  : " + @json($authUser->name) + "\n" +
            "Email : " + @json($authUser->email) + "\n\n" +
            "Mohon balas secepatnya. Terima kasih! 🙏";

        /* Batalkan timer jika admin membalas */
        $crisp.push(["on", "message:received", function () {
            operatorHasReplied = true;
            if (leaveTimer) clearTimeout(leaveTimer);
        }]);

        /* Mulai timer ketika chat dibuka */
        $crisp.push(["on", "chat:opened", function () {
            if (operatorHasReplied) return;

            leaveTimer = setTimeout(function () {
                /* Hanya isi template jika kolom input masih kosong */
                if ($crisp.get("message:text") === "") {
                    $crisp.push(["set", "message:text", [LEAVE_MSG_TEMPLATE]]);
                }
            }, 8000); /* 8 detik setelah chat dibuka */
        }]);

        /* Bersihkan timer ketika chat ditutup */
        $crisp.push(["on", "chat:closed", function () {
            if (leaveTimer) clearTimeout(leaveTimer);
        }]);
    })();

    /* ── Muat skrip Crisp ── */
    (function () {
        var d = document, s = d.createElement("script");
        s.src   = "https://client.crisp.chat/l.js";
        s.async = 1;
        d.getElementsByTagName("head")[0].appendChild(s);
    })();
</script>
@endif
