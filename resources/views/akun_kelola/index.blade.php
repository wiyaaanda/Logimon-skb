@extends('layouts.app')
@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div class="d-flex flex-wrap align-items-center gap-2">
    <a class="btn btn-sm {{ $tab === 'admin' ? 'btn-primary' : 'btn-outline-secondary' }}" href="?tab=admin">
      Admin
    </a>
    <a class="btn btn-sm {{ $tab === 'driver' ? 'btn-primary' : 'btn-outline-secondary' }}" href="?tab=driver">
      Driver
    </a>
  </div>

  <div class="d-flex flex-wrap align-items-center gap-2">
    @if($tab === 'admin')
      <a href="{{ route('akun_kelola.createAdmin') }}" class="btn btn-icon toolbar-icon-button" title="Tambah Admin" aria-label="Tambah Admin">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
      </a>
    @else
      <a href="{{ route('akun_kelola.createDriver') }}" class="btn btn-icon toolbar-icon-button" title="Tambah Driver" aria-label="Tambah Driver">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
      </a>
    @endif
    <a href="{{ route('akun_kelola.history') }}" class="btn btn-sm btn-info">History Aktivitas</a>
  </div>
</div>

@if(session('success_message'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <h5 class="alert-heading mb-2">Berhasil!</h5>
    <div style="line-height: 1.8;">
      {!! nl2br(session('success_message')) !!}
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

@if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
@endif

<!-- Search Form -->
<form class="mb-3" method="GET">
  <input type="hidden" name="tab" value="{{ $tab }}">
  <div class="input-group" style="max-width: 420px;">
    <input name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Cari...">
    <button class="btn btn-outline-secondary" title="Cari" aria-label="Cari"><svg class="search-button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
  </div>
</form>

<!-- Admin Tab Content -->
@if($tab === 'admin')
  <div class="table-responsive">
    <table class="table table-sm table-hover">
      <thead class="table-light">
        <tr>
          <th>#</th>
          <th>Nama</th>
          <th>Username</th>
          <th>Email</th>
          <th>Dibuat</th>
          <th width="200"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->name }}</td>
            <td><code>{{ $item->username }}</code></td>
            <td>{{ $item->email }}</td>
            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
            <td>
              <a class="btn btn-icon btn-icon--small" href="{{ route('akun_kelola.editAdmin', $item->id_user) }}" title="Edit" aria-label="Edit admin"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg></a>
              <form method="POST" action="{{ route('akun_kelola.resetPasswordAdmin', $item->id_user) }}" style="display:inline" title="Reset Password">
                @csrf
                <button type="submit" class="btn btn-icon btn-icon--small btn-icon--muted" data-confirm-title="Reset password?" data-confirm="Apakah Anda yakin ingin mereset password akun ini?" data-confirm-action="Iya" aria-label="Reset password admin"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 10V8a5 5 0 1 1 10 0v2"/><rect x="4" y="10" width="16" height="10" rx="2"/><circle cx="12" cy="15" r="1.5"/></svg></button>
              </form>
              <form method="POST" action="{{ route('akun_kelola.destroy') }}" style="display:inline">
                @csrf @method('DELETE')
                <input type="hidden" name="type" value="admin">
                <input type="hidden" name="id" value="{{ $item->id_user }}">
                <button type="submit" class="btn btn-icon btn-icon--small btn-icon--danger" data-confirm-title="Hapus akun?" data-confirm="Yakin hapus akun ini? Data yang dihapus tidak dapat dikembalikan." data-confirm-action="Iya" aria-label="Hapus admin"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6M14 11v6"/></svg></button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">Tidak ada data admin</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links() }}
@endif

<!-- Driver Tab Content -->
@if($tab === 'driver')
  <div class="table-responsive">
    <table class="table table-sm table-hover">
      <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Status</th>
            <th>Dibuat</th>
            <th width="280"></th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->name }}</td>
            <td><code>{{ $item->username }}</code></td>

            <td>
              <form method="POST" action="{{ route('akun_kelola.updateStatusDriver', $item->id_user) }}" style="display:inline">
                @csrf
                <select name="status" class="form-select form-select-sm" style="width:120px; display:inline-block" onchange="this.form.submit()">
                  <option value="aktif" selected>Aktif</option>
                  <option value="nonaktif">Nonaktif</option>
                </select>
              </form>
            </td>
            <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
            <td>
              <a class="btn btn-icon btn-icon--small" href="{{ route('akun_kelola.editDriver', $item->id_user) }}" title="Edit" aria-label="Edit driver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1-1-4 12.5-12.5Z"/></svg></a>
              <form method="POST" action="{{ route('akun_kelola.resetPasswordDriver', $item->id_user) }}" style="display:inline" title="Reset Password">
                @csrf
                <button type="submit" class="btn btn-icon btn-icon--small btn-icon--muted" data-confirm-title="Reset password?" data-confirm="Apakah Anda yakin ingin mereset password akun ini?" data-confirm-action="Iya" aria-label="Reset password driver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 10V8a5 5 0 1 1 10 0v2"/><rect x="4" y="10" width="16" height="10" rx="2"/><circle cx="12" cy="15" r="1.5"/></svg></button>
              </form>
              <form method="POST" action="{{ route('akun_kelola.destroy') }}" style="display:inline">
                @csrf @method('DELETE')
                <input type="hidden" name="type" value="driver">
                <input type="hidden" name="id" value="{{ $item->id_user }}">
                <button type="submit" class="btn btn-icon btn-icon--small btn-icon--danger" data-confirm-title="Hapus akun?" data-confirm="Yakin hapus akun ini? Data yang dihapus tidak dapat dikembalikan." data-confirm-action="Iya" aria-label="Hapus driver"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6M14 11v6"/></svg></button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted py-4">Tidak ada data driver</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $items->links() }}
@endif
@endsection
