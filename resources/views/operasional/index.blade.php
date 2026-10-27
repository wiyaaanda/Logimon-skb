@extends('layouts.app')
@section('content')

<div class="mb-3">
  <form class="mb-0" method="GET">
    <input type="hidden" name="tab" value="{{ request('tab', 'operasional') }}">
    <div class="row g-2 align-items-center" style="margin: 0;">
      <div class="col-md-4" style="padding-right: 8px;">
        <input type="date" name="date" class="form-control compact-field" value="{{ request('date', $date ?? '') }}" />
      </div>
      <div class="col-md-8" style="padding-left: 8px;">
        <div class="d-flex align-items-center gap-2">
          <div class="input-group flex-grow-1">
            <input name="q" value="{{ request('q', '') }}" class="form-control" placeholder="Cari material, kode, tujuan, no rec...">
            <button class="btn btn-outline-secondary" title="Cari" aria-label="Cari"><svg class="search-button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
          </div>
          <a href="{{ route('operasional.export', array_filter(['date' => request('date'), 'q' => request('q')])) }}" class="btn btn-icon btn-icon--success toolbar-icon-button" title="Export Excel" aria-label="Export Excel"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H7a2 2 0 0 0 2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h8"/></svg></a>
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
      <th>Tujuan</th>
      <th>No Rec</th>
      <th>Jam Berangkat</th>
      <th>Jumlah Pengantaran</th>
    </tr>
  </thead>
  <tbody>
    @forelse($rows as $r)
      <tr>
        <td>{{ $rows->firstItem() + $loop->index }}</td>
        <td>{{ $r->tanggal }}</td>
        <td>{{ $r->tujuan }}</td>
        <td>{{ $r->no_rec }}</td>
        <td>{{ $r->jam_berangkat }}</td>
        <td>{{ $r->jumlah_pengantaran ?? 0 }}</td>
      </tr>
    @empty
      <tr>
        <td colspan="6" class="text-center text-muted py-4">Belum ada data operasional. Data akan tampil setelah ada pengiriman.</td>
      </tr>
    @endforelse
  </tbody>
</table>

{{ $rows->links() }}


@endsection
