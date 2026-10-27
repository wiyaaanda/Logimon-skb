@extends('layouts.app')
@section('content')
<div class="driver-status-page">
  @if(session('success'))
    <div class="alert alert-success mb-3">{{ session('success') }}</div>
  @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Jadwal Saya</h4>
    <div class="btn-group" role="group">
      <a href="?filter=today" class="btn btn-sm btn-outline-secondary {{ $filter ?? 'all' === 'today' ? 'active' : '' }}">Hari Ini</a>
      <a href="?filter=yesterday" class="btn btn-sm btn-outline-secondary {{ ($filter ?? 'all') === 'yesterday' ? 'active' : '' }}">Kemarin</a>
      <a href="?filter=tomorrow" class="btn btn-sm btn-outline-secondary {{ ($filter ?? 'all') === 'tomorrow' ? 'active' : '' }}">Besok</a>
      <a href="?filter=all" class="btn btn-sm btn-outline-secondary {{ ($filter ?? 'all') === 'all' ? 'active' : '' }}">Semua</a>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-hover align-middle">
      <thead>
        <tr>
          <th>#</th>
          <th>Tanggal</th>
          <th>Jam Berangkat</th>
          <th>Tujuan</th>
          <th>Material</th>
          <th>No Rec</th>
          <th>Volume (kg)</th>
          <th>Timbangan CV SR</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $it)
          @php
            $currentStatus = $it->status ?? 'Terjadwal';
            $currentNote = $it->pengirimans?->first()?->catatan ?? '';
          @endphp
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $it->tanggal }}</td>
            <td>{{ $it->jam_berangkat }}</td>
            <td>{{ $it->tujuan?->nama_tujuan }}</td>
            <td>{{ $it->material?->nama_material }}</td>
            <td>{{ $it->no_rec }}</td>
            <td>{{ number_format($it->vol_kg, 0, ',', '.') }}</td>
            <td>{{ number_format($it->timbangan_cv_sr, 0, ',', '.') }}</td>
            <td>
              <div class="driver-action-stack">
                <form method="POST" action="{{ route('driver.status.update') }}" class="driver-schedule-form d-inline-block m-0">
                  @csrf
                  <input type="hidden" name="id_jadwal" value="{{ $it->id_jadwal }}">
                  <input type="hidden" name="status" value="{{ $currentStatus }}">
                  <input type="hidden" name="catatan" value="{{ $currentNote }}">
                  <div class="driver-inline-action-row">
                    <div class="driver-status-toggle-group" role="group" aria-label="Pilih status">
                      <button type="button" class="driver-status-chip {{ $currentStatus === 'Proses' ? 'selected' : '' }}" data-status="Proses">Proses</button>
                      <button type="button" class="driver-status-chip {{ $currentStatus === 'Selesai' ? 'selected' : '' }}" data-status="Selesai">Selesai</button>
                      <button type="button" class="driver-status-chip {{ $currentStatus === 'Pending' ? 'selected' : '' }}" data-status="Pending">Pending</button>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary driver-update-status-btn">Update</button>
                  </div>
                </form>

                <button type="button" class="btn btn-sm btn-warning driver-note-trigger {{ $currentStatus === 'Pending' ? '' : 'd-none' }}" data-note="{{ e($currentNote) }}">Catatan</button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="text-center text-muted py-4">Tidak ada jadwal untuk filter ini.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{ $items->links() }}
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const syncPendingNoteState = (form, selectedStatus) => {
      const statusInput = form.querySelector('[name="status"]');
      const noteTrigger = form.parentElement.querySelector('.driver-note-trigger');
      const noteInput = form.querySelector('[name="catatan"]');

      if (statusInput) statusInput.value = selectedStatus;

      if (noteTrigger) {
        const shouldShow = selectedStatus === 'Pending';
        noteTrigger.classList.toggle('d-none', !shouldShow);
        if (!shouldShow) {
          noteInput.value = '';
        }
      }
    };

    document.querySelectorAll('.driver-status-chip').forEach((chip) => {
      const form = chip.closest('.driver-schedule-form');
      const selectedStatus = chip.dataset.status;

      chip.addEventListener('click', function () {
        const form = this.closest('.driver-schedule-form');
        const chips = form.querySelectorAll('.driver-status-chip');

        chips.forEach((item) => item.classList.toggle('selected', item === this));
        syncPendingNoteState(form, this.dataset.status);
      });

      if (form) {
        const statusInput = form.querySelector('[name="status"]');
        if (statusInput && statusInput.value === selectedStatus) {
          chip.classList.add('selected');
        }
      }
    });

    document.querySelectorAll('.driver-update-status-btn').forEach((button) => {
      button.addEventListener('click', function () {
        const form = this.closest('.driver-schedule-form');
        const statusInput = form.querySelector('[name="status"]');
        const noteInput = form.querySelector('[name="catatan"]');
        const selectedStatus = statusInput.value;

        if (selectedStatus === 'Pending' && !(noteInput.value || '').trim()) {
          Swal.fire({
            icon: 'warning',
            title: 'Status Pending perlu catatan',
            text: 'Sebelum update, isi alasan status Pending terlebih dahulu.',
            confirmButtonText: 'Isi Catatan',
            reverseButtons: true
          }).then((result) => {
            if (result.isConfirmed) {
              const noteButton = form.parentElement.querySelector('.driver-note-trigger');
              if (noteButton) {
                noteButton.click();
              }
            }
          });
          return;
        }

        const confirmText = selectedStatus === 'Pending'
          ? 'Status yang dipilih: <span class="badge bg-warning text-dark">Pending</span><br><br>Apakah Anda yakin ingin update jadwal ini dengan status Pending? Pastikan catatan alasan sudah benar.'
          : 'Status yang dipilih: <span class="badge bg-primary">' + selectedStatus + '</span><br><br>Apakah Anda yakin ingin mengubah status jadwal ini?';

        Swal.fire({
          title: 'Konfirmasi update status',
          html: '<div class="swal-status-confirmation">' + confirmText + '</div>',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Ya, Update',
          cancelButtonText: 'Batal',
          reverseButtons: true,
          customClass: {
            confirmButton: 'btn btn-primary ms-2',
            cancelButton: 'btn btn-outline-secondary'
          },
          buttonsStyling: false
        }).then((result) => {
          if (result.isConfirmed) {
            form.submit();
          }
        });
      });
    });

    document.querySelectorAll('.driver-note-trigger').forEach((button) => {
      button.addEventListener('click', function () {
        const form = this.closest('td')?.querySelector('.driver-schedule-form');
        const noteInput = form?.querySelector('[name="catatan"]');
        const currentNote = noteInput?.value || this.dataset.note || '';

        Swal.fire({
          title: 'Catatan Pending',
          input: 'text',
          inputLabel: 'Tuliskan alasan status Pending',
          inputValue: currentNote,
          showCancelButton: true,
          confirmButtonText: 'Simpan',
          cancelButtonText: 'Batal',
          inputPlaceholder: 'Contoh: menunggu konfirmasi dari pusat'
        }).then((result) => {
          if (result.isConfirmed) {
            if (noteInput) noteInput.value = result.value || '';
          }
        });
      });
    });
  });
</script>
@endsection
