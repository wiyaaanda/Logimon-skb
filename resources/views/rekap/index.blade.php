@extends('layouts.app')

@push('styles')
<style>
  .rekap-filter-form {
    display: grid !important;
    grid-template-columns: 140px 180px auto minmax(180px, 1fr) auto !important;
    gap: 12px !important;
    align-items: flex-end !important;
    margin: -2px 0 16px !important;
  }
  .rekap-date-wrap,
  .rekap-month-wrap,
  .rekap-year-wrap {
    grid-column: auto !important;
    width: 100% !important;
    display: flex;
    flex-direction: column;
  }
  .rekap-date-input,
  .rekap-month-input,
  .rekap-select-input {
    width: 100% !important;
    height: 42px !important;
    padding: 0 12px !important;
    font-size: 0.88rem !important;
    border-radius: 8px !important;
    background: rgba(255, 255, 255, 0.9) !important;
  }
  .rekap-action-row {
    display: flex !important;
    align-items: flex-end !important;
    gap: 8px !important;
    grid-column: auto !important;
    grid-row: auto !important;
  }
  .rekap-action-row .btn,
  .rekap-action-primary,
  .rekap-reset-button {
    grid-column: auto !important;
    grid-row: auto !important;
    width: auto !important;
    min-width: 72px !important;
    height: 42px !important;
    padding: 0 16px !important;
    font-size: 0.88rem !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
  }
  .rekap-filter-field label,
  .rekap-search-wrap label {
    margin-bottom: 4px !important;
    font-size: 0.8rem !important;
    font-weight: 600 !important;
    color: var(--gray-600) !important;
  }
  @media (max-width: 991px) {
    .rekap-filter-form {
      grid-template-columns: 1fr 1fr !important;
    }
  }
  @media (max-width: 576px) {
    .rekap-filter-form {
      grid-template-columns: 1fr !important;
    }
  }
</style>
@endpush

@section('content')
<div class="d-none d-print-block print-header">
  <div class="skb-logo skb-logo--print" aria-label="SKB Trans logo print">
    <img src="{{ asset('images/logo.png') }}" alt="SKB Trans" />
  </div>
  <div class="print-meta">
    <strong>Keluar Masuk Material</strong>
    <span>{{ now()->translatedFormat('d F Y') }}</span>
    <span>{{ now()->translatedFormat('H:i:s') }} WIB</span>
  </div>
</div>

