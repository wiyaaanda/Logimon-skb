@extends('layouts.app')
@section('content')
  @php
    $selectedPeriod = request('period', 'month');
    $hasPeriodFilter = request()->filled('date');
    $hasAppliedFilters = $hasPeriodFilter || request()->filled('q');
    $selectedDate = request('date');
  @endphp
  <form method="GET" class="filter-panel status-log-filter-panel mb-3" data-has-applied-filters="{{ $hasAppliedFilters ? 'true' : 'false' }}">
    <input type="hidden" name="tab" value="{{ request('tab', 'status_log') }}">
    <div>
      <label class="form-label">Periode</label>
      <select name="period" class="form-select">
        <option value="month" {{ (request('period','month')=='month') ? 'selected' : '' }}>Month</option>
        <option value="day" {{ (request('period')=='day') ? 'selected' : '' }}>Day</option>
        <option value="year" {{ (request('period')=='year') ? 'selected' : '' }}>Year</option>
      </select>
    </div>
    <div>
      <label class="form-label" id="periodDateLabel">Tanggal</label>
      <input type="date" id="periodDate" name="date" value="{{ $selectedPeriod === 'day' && $hasPeriodFilter ? $selectedDate : '' }}" class="form-control" style="display: {{ $selectedPeriod === 'day' ? 'block' : 'none' }}" {{ $selectedPeriod !== 'day' ? 'disabled' : '' }} />
        <input type="month" id="periodMonth" name="date" value="{{ $selectedPeriod === 'month' && $hasPeriodFilter ? \Carbon\Carbon::parse($selectedDate)->format('Y-m') : '' }}" class="form-control" style="display: {{ $selectedPeriod === 'month' ? 'block' : 'none' }}" {{ $selectedPeriod !== 'month' ? 'disabled' : '' }} />
      <div class="year-picker" style="display: {{ $selectedPeriod === 'year' ? 'block' : 'none' }}">
          <input type="text" id="periodYear" value="{{ $selectedPeriod === 'year' && $hasPeriodFilter ? date('Y', strtotime($selectedDate)) : '' }}" class="form-control" inputmode="numeric" autocomplete="off" readonly {{ $selectedPeriod !== 'year' ? 'disabled' : '' }} aria-haspopup="listbox" aria-expanded="false" />
          <input type="hidden" id="periodYearValue" name="date" value="{{ $selectedPeriod === 'year' && $hasPeriodFilter ? date('Y', strtotime($selectedDate)) . '-01-01' : '' }}" {{ $selectedPeriod !== 'year' ? 'disabled' : '' }} />
        <div class="year-picker-menu" id="periodYearMenu" role="listbox" aria-label="Pilih tahun"></div>
      </div>
    </div>
    <div class="d-flex align-items-end">
      <button type="submit" id="periodApplyButton" class="btn btn-primary status-log-apply-button w-100 d-flex align-items-center justify-content-center" {{ $hasPeriodFilter ? '' : 'disabled' }}>Apply</button>
    </div>
    <div class="d-flex align-items-end">
      <a id="periodResetButton" href="{{ route('status_log.index') }}" class="btn btn-outline-secondary status-log-reset-button w-100 d-flex align-items-center justify-content-center {{ $hasAppliedFilters ? '' : 'disabled' }}" aria-disabled="{{ $hasAppliedFilters ? 'false' : 'true' }}" tabindex="{{ $hasAppliedFilters ? '0' : '-1' }}">Reset</a>
    </div>
    <div class="status-log-search-field">
      <div class="input-group">
        <input name="q" value="{{ request('q', '') }}" class="form-control" placeholder="Cari driver, username, asal, tujuan, material, no rec...">
        <button class="btn btn-outline-secondary" title="Cari" aria-label="Cari"><svg class="search-button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
      </div>
    </div>
  </form>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const period = document.querySelector('[name="period"]');
      const dateLabel = document.getElementById('periodDateLabel');
      const dayInput = document.getElementById('periodDate');
      const monthInput = document.getElementById('periodMonth');
      const yearInput = document.getElementById('periodYear');
      const yearValue = document.getElementById('periodYearValue');
      const yearMenu = document.getElementById('periodYearMenu');
      const yearPicker = document.querySelector('.year-picker');
      const applyButton = document.getElementById('periodApplyButton');
      const resetButton = document.getElementById('periodResetButton');
      const form = period.closest('form');
      const searchInput = form.querySelector('[name="q"]');
      const initiallyFiltered = form.dataset.hasAppliedFilters === 'true';
      const currentYear = new Date().getFullYear();
      let currentMode = period.value;

      function selectedValue(mode) {
        if (mode === 'day') return dayInput.value;
        if (mode === 'year') return yearValue.value;
        return monthInput.value;
      }

      function syncApplyButton() {
        applyButton.disabled = !selectedValue(period.value);
      }

      function syncResetButton() {
        const canReset = initiallyFiltered || Boolean(selectedValue(period.value) || searchInput.value.trim());
        resetButton.classList.toggle('disabled', !canReset);
        resetButton.setAttribute('aria-disabled', String(!canReset));
        resetButton.tabIndex = canReset ? 0 : -1;
      }

      function syncPeriodInput() {
        const mode = period.value;
        dateLabel.textContent = mode === 'day' ? 'Tanggal' : mode === 'month' ? 'Bulan' : 'Tahun';
        dayInput.disabled = mode !== 'day';
        dayInput.style.display = mode === 'day' ? '' : 'none';
        monthInput.disabled = mode !== 'month';
        monthInput.style.display = mode === 'month' ? '' : 'none';
        yearInput.disabled = mode !== 'year';
        yearValue.disabled = mode !== 'year';
        yearPicker.style.display = mode === 'year' ? '' : 'none';
      }

      for (let year = currentYear + 5; year >= 2000; year -= 1) {
        const option = document.createElement('button');
        option.type = 'button';
        option.className = 'year-picker-option';
        option.textContent = year;
        option.dataset.year = year;
        option.setAttribute('role', 'option');
        yearMenu.appendChild(option);
      }

      yearInput.addEventListener('click', function () {
        if (yearInput.disabled) return;
        const isOpen = yearMenu.classList.toggle('is-open');
        yearInput.setAttribute('aria-expanded', String(isOpen));
      });

      yearMenu.addEventListener('click', function (event) {
        const option = event.target.closest('.year-picker-option');
        if (!option) return;
        yearInput.value = option.dataset.year;
        yearValue.value = option.dataset.year + '-01-01';
        yearMenu.classList.remove('is-open');
        yearInput.setAttribute('aria-expanded', 'false');
        syncApplyButton();
        syncResetButton();
      });

      document.addEventListener('click', function (event) {
        if (!event.target.closest('.year-picker')) {
          yearMenu.classList.remove('is-open');
          yearInput.setAttribute('aria-expanded', 'false');
        }
      });

      period.addEventListener('change', function () {
        const previousValue = selectedValue(currentMode);
        const nextMode = period.value;
        const selectedYear = previousValue ? previousValue.slice(0, 4) : '';

        if (nextMode === 'year') {
          yearInput.value = selectedYear;
          yearValue.value = selectedYear ? selectedYear + '-01-01' : '';
        } else if (nextMode === 'month') {
          monthInput.value = currentMode === 'day' && previousValue ? previousValue.slice(0, 7) : '';
        } else {
          dayInput.value = currentMode === 'day' ? previousValue : '';
        }

        currentMode = nextMode;
        syncPeriodInput();
        syncApplyButton();
        syncResetButton();
      });

      [dayInput, monthInput].forEach(function (input) {
        input.addEventListener('input', syncApplyButton);
        input.addEventListener('change', syncApplyButton);
        input.addEventListener('input', syncResetButton);
        input.addEventListener('change', syncResetButton);
      });
      searchInput.addEventListener('input', syncResetButton);
      resetButton.addEventListener('click', function (event) {
        if (resetButton.getAttribute('aria-disabled') === 'true') {
          event.preventDefault();
        }
      });

      form.addEventListener('submit', function (event) {
        if (event.submitter === applyButton && !selectedValue(period.value)) {
          event.preventDefault();
        }
      });

      syncPeriodInput();
      syncApplyButton();
      syncResetButton();

      window.setInterval(function () {
        const activeElement = document.activeElement;
        const isEditing = activeElement && !['BODY', 'HTML'].includes(activeElement.tagName);
        if (document.visibilityState === 'visible' && !isEditing) {
          window.location.reload();
        }
      }, 15000);
    });
  </script>

  @if($rows->isNotEmpty())
    <div class="metric-grid">
      <article class="metric-card">
        <div class="label"><span>Total</span></div>
        <div class="value">{{ number_format($total ?? 0) }}</div>
      </article>
      <article class="metric-card">
        <div class="label"><span>Pending</span></div>
        <div class="value">{{ number_format($pending ?? 0) }}</div>
      </article>
      <article class="metric-card">
        <div class="label"><span>Proses</span></div>
        <div class="value">{{ number_format($proses ?? 0) }}</div>
      </article>
      <article class="metric-card">
        <div class="label"><span>Selesai</span></div>
        <div class="value">{{ number_format($selesai ?? 0) }}</div>
      </article>
    </div>

    <div class="panel section-panel mb-4">
      <div class="section-header">
        <h3>Distribusi Status</h3>
      </div>
      <div style="height:300px;">
        <canvas id="statusPie" style="max-height:100%; width:100%"></canvas>
      </div>
    </div>

  @endif

  <div class="panel section-panel">
    <table class="table-modern">
      <thead>
        <tr>
          <th>#</th>
          <th>Driver</th>
          <th>Username</th>
          <th>Dari</th>
          <th>Tujuan</th>
          <th>Material</th>
          <th>Code Material</th>
          <th>No Rec</th>
          <th>Waktu Pending</th>
          <th>Waktu Penyelesaian</th>
          <th>Status Update</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rows as $r)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $r->driver }}</td>
              <td>{{ $r->username }}</td>
              <td>{{ $r->dari }}</td>
              <td>{{ $r->tujuan_field }}</td>
              <td>{{ $r->material }}</td>
              <td>{{ $r->code_material ?? '-' }}</td>
              <td>{{ $r->no_rec ?? '' }}</td>
              <td>{{ $r->waktu_pending ?: '-' }}</td>
              <td>{{ $r->waktu_penyelesaian ?: '-' }}</td>
              <td>
                @if($r->status_update === 'Pending')
                  <span class="status-pill danger">Pending</span>
                @elseif($r->status_update === 'Proses')
                  <span class="status-pill warning">Proses</span>
                @elseif($r->status_update === 'Selesai')
                  <span class="status-pill success">Selesai</span>
                @elseif($r->status_update === 'Terjadwal')
                  <span class="status-pill info">Terjadwal</span>
                @else
                  <span class="status-pill muted">Belum ada log</span>
                @endif
              </td>
              <td>
                @if($r->id_log)
                  <a href="{{ route('status_log.show', $r->id_log) }}" class="btn btn-icon btn-icon--small" title="Detail" aria-label="Lihat detail status log"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/></svg></a>
                @else
                  <span class="text-muted">-</span>
                @endif
              </td>
            </tr>
        @empty
          <tr>
            <td colspan="12" class="text-center text-muted py-4">Belum ada data status log. Data akan tampil setelah ada pembaruan status.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $rows->links() }}
  </div>

  @push('scripts')
  <script>
    (function renderStatusDistribution() {
      const ctx = document.getElementById('statusPie');
      if (!ctx) return;
      if (!window.Chart) {
        window.setTimeout(renderStatusDistribution, 50);
        return;
      }
      if (window.statusDistributionChart) {
        window.statusDistributionChart.destroy();
      }
      window.statusDistributionChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels: ['Selesai','Pending','Proses','Urgent','High','Normal'],
          datasets: [{
            data: [{{ $selesai ?? 0 }}, {{ $pending ?? 0 }}, {{ $proses ?? 0 }}, {{ $urgent ?? 0 }}, {{ $high ?? 0 }}, {{ $normal ?? 0 }}],
            backgroundColor: ['#16A34A','#DC2626','#F97316','#0EA5E9','#F5A524','#334155']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'bottom' }
          }
        }
      });
    }());
  </script>
  @endpush
@endsection
