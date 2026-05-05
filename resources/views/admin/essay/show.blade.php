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
                        <div class="border rounded p-3 bg-light">
                            {{ $pair['answer'] ?: '(Tidak ada jawaban)' }}
                        </div>
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
    </style>
@endpush
