@extends('template', ['title' => 'Nilai Esai'])

@section('content')
    <div class="page-title-head d-flex align-items-center justify-content-between mb-3">
        <h4 class="fs-18 text-uppercase fw-bold mb-0">Penilaian Esai</h4>
        <a href="{{ route('admin.essay.index') }}" class="btn btn-soft-danger">
            <i class="ti ti-arrow-left me-1"></i> Kembali
        </a>
    </div>

    {{-- Attempt Info --}}
    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small mb-1">Pengguna</div>
                    <div class="fw-bold">{{ $attempt->user->name }}</div>
                    <div class="text-muted small">{{ $attempt->user->email }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small mb-1">Kuis</div>
                    <div class="fw-bold">{{ $attempt->content->title }}</div>
                    <div class="text-muted small">{{ $attempt->content->module->kursus->judul ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small mb-1">Status</div>
                    @if($attempt->grading_status === 'pending_review')
                        <span class="badge bg-warning text-dark fs-13"><i class="ti ti-clock me-1"></i>Menunggu Penilaian</span>
                    @else
                        <span class="badge bg-success fs-13"><i class="ti ti-check me-1"></i>Sudah Dinilai</span>
                        @if($attempt->score !== null)
                            <div class="mt-1 fw-bold {{ $attempt->score >= 70 ? 'text-success' : 'text-danger' }}">
                                Nilai: {{ $attempt->score }} / 100 — {{ $attempt->is_passed ? 'Lulus' : 'Tidak Lulus' }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── Keystroke Dynamics Analytics ───────────────────────────────────── --}}
    @php
        $ksFlag      = $attempt->keystroke_flag ?? null;
        $ksScore     = $attempt->keystroke_anomaly_score !== null ? (float) $attempt->keystroke_anomaly_score : null;
        $ksPaste     = $keystrokeData['paste_indicator'] ?? null;
        $ksPasteDetected = $ksPaste && !empty($ksPaste['detected']);
        $ksBarColor  = $ksScore === null ? 'secondary' : ($ksScore >= 70 ? 'danger' : ($ksScore >= 40 ? 'warning' : 'success'));
        $ksCardBorder = $ksPasteDetected ? 'border-danger' : match($ksFlag) { 'suspect' => 'border-danger', 'caution' => 'border-warning', default => '' };
        $ksDwellStr  = isset($keystrokeData['mean_dwell'])    ? $keystrokeData['mean_dwell']    . ' ms'  : '-';
        $ksFlightStr = isset($keystrokeData['mean_flight'])   ? $keystrokeData['mean_flight']   . ' ms'  : '-';
        $ksSpeedStr  = isset($keystrokeData['mean_speed_cps'])? $keystrokeData['mean_speed_cps']. ' CPS' : '-';
        $ksErrorRate = isset($keystrokeData['error_rate'])    ? round($keystrokeData['error_rate'] * 100, 1) . '%' : '-';
        $ksTotalKeys = $keystrokeData['total_keystrokes'] ?? '-';
        $ksAnswerLen = $keystrokeData['total_answer_chars'] ?? null;
        $ksPasteCount = $keystrokeData['paste_count'] ?? 0;
        $ksPastedChars = $keystrokeData['pasted_chars'] ?? 0;
        $ksDevice    = $keystrokeData['device_type'] ?? '-';
        $ksScoreVal  = $ksScore ?? 0;
    @endphp

    <div class="card mb-4 border {{ $ksCardBorder }}" style="border-width:2px!important;">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="mb-0"><i class="ti ti-keyboard me-2"></i>Keystroke Dynamics — Verifikasi Identitas</h6>
            @if ($ksPasteDetected)
                <span class="badge bg-danger fs-12"><i class="ti ti-clipboard-x me-1"></i>COPY-PASTE TERDETEKSI</span>
            @elseif ($ksFlag === 'suspect')
                <span class="badge bg-danger fs-12"><i class="ti ti-alert-triangle me-1"></i>SUSPECT — Pola Sangat Berbeda</span>
            @elseif ($ksFlag === 'caution')
                <span class="badge bg-warning text-dark fs-12"><i class="ti ti-eye me-1"></i>PERLU PERHATIAN</span>
            @elseif ($ksFlag === 'normal')
                <span class="badge bg-success fs-12"><i class="ti ti-check me-1"></i>NORMAL</span>
            @elseif ($keystrokeData && !$keystrokeBaseline)
                <span class="badge bg-secondary fs-12"><i class="ti ti-info-circle me-1"></i>Data Direkam — Baseline Belum Cukup</span>
            @else
                <span class="badge bg-light text-muted fs-12">Tidak Ada Data</span>
            @endif
        </div>
        <div class="card-body">
            {{-- Penjelasan singkat untuk admin --}}
            <div class="alert alert-light border mb-3 py-2 px-3 small">
                <div class="d-flex align-items-start gap-2">
                    <i class="ti ti-info-circle text-primary mt-1"></i>
                    <div class="flex-grow-1">
                        <strong class="d-block mb-1">Cara Kerja Singkat</strong>
                        <p class="mb-1">
                            Sistem memantau <em>cara</em> siswa mengisi jawaban — bukan isi jawabannya. Ada
                            <strong>2 lapis deteksi</strong>:
                        </p>
                        <ol class="mb-2 ps-3">
                            <li class="mb-1">
                                <strong class="text-danger">Deteksi Copy-Paste (instan)</strong> — sistem
                                menangkap aksi <em>paste</em> (Ctrl+V, klik kanan→tempel, drag teks, dll.).
                                Jika terdeteksi, sesi <strong>langsung ditandai SUSPECT</strong> tanpa perlu
                                menunggu baseline. Juga aktif jika jawaban panjang tetapi total ketukan
                                keyboard sangat sedikit (mustahil mengetik manual).
                            </li>
                            <li class="mb-1">
                                <strong class="text-primary">Verifikasi Identitas (biometrik)</strong> — merekam
                                ritme ketik (dwell time, flight time, kecepatan, error rate) sebagai
                                "sidik jari pengetikan". Setelah siswa menyelesaikan ≥2 sesi, sistem
                                membentuk <strong>baseline</strong> dan membandingkan setiap sesi baru dengan
                                pola normal siswa tersebut.
                            </li>
                        </ol>
                        <p class="mb-1"><strong>Status yang mungkin muncul:</strong></p>
                        <ul class="mb-1 ps-3">
                            <li><span class="text-danger fw-bold">COPY-PASTE TERDETEKSI</span> — siswa menempelkan teks, bukan diketik. <strong>Bukti kuat kecurangan.</strong></li>
                            <li><span class="text-success fw-bold">NORMAL</span> — pola ketik konsisten dengan baseline, kemungkinan murni dikerjakan sendiri.</li>
                            <li><span class="text-warning fw-bold">PERLU PERHATIAN</span> — ada perbedaan dari biasanya; mungkin lelah, terburu-buru, atau awal indikasi orang lain.</li>
                            <li><span class="text-danger fw-bold">SUSPECT</span> — pola sangat berbeda dari biasanya, kemungkinan besar <strong>dikerjakan orang lain (joki)</strong>.</li>
                            <li><span class="text-secondary fw-bold">DATA DIREKAM — BASELINE BELUM CUKUP</span> — sesi terekam, tapi siswa belum punya ≥2 sesi untuk membentuk baseline. Verifikasi identitas akan aktif di sesi berikutnya.</li>
                        </ul>
                        <p class="mb-0 text-muted">
                            <i class="ti ti-bulb me-1"></i>
                            Status <strong>COPY-PASTE TERDETEKSI</strong> adalah indikator paling kuat — siswa secara teknis terbukti tidak mengetik jawaban. Untuk status lain, gunakan sebagai <em>petunjuk tambahan</em> dan cek juga isi jawaban serta riwayat pelanggaran integrity mode.
                        </p>
                    </div>
                </div>
            </div>

            @if ($ksPasteDetected)
                <div class="alert alert-danger d-flex align-items-start gap-2 mb-3">
                    <i class="ti ti-clipboard-x fs-4"></i>
                    <div class="flex-grow-1 small">
                        <strong class="d-block mb-1">⚠️ INDIKASI COPY-PASTE TERDETEKSI</strong>
                        @if ($ksPaste['reason'] === 'paste_event')
                            <p class="mb-1">Siswa <strong>menempelkan teks ({{ $ksPasteCount }}× paste, total {{ $ksPastedChars }} karakter)</strong> ke dalam jawaban — bukan diketik manual.</p>
                        @elseif ($ksPaste['reason'] === 'low_keystroke_ratio')
                            <p class="mb-1">Jawaban siswa berisi <strong>{{ $ksAnswerLen ?? '?' }} karakter</strong> tetapi hanya <strong>{{ $ksTotalKeys }} ketukan keyboard</strong> (rasio {{ $ksPaste['ratio'] ?? '-' }}). Tidak mungkin mengetik jawaban sepanjang itu dengan ketukan sesedikit ini.</p>
                        @endif
                        <p class="mb-0 text-dark"><strong>Rekomendasi:</strong> Cek sumber jawaban siswa (misalnya cocokkan dengan materi/internet). Pertimbangkan untuk meminta siswa mengulang kuis dengan pengawasan, atau berikan nilai sesuai kebijakan akademik.</p>
                    </div>
                </div>
            @endif

            @if (!$keystrokeData)
                <p class="text-muted mb-0 small">Data keystroke tidak tersedia untuk sesi ini.</p>
            @else
                {{-- Anomaly Score Bar --}}
                @if ($ksScore !== null)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="fw-bold">Anomaly Score</small>
                            <small class="fw-bold text-{{ $ksBarColor }}">{{ $ksScore }} / 100</small>
                        </div>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-{{ $ksBarColor }}" id="ks-anomaly-bar" role="progressbar"></div>
                        </div>
                        <small class="text-muted">0–39 Normal &nbsp;|&nbsp; 40–69 Perlu Perhatian &nbsp;|&nbsp; 70–100 Suspect</small>
                    </div>
                @endif

                {{-- Raw Session Stats --}}
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center">
                            <div class="fw-bold">{{ $ksDwellStr }}</div>
                            <small class="text-muted">Dwell Time</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center">
                            <div class="fw-bold">{{ $ksFlightStr }}</div>
                            <small class="text-muted">Flight Time</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center">
                            <div class="fw-bold">{{ $ksSpeedStr }}</div>
                            <small class="text-muted">Kecepatan Ketik</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center">
                            <div class="fw-bold">{{ $ksErrorRate }}</div>
                            <small class="text-muted">Error Rate</small>
                        </div>
                    </div>
                </div>

                {{-- Comparison Table --}}
                @if (!empty($keystrokeTable))
                    <h6 class="mb-2 small fw-bold text-muted">PERBANDINGAN vs BASELINE ({{ $keystrokeBaseline->sample_sessions }} sesi)</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Metrik</th>
                                    <th class="text-center">Baseline</th>
                                    <th class="text-center">Sesi Ini</th>
                                    <th class="text-center">Z-Score</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($keystrokeTable as $row)
                                    @php
                                        $zVal     = $row['z_score'];
                                        $trClass  = $zVal === null ? '' : ($zVal >= 2.5 ? 'table-danger' : ($zVal >= 1.5 ? 'table-warning' : ''));
                                    @endphp
                                    <tr class="{{ $trClass }}">
                                        <td>{{ $row['label'] }} <span class="text-muted">({{ $row['unit'] }})</span></td>
                                        <td class="text-center">{{ $row['baseline'] }}</td>
                                        <td class="text-center">{{ $row['session'] }}</td>
                                        <td class="text-center">{{ $zVal ?? '-' }}</td>
                                        <td class="text-center">
                                            @if ($zVal === null)
                                                <span class="text-muted">-</span>
                                            @elseif ($zVal >= 2.5)
                                                <span class="badge bg-danger">Anomali</span>
                                            @elseif ($zVal >= 1.5)
                                                <span class="badge bg-warning text-dark">Waspada</span>
                                            @else
                                                <span class="badge bg-success">Normal</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif (!$keystrokeBaseline)
                    <div class="alert alert-info mb-0 py-2 small">
                        <i class="ti ti-info-circle me-1"></i>
                        Data ketikan sesi ini sudah direkam. Perbandingan tersedia setelah siswa menyelesaikan minimal <strong>2 sesi</strong> esai.
                        <br>
                        <strong>Total ketukan:</strong> {{ $ksTotalKeys }}
                        @if ($ksAnswerLen !== null)
                            | <strong>Panjang jawaban:</strong> {{ $ksAnswerLen }} karakter
                        @endif
                        @if ($ksPasteCount > 0)
                            | <strong class="text-danger">Paste:</strong> {{ $ksPasteCount }}× ({{ $ksPastedChars }} karakter)
                        @endif
                        | <strong>Device:</strong> {{ $ksDevice }}
                    </div>
                @endif
            @endif
        </div>
    </div>

    <form method="POST" action="{{ route('admin.essay.grade', $attempt->id) }}">
        @csrf

        @foreach($pairs as $pair)
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <strong>Pertanyaan {{ $pair['index'] + 1 }}</strong>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted small mb-1">Pertanyaan</div>
                        <p class="mb-0">{{ $pair['question'] }}</p>
                    </div>

                    <div class="mb-3">
                        <div class="text-muted small mb-1">Jawaban Siswa</div>
                        <textarea class="form-control bg-light admin-answer-box" rows="6" readonly
                            aria-label="Jawaban siswa">{{ $pair['answer'] ?: '(Tidak ada jawaban)' }}</textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Nilai (0–100)</label>
                            <input type="number" name="scores[{{ $pair['index'] }}]"
                                class="form-control"
                                min="0" max="100"
                                value="{{ $pair['score'] ?? '' }}"
                                placeholder="0–100" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label fw-bold">Feedback / Catatan</label>
                            <textarea name="feedbacks[{{ $pair['index'] }}]"
                                class="form-control" rows="2"
                                placeholder="Tulis feedback untuk jawaban ini...">{{ $pair['feedback'] ?? '' }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="card mb-4">
            <div class="card-body">
                <label class="form-label fw-bold">Catatan Umum Admin (opsional)</label>
                <textarea name="admin_notes" class="form-control" rows="3"
                    placeholder="Catatan umum untuk siswa...">{{ $attempt->admin_notes }}</textarea>
            </div>
        </div>

        <div class="d-flex gap-2 justify-content-end">
            <a href="{{ route('admin.essay.index') }}" class="btn btn-soft-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="ti ti-device-floppy me-1"></i> Simpan Penilaian
            </button>
        </div>
    </form>
@endsection

@push('styles')
    <style>
        .fs-13 { font-size: 13px; }
        .fs-12 { font-size: 12px; }
        .admin-answer-box {
            min-height: 150px;
            resize: vertical;
            white-space: pre-wrap;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            var bar = document.getElementById('ks-anomaly-bar');
            if (bar) bar.style.width = '{{ $ksScoreVal }}%';
        })();
    </script>
@endpush
