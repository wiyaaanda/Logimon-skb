@extends('layouts.app')
@section('content')
<!-- Search Form -->
<form class="mb-3" method="GET">
  <div class="input-group">
    <input name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Cari aktivitas...">
    <button class="btn btn-outline-secondary" title="Cari" aria-label="Cari"><svg class="search-button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
  </div>
</form>

<div class="table-responsive">
  <table class="table table-sm table-hover">
    <thead class="table-light">
      <tr>
        <th width="150">Waktu</th>
        <th width="120">Tipe</th>
        <th width="200">Nama Akun</th>
        <th>Aktivitas</th>
      </tr>
    </thead>
    <tbody>
      @forelse($history as $item)
        <tr>
          <td>
            <small>{{ $item->created_at->format('d/m/Y H:i:s') }}</small>
          </td>
          <td>
            @if($item->id_user)
              <span class="badge bg-primary">Admin</span>
            @else
              <span class="badge bg-success">Driver</span>
            @endif
          </td>
          <td>
            @if($item->id_user && $item->user)
              <strong>{{ $item->user->name }}</strong><br>
              <code>{{ $item->user->username }}</code>
            @elseif($item->id_driver && $item->driver)
              <strong>{{ $item->driver->nama_driver }}</strong><br>
              <code>{{ $item->driver->username }}</code>
            @else
              <em class="text-muted">Data tidak tersedia</em>
            @endif
          </td>
          <td>
            {{ $item->aktivitas_akun }}
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" class="text-center text-muted py-4">Tidak ada history aktivitas</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

{{ $history->links() }}
@endsection