<form method="GET" id="filterForm" class="filter-panel rekap-filter-form">
    <div class="rekap-filter-field rekap-mode-group">
      <label for="modeSelect" class="form-label">Periode</label>
      <select id="modeSelect" class="form-select rekap-mode-select" aria-label="Pilih mode filter">
        <option value="day">Day</option>
        <option value="month">Month</option>
        <option value="year">Year</option>
      </select>
    </div>

    <div class="rekap-filter-field rekap-date-wrap" id="dateGroup">
      <label for="dateSingle" class="form-label">Tanggal</label>
      <input type="date" id="dateSingle" class="form-control rekap-date-input" value="{{ $date_from ?? '' }}">
      <input type="hidden" name="date_from" id="date_from" value="{{ $date_from ?? '' }}">
      <input type="hidden" name="date_to" id="date_to" value="{{ $date_to ?? '' }}">
    </div>

    <div class="rekap-filter-field rekap-month-wrap" id="monthGroup" style="display:none;">
      <label for="monthNative" class="form-label">Bulan</label>
      <input type="month" id="monthNative" class="form-control rekap-month-input" value="{{ $year && $month ? sprintf('%04d-%02d', $year, $month) : '' }}">
      <input type="hidden" name="month" id="monthInput" value="{{ $month ?? '' }}">
    </div>

    <div class="rekap-filter-field rekap-year-wrap" id="yearGroup" style="display:none;">
      <label for="yearSelect" class="form-label">Tahun</label>
      <select id="yearSelect" name="year" class="form-select rekap-select-input">
        <option value="">--Pilih Tahun--</option>
        @for($y = date('Y'); $y >= date('Y')-5; $y--)
          <option value="{{ $y }}" @if(($year ?? '') == $y) selected @endif>{{ $y }}</option>
        @endfor
      </select>
    </div>

    <div class="rekap-action-row">
      <button type="submit" class="btn btn-primary rekap-action-primary">Apply</button>
      <a href="{{ route('rekap.index') }}" class="btn btn-outline-secondary rekap-reset-button">Reset</a>
    </div>

    <div class="rekap-search-wrap rekap-search-field">
      <label for="rekapSearch" class="form-label">Pencarian</label>
      <div class="rekap-search-control">
        <input id="rekapSearch" type="text" name="q" value="{{ $q ?? '' }}" placeholder="Material, driver, tujuan, no_rec..." class="form-control rekap-search-input">
        <button type="submit" class="rekap-search-submit" title="Cari" aria-label="Cari"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
      </div>
    </div>

    <div class="rekap-tools-wrap rekap-filter-actions">
      <div class="rekap-print-box">
        <a href="{{ route('rekap.export', request()->query()) }}" class="btn btn-icon btn-icon--success rekap-export-btn" title="Export Excel" aria-label="Export Excel">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h8"/></svg>
        </a>
        <a href="{{ route('rekap.pdf', request()->query()) }}" class="btn btn-icon btn-outline-secondary rekap-pdf-btn js-rekap-pdf-download" title="Export PDF" aria-label="Export PDF">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h9l5 5v15H6z"/><path d="M15 2v6h5"/><path d="M9 13h6M9 17h4"/></svg>
        </a>
        <button id="btnPrint" type="button" class="btn btn-icon btn-outline-secondary rekap-print-btn" title="Print" aria-label="Print">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 9V3h10v6"/><path d="M7 18H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M7 14h10v7H7z"/></svg>
        </button>
      </div>
    </div>
</form>

<script>
  document.getElementById('btnPrint')?.addEventListener('click', function(){
    window.print();
  });

  document.querySelectorAll('.js-rekap-pdf-download').forEach(function (link) {
    link.addEventListener('click', async function (event) {
      event.preventDefault();
      link.setAttribute('aria-busy', 'true');
      link.classList.add('is-loading');

      try {
        const response = await fetch(link.href, {
          credentials: 'same-origin',
          headers: { 'Accept': 'application/pdf' }
        });
        const contentType = response.headers.get('Content-Type') || '';

        if (!response.ok || !contentType.toLowerCase().includes('application/pdf')) {
          throw new Error('Server tidak mengembalikan file PDF.');
        }

        const blob = await response.blob();
        const disposition = response.headers.get('Content-Disposition') || '';
        const filenameMatch = disposition.match(/filename="?([^";]+)"?/i);
        const filename = filenameMatch?.[1] || 'rekap_pengiriman.pdf';
        const downloadUrl = URL.createObjectURL(blob);
        const downloadLink = document.createElement('a');
        downloadLink.href = downloadUrl;
        downloadLink.download = filename;
        document.body.appendChild(downloadLink);
        downloadLink.click();
        window.setTimeout(function () {
          downloadLink.remove();
          URL.revokeObjectURL(downloadUrl);
        }, 60000);
      } catch (error) {
        window.alert('PDF gagal diunduh. Periksa data/filter lalu coba lagi.');
        console.error('Rekap PDF download failed:', error);
      } finally {
        link.removeAttribute('aria-busy');
        link.classList.remove('is-loading');
      }
    });
  });

  (function(){
    const modeSelect = document.getElementById('modeSelect');
    const dateGroup = document.getElementById('dateGroup');
    const monthGroup = document.getElementById('monthGroup');
    const yearGroup = document.getElementById('yearGroup');
    const dateSingle = document.getElementById('dateSingle');
    const dateFrom = document.getElementById('date_from');
    const dateTo = document.getElementById('date_to');
    const monthNative = document.getElementById('monthNative');
    const monthInput = document.getElementById('monthInput');
    const yearSelect = document.getElementById('yearSelect');

    function updateMode(mode) {
      if (mode === 'day') {
        dateGroup.style.display = 'flex';
        monthGroup.style.display = 'none';
        yearGroup.style.display = 'none';
        monthInput.value = '';
        if (modeSelect.dataset.initial !== 'true') yearSelect.value = '';
      } else if (mode === 'month') {
        dateGroup.style.display = 'none';
        monthGroup.style.display = 'flex';
        yearGroup.style.display = 'none';
        dateFrom.value = '';
        dateTo.value = '';
      } else if (mode === 'year') {
        dateGroup.style.display = 'none';
        monthGroup.style.display = 'none';
        yearGroup.style.display = 'flex';
        dateFrom.value = '';
        dateTo.value = '';
        monthInput.value = '';
      }
    }

    dateSingle.addEventListener('change', function() {
      dateFrom.value = this.value;
      dateTo.value = this.value;
    });

    monthNative.addEventListener('change', function() {
      if (this.value) {
        const parts = this.value.split('-');
        yearSelect.value = parts[0];
        monthInput.value = parseInt(parts[1], 10);
      } else {
        monthInput.value = '';
      }
    });

    modeSelect.addEventListener('change', function() {
      modeSelect.dataset.initial = 'false';
      updateMode(this.value);
    });

    // Detect initial mode from current values
    const hasDf = '{{ $date_from ?? "" }}' !== '';
    const hasMonth = '{{ $month ?? "" }}' !== '';
    const hasYear = '{{ $year ?? "" }}' !== '';

    modeSelect.dataset.initial = 'true';
    if (hasDf) {
      modeSelect.value = 'day';
      updateMode('day');
    } else if (hasMonth) {
      modeSelect.value = 'month';
      updateMode('month');
    } else if (hasYear) {
      modeSelect.value = 'year';
      updateMode('year');
    } else {
      modeSelect.value = 'day';
      updateMode('day');
    }
  })();
