@extends('layouts.app')

@push('styles')
<style>
  .priority-card {
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(148, 163, 184, 0.18);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.03);
    padding: 16px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }
  .priority-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
  }
  .priority-card--urgent {
    border-left: 5px solid #ef4444 !important;
  }
  .priority-card--high {
    border-left: 5px solid #f59e0b !important;
  }
  .priority-card--normal {
    border-left: 5px solid #3b82f6 !important;
  }
  .priority-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .priority-icon-box--urgent {
    background: #fee2e2;
    color: #ef4444;
  }
  .priority-icon-box--high {
    background: #fef3c7;
    color: #f59e0b;
  }
  .priority-icon-box--normal {
    background: #dbeafe;
    color: #3b82f6;
  }
  .dashboard-charts-3col {
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr;
    gap: 18px;
    margin-bottom: 20px;
  }
  @media (max-width: 1200px) {
    .dashboard-charts-3col {
      grid-template-columns: 1fr 1fr;
    }
    .dashboard-charts-3col > section:first-child {
      grid-column: span 2;
    }
  }
  @media (max-width: 768px) {
    .dashboard-charts-3col {
      grid-template-columns: 1fr;
    }
    .dashboard-charts-3col > section:first-child {
      grid-column: span 1;
    }
  }
</style>
@endpush

