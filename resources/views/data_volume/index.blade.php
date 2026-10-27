@extends('layouts.app')
@section('content')
<div class="row mb-4">
  <div class="col-md-4"><div class="card p-2"><div class="small text-muted">Total Volume (kg)</div><div class="h5">{{ number_format($totalVolume, 0, ',', '.') }}</div></div></div>
  <div class="col-md-4"><div class="card p-2"><div class="small text-muted">Total Pengantaran</div><div class="h5">{{ number_format($totalPengantaran) }}</div></div></div>
  <div class="col-md-4"><div class="card p-2"><div class="small text-muted">Rows</div><div class="h5">{{ number_format($rows->count()) }}</div></div></div>
</div>

<div class="mb-3">
  <form class="mb-3" method="GET">
    <input type="hidden" name="tab" value="{{ request('tab', 'volume') }}">
    <div class="row g-2 align-items-center">
      <div class="col-md-4">
        <input type="date" name="date" class="form-control" value="{{ request('date', $date ?? '') }}" />
      </div>
      <div class="col-md-8">
        <div class="d-flex align-items-center gap-2">
          <div class="input-group flex-grow-1">
            <input name="q" value="{{ request('q', '') }}" class="form-control" placeholder="Cari material, kode, status, atau volume...">
            <button class="btn btn-outline-secondary" title="Cari" aria-label="Cari"><svg class="search-button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
          </div>
          <a href="{{ route('data_volume.export', array_filter(['date'=>request('date'),'q'=>request('q')])) }}" class="btn btn-icon btn-icon--success toolbar-icon-button" title="Export Excel" aria-label="Export Excel">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h8"/></svg>
          </a>
        </div>
      </div>
    </div>
  </form>
</div>

<table class="table table-sm">
  <thead>
    <tr>
      <th>#</th>
      <th>Tanggal</th>
      <th>Nama Material</th>
      <th>Code Material</th>
      <th>Qty</th>
      <th>Total Volume</th>
      <th>Jumlah Pengantaran</th>
    </tr>
  </thead>
  <tbody>
    @php($detailNumber = $resultFrom)
    @forelse($rows as $group)
      @foreach($group->details as $detailIndex => $r)
        <tr>
          <td>{{ $detailNumber++ }}</td>
          <td>{{ $r->tanggal }}</td>
          <td>{{ $r->nama_material }}</td>
          <td>{{ $r->code_material ?? '' }}</td>
          <td>kg</td>
          @if($detailIndex === 0)
            <td rowspan="{{ $group->details->count() }}">{{ number_format($group->group_total_volume, 0, ',', '.') }}</td>
            <td rowspan="{{ $group->details->count() }}">{{ $group->group_total_pengantaran }}</td>
          @endif
        </tr>
      @endforeach
    @empty
      <tr>
        <td colspan="7" class="text-center text-muted py-4">Belum ada data volume. Data akan tampil setelah ada pengiriman.</td>
      </tr>
    @endforelse
  </tbody>
</table>

{{ $rows->links() }}

<script>
  const labels = {!! json_encode($labels) !!};
  const data = {!! json_encode($data) !!};
  const ctx = document.getElementById('volumeChart').getContext('2d');
  new Chart(ctx, { type: 'bar', data: { labels: labels, datasets:[{ label:'Volume (kg)', data:data, backgroundColor:'rgba(75,192,192,0.6)'}] }, options:{scales:{y:{beginAtZero:true}}} });
</script>
@endsection