</script>

<div class="panel section-panel rekap-table-panel">
  <div class="table-responsive rekap-table-responsive">
    <table class="table-modern rekap-table-modern" data-no-bulk-selection>
      <thead>
        <tr>
          <th>NO</th>
          <th>HARI/TANGGAL</th>
          <th>NAMA_ASAL</th>
          <th>NAMA_TUJUAN</th>
          <th>NAMA_DRIVER</th>
          <th>NO_POL</th>
          <th>NAMA_MATERIAL</th>
          <th>CODE_MATERIAL</th>
          <th>VOLUME</th>
          <th>TOTAL_VOLUME</th>
          <th>TIMBANGAN_CV_SR</th>
          <th>TOTAL_TIMBANGAN_CV_SR</th>
          <th>NO_REC</th>
          <th>JAM_BERANGKAT</th>
          <th>NAMA_TRANSPORTER</th>
        </tr>
      </thead>
      <tbody>
        @php($detailNumber = $detailNumberStart)
        @forelse($rows as $group)
          @foreach($group->details as $detailIndex => $r)
            <tr>
              <td>{{ $detailNumber++ }}</td>
              <td>{{ $r->tanggal }}</td>
              <td>{{ $r->nama_asal }}</td>
              <td>{{ $r->nama_tujuan }}</td>
              <td>{{ $r->nama_driver }}</td>
              <td>{{ $r->no_pol }}</td>
              <td>{{ $r->nama_material }}</td>
              <td>{{ $r->code_material }}</td>
              <td>{{ $r->vol_kg ?: '-' }}</td>
              @if($detailIndex === 0)
                <td rowspan="{{ $group->details->count() }}">{{ number_format($group->total_volume_kg, 0, ',', '.') }}</td>
              @endif
              <td>{{ $r->timbangan_cv_sr ?: '-' }}</td>
              @if($detailIndex === 0)
                <td rowspan="{{ $group->details->count() }}">{{ number_format($group->total_timbangan_cv_sr, 0, ',', '.') }}</td>
              @endif
              <td>{{ $r->no_rec }}</td>
              <td>{{ $r->jam_berangkat }}</td>
              <td>{{ $r->nama_transporter }}</td>
            </tr>
          @endforeach
        @empty
          <tr>
            <td colspan="15" class="text-center text-muted py-4">Belum ada data rekap untuk periode yang dipilih.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{ $rows->links() }}

@endsection