@section('content')
  <div class="page-header compact-dashboard-header d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
      <h2 style="font-size: 1.25rem; font-weight: 700; margin: 0; color: #1e293b;">Dashboard Operasional</h2>
      <p style="font-size: 0.82rem; color: #64748b; margin: 2px 0 0;">
        Periode: <strong>{{ [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'][$selectedMonth] ?? '' }} {{ $selectedYear }}</strong>
      </p>
    </div>

    <form method="GET" class="d-flex align-items-center gap-2">
      <input type="hidden" name="jadwal_filter" value="{{ $jadwalFilter }}">
      <div style="min-width: 135px;">
        <select name="month" class="form-select form-select-sm" style="border-radius: 8px; font-weight: 500; height: 38px;" aria-label="Pilih Bulan">
          @foreach([
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
          ] as $num => $name)
            <option value="{{ $num }}" {{ $selectedMonth == $num ? 'selected' : '' }}>{{ $name }}</option>
          @endforeach
        </select>
      </div>

      <div style="min-width: 95px;">
        <select name="year" class="form-select form-select-sm" style="border-radius: 8px; font-weight: 500; height: 38px;" aria-label="Pilih Tahun">
          @for($y = date('Y') + 1; $y >= 2023; $y--)
            <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      </div>

      <button type="submit" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" style="border-radius: 8px; height: 38px; padding: 0 14px;">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
        Filter
      </button>

      @if(request()->has('month') || request()->has('year'))
        <a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center" style="border-radius: 8px; height: 38px; padding: 0 12px;">Reset</a>
      @endif
    </form>
  </div>

  <!-- Prioritas Pengiriman Section -->
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="priority-card priority-card--urgent">
        <div>
          <div style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; color: #ef4444; letter-spacing: 0.5px;">Prioritas Urgent</div>
          <div style="font-size: 1.85rem; font-weight: 800; color: #0f172a; line-height: 1.1; margin: 4px 0 2px;">{{ number_format($totalUrgent, 0, ',', '.') }}</div>
          <div style="font-size: 0.74rem; color: #64748b;">Mendesak / Penanganan Cepat</div>
        </div>
        <div class="priority-icon-box priority-icon-box--urgent">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="priority-card priority-card--high">
        <div>
          <div style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; color: #f59e0b; letter-spacing: 0.5px;">Prioritas High</div>
          <div style="font-size: 1.85rem; font-weight: 800; color: #0f172a; line-height: 1.1; margin: 4px 0 2px;">{{ number_format($totalHigh, 0, ',', '.') }}</div>
          <div style="font-size: 0.74rem; color: #64748b;">Prioritas Diutamakan</div>
        </div>
        <div class="priority-icon-box priority-icon-box--high">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="priority-card priority-card--normal">
        <div>
          <div style="font-size: 0.76rem; font-weight: 700; text-transform: uppercase; color: #3b82f6; letter-spacing: 0.5px;">Prioritas Normal</div>
          <div style="font-size: 1.85rem; font-weight: 800; color: #0f172a; line-height: 1.1; margin: 4px 0 2px;">{{ number_format($totalNormal, 0, ',', '.') }}</div>
          <div style="font-size: 0.74rem; color: #64748b;">Pengiriman Reguler / Standar</div>
        </div>
        <div class="priority-icon-box priority-icon-box--normal">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
      </div>
    </div>
  </div>

  <div class="metric-grid">
    <article class="metric-card">
      <div class="label">
        <span>Total Pengiriman</span>
        <span class="trend">{{ [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',7=>'Jul',8=>'Ags',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'][$selectedMonth] ?? '' }} {{ $selectedYear }}</span>
      </div>
      <div class="value">{{ number_format($totalPengiriman, 0, ',', '.') }}</div>
    </article>

    <article class="metric-card">
      <div class="label">
        <span>Driver Aktif</span>
        <span class="trend">Aktif</span>
      </div>
      <div class="value">{{ $driverAktif }}</div>
    </article>

    <article class="metric-card">
      <div class="label">
        <span>On-Time Rate</span>
        <span class="trend">{{ $onTimeRate }}%</span>
      </div>
      <div class="value">{{ $onTimeRate }}%</div>
    </article>

    <article class="metric-card">
      <div class="label">
        <span>Total Status</span>
        <span class="trend">{{ count($statusCounts) }} Kategori</span>
      </div>
      <div class="value">{{ array_sum(is_array($statusCounts) ? $statusCounts : $statusCounts->toArray()) }}</div>
    </article>

    <article class="metric-card">
      <div class="label">
        <span>Total Surat Masuk</span>
        <span class="trend">No Rec</span>
      </div>
      <div class="value">{{ number_format($totalSuratMasuk, 0, ',', '.') }}</div>
    </article>
  </div>

  <div class="stats-grid">
    <section class="panel section-panel">
      <div class="section-header">
        <h3>Jadwal Pengiriman</h3>
        <div class="pill-group" role="group" aria-label="Filter jadwal pengiriman">
          <a href="{{ route('dashboard', ['jadwal_filter' => 'hari_ini', 'month' => $selectedMonth, 'year' => $selectedYear]) }}" class="btn btn-sm {{ $jadwalFilter === 'hari_ini' ? 'btn-primary' : 'btn-outline-secondary' }}">Hari Ini</a>
          <a href="{{ route('dashboard', ['jadwal_filter' => 'besok', 'month' => $selectedMonth, 'year' => $selectedYear]) }}" class="btn btn-sm {{ $jadwalFilter === 'besok' ? 'btn-primary' : 'btn-outline-secondary' }}">Besok</a>
          <a href="{{ route('dashboard', ['jadwal_filter' => 'kemarin', 'month' => $selectedMonth, 'year' => $selectedYear]) }}" class="btn btn-sm {{ $jadwalFilter === 'kemarin' ? 'btn-primary' : 'btn-outline-secondary' }}">Kemarin</a>
          <a href="{{ route('dashboard', ['jadwal_filter' => 'semua', 'month' => $selectedMonth, 'year' => $selectedYear]) }}" class="btn btn-sm {{ $jadwalFilter === 'semua' ? 'btn-primary' : 'btn-outline-secondary' }}">Semua</a>
        </div>
      </div>

      <div class="dashboard-schedule-scroll">
        <table class="table-modern">
          <thead>
            <tr>
              <th>#</th>
              <th>Tanggal</th>
              <th>Driver</th>
              <th>Tujuan</th>
              <th>Material</th>
              <th>Priority</th>
              <th>Bobot</th>
              <th>Progress</th>
            </tr>
          </thead>
          <tbody>
            @forelse($items as $it)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ \Carbon\Carbon::parse($it->tanggal)->locale('id')->translatedFormat('d/m/Y') }}</td>
                <td>{{ $it->driver?->nama_driver ?? '-' }}</td>
                <td>{{ $it->tujuan?->nama_tujuan ?? '-' }}</td>
                <td>{{ $it->material?->nama_material ?? '-' }}</td>
                <td>
                  @if($it->priority === 'urgent')
                    <span class="status-pill danger">Urgent</span>
                  @elseif($it->priority === 'high')
                    <span class="status-pill warning">High</span>
                  @else
                    <span class="status-pill info">Normal</span>
                  @endif
                </td>
                <td>{{ number_format($it->nilai_bobot, 2) }}</td>
                <td>
                  <span class="status-pill info">{{ $it->progress_tujuan_persen }}%</span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="8">
                  <div class="empty-state">
                    <div>
                      <strong>Belum ada jadwal untuk filter ini</strong>
                      <span>Data pengiriman akan muncul setelah jadwal dibuat.</span>
                    </div>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="p-2 px-3 d-flex justify-content-between align-items-center border-top bg-light-subtle" style="font-size: 13px;">
        <span class="text-muted">Menampilkan {{ count($items) }} jadwal prioritas</span>
        <a href="{{ route('jadwal.index') }}" class="btn btn-sm btn-outline-primary" style="display: inline-flex; align-items: center; gap: 4px;">
          Buka Semua Jadwal &rarr;
        </a>
      </div>
    </section>

    <section class="panel section-panel">
      <div class="section-header">
        <h3>Statistik Pengiriman</h3>
      </div>
      <div class="row g-3 mb-3">
        <div class="col-6">
          <div class="metric-card kpi-card">
            <div class="label">
              <span>Top Material</span>
            </div>
            <div class="value" style="font-size:1.45rem;">{{ $materialLabels[0] ?? 'N/A' }}</div>
          </div>
        </div>
        <div class="col-6">
          <div class="metric-card kpi-card">
            <div class="label">
              <span>On-Time Rate</span>
            </div>
            <div class="value" style="font-size:1.45rem;">{{ $onTimeRate }}%</div>
          </div>
        </div>
      </div>
      <div class="chart-wrap">
        <canvas id="topMaterialChart" height="220"></canvas>
      </div>
      @if (array_sum($timbanganData) > 0)
        <div class="chart-wrap dashboard-chart-spaced">
          <canvas id="topTimbanganChart" height="220"></canvas>
        </div>
      @else
        <div class="empty-state dashboard-chart-spaced">
          <strong>Belum ada data timbangan</strong>
          <span>Grafik timbangan akan muncul setelah data tersedia.</span>
        </div>
      @endif
    </section>
  </div>

  <div class="dashboard-charts-3col">
    <section class="panel section-panel">
      <div class="section-header">
        <h3>Tren Volume & Timbangan</h3>
      </div>
      <div class="chart-wrap">
        <canvas id="volumeTimbanganTrendChart" height="240"></canvas>
      </div>
    </section>

    <section class="panel section-panel">
      <div class="section-header">
        <h3>Status Pengiriman</h3>
      </div>
      <div class="chart-wrap dashboard-pie-wrap">
        <canvas id="statusPieChart" height="240"></canvas>
      </div>
    </section>

    <section class="panel section-panel">
      <div class="section-header">
        <h3>Distribusi Prioritas</h3>
      </div>
      <div class="chart-wrap dashboard-pie-wrap">
        <canvas id="priorityPieChart" height="240"></canvas>
      </div>
    </section>
  </div>

  <script>
    (function renderDashboardCharts() {
      if (!window.Chart) {
        window.setTimeout(renderDashboardCharts, 50);
        return;
      }

      const materialLabels = {!! json_encode($materialLabels) !!};
      const materialData = {!! json_encode($materialData) !!};
      const timbanganLabels = {!! json_encode($timbanganLabels) !!};
      const timbanganData = {!! json_encode($timbanganData) !!};
      const trendLabels = {!! json_encode($trendLabels) !!};
      const volumeTrendData = {!! json_encode($volumeTrendData) !!};
      const timbanganTrendData = {!! json_encode($timbanganTrendData) !!};
      const statusLabels = {!! json_encode($statusLabels) !!};
      const statusData = {!! json_encode($statusData) !!};
      const priorityLabels = {!! json_encode($priorityLabels) !!};
      const priorityData = {!! json_encode($priorityData) !!};

      ['topMaterialChart', 'topTimbanganChart', 'volumeTimbanganTrendChart', 'statusPieChart', 'priorityPieChart'].forEach(function (id) {
        const canvas = document.getElementById(id);
        const chart = canvas && window.Chart.getChart(canvas);
        if (chart) chart.destroy();
      });

      new Chart(document.getElementById('topMaterialChart'), {
        type: 'bar',
        data: {
          labels: materialLabels,
          datasets: [{
            label: 'Top Material',
            data: materialData,
            borderRadius: 8,
            backgroundColor: ['#0B1220', '#334155', '#F5A524', '#0EA5E9', '#16A34A']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: {
              beginAtZero: true,
              grid: { color: '#E2E8F0' },
              ticks: { color: '#475569' }
            },
            x: {
              grid: { display: false },
              ticks: { color: '#475569' }
            }
          }
        }
      });

      @if (array_sum($timbanganData) > 0)
      new Chart(document.getElementById('topTimbanganChart'), {
        type: 'bar',
        data: {
          labels: timbanganLabels,
          datasets: [{
            label: 'Timbangan CV SR',
            data: timbanganData,
            borderRadius: 8,
            backgroundColor: '#0EA5A4'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, grid: { color: '#E2E8F0' }, ticks: { color: '#475569' } },
            x: { grid: { display: false }, ticks: { color: '#475569' } }
          }
        }
      });
      @endif

      new Chart(document.getElementById('volumeTimbanganTrendChart'), {
        type: 'line',
        data: {
          labels: trendLabels,
          datasets: [
            { label: 'Volume', data: volumeTrendData, borderColor: '#0EA5E9', backgroundColor: 'rgba(14,165,233,0.12)', fill: true, tension: 0.35 },
            { label: 'Timbangan', data: timbanganTrendData, borderColor: '#0F766E', backgroundColor: 'rgba(15,118,110,0.08)', fill: true, tension: 0.35 }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: { mode: 'index', intersect: false },
          plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, color: '#475569' } } },
          scales: {
            y: { beginAtZero: true, grid: { color: '#E2E8F0' }, ticks: { color: '#475569' } },
            x: { grid: { display: false }, ticks: { color: '#475569', maxRotation: 45 } }
          }
        }
      });

      new Chart(document.getElementById('statusPieChart'), {
        type: 'doughnut',
        data: {
          labels: statusLabels,
          datasets: [{
            data: statusData,
            backgroundColor: ['#0F766E', '#F59E0B', '#DC2626', '#0EA5E9', '#64748B'],
            borderColor: '#FFFFFF',
            borderWidth: 3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '62%',
          plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, color: '#475569' } } }
        }
      });

      // Priority Distribution Chart
      new Chart(document.getElementById('priorityPieChart'), {
        type: 'doughnut',
        data: {
          labels: priorityLabels,
          datasets: [{
            data: priorityData,
            backgroundColor: ['#EF4444', '#F59E0B', '#3B82F6'],
            borderColor: '#FFFFFF',
            borderWidth: 3
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '62%',
          plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, color: '#475569' } } }
        }
      });
    }());
  </script>
@endsection
