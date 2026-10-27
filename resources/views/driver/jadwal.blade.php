@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">

  <div class="btn-group" role="group">
    <a href="?filter=today" class="btn btn-sm btn-outline-secondary {{ $filter==='today' ? 'active' : '' }}">Hari Ini</a>
    <a href="?filter=yesterday" class="btn btn-sm btn-outline-secondary {{ $filter==='yesterday' ? 'active' : '' }}">Kemarin</a>
    <a href="?filter=tomorrow" class="btn btn-sm btn-outline-secondary {{ $filter==='tomorrow' ? 'active' : '' }}">Besok</a>
    <a href="?filter=all" class="btn btn-sm btn-outline-secondary {{ $filter==='all' ? 'active' : '' }}">Semua</a>
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
                  <select class="form-select form-select-sm driver-status-select" aria-label="Pilih status" data-current-status="{{ $currentStatus }}">
                    <option value="Proses" {{ $currentStatus === 'Proses' ? 'selected' : '' }}>Proses</option>
                    <option value="Selesai" {{ $currentStatus === 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="Pending" {{ $currentStatus === 'Pending' ? 'selected' : '' }}>Pending</option>
                  </select>
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

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const applySelectedStatus = (form, selectedStatus) => {
      const statusInput = form.querySelector('[name="status"]');
      const noteTrigger = form.parentElement.querySelector('.driver-note-trigger');
      const noteInput = form.querySelector('[name="catatan"]');

      if (statusInput) statusInput.value = selectedStatus;

      if (noteTrigger) {
        const shouldShow = selectedStatus === 'Pending';
        noteTrigger.classList.toggle('d-none', !shouldShow);
        if (!shouldShow) {
          if (noteInput) noteInput.value = '';
        }
      }
    };

    document.querySelectorAll('.driver-status-select').forEach((select) => {
      const form = select.closest('.driver-schedule-form');
      const currentStatus = select.dataset.currentStatus || 'Terjadwal';
      const statusInput = form.querySelector('[name="status"]');
      if (statusInput) statusInput.value = currentStatus;

      select.addEventListener('change', function () {
        const selectedStatus = this.value;
        const previousValue = this.dataset.currentStatus || currentStatus;
        const noteInput = form.querySelector('[name="catatan"]');

        if (selectedStatus === 'Pending' && !(noteInput.value || '').trim()) {
          Swal.fire({
            title: 'Catatan Pending dibutuhkan',
            input: 'text',
            inputLabel: 'Tuliskan alasan status Pending',
            inputPlaceholder: 'Contoh: menunggu konfirmasi dari pusat',
            showCancelButton: true,
            confirmButtonText: 'Lanjutkan',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
              if (!value || !value.trim()) {
                return 'Catatan tidak boleh kosong';
              }
              return null;
            }
          }).then((result) => {
            if (result.isConfirmed) {
              if (noteInput) noteInput.value = result.value || '';
              applySelectedStatus(form, selectedStatus);
              const confirmText = selectedStatus === 'Pending'
                ? 'Status yang dipilih: <span class="badge bg-warning text-dark">Pending</span><br><br>Apakah Anda yakin ingin mengubah status jadwal ini menjadi Pending?'
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
              }).then((confirmResult) => {
                if (confirmResult.isConfirmed) {
                  form.submit();
                } else {
                  select.value = previousValue;
                  applySelectedStatus(form, previousValue);
                }
              });
            } else {
              select.value = previousValue;
              applySelectedStatus(form, previousValue);
            }
          });
          return;
        }

        const confirmText = selectedStatus === 'Pending'
          ? 'Status yang dipilih: <span class="badge bg-warning text-dark">Pending</span><br><br>Apakah Anda yakin ingin mengubah status jadwal ini menjadi Pending?'
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
            applySelectedStatus(form, selectedStatus);
            form.submit();
          } else {
            select.value = previousValue;
            applySelectedStatus(form, previousValue);
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

{{ $items->links() }}
@endsection
