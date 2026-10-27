@extends('layouts.app')
@section('content')
<div class="master-toolbar mb-3">
  <div class="master-actions">
    <a href="{{ route('master.kendaraan.create') }}" class="btn btn-icon" title="Tambah Kendaraan" aria-label="Tambah Kendaraan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg></a>
    <a href="{{ route('master.kendaraan.downloadTemplate') }}" class="btn btn-icon btn-icon--muted" title="Download Template" aria-label="Download Template"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v11"/><path d="M7 17l5 5 5-5"/></svg></a>
    <a href="{{ route('master.kendaraan.export') }}" class="btn btn-icon btn-icon--success" title="Export" aria-label="Export"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6M9 17h6"/></svg></a>
  </div>

  <div class="master-tools">
    <form method="GET" class="master-search-form">
      <input name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Cari...">
      <button class="btn btn-outline-secondary" title="Cari" aria-label="Cari"><svg class="search-button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
    </form>

    <form method="POST" action="{{ route('master.kendaraan.import') }}" enctype="multipart/form-data" class="master-import-form js-import-form" data-preview-url="{{ route('master.import.preview', 'kendaraan') }}" data-import-url="{{ route('master.import.execute', 'kendaraan') }}">
      @csrf
      <input type="file" name="file" class="form-control js-import-file" accept=".xlsx" hidden>
      <button type="button" class="btn btn-icon btn-icon--muted import-trigger" title="Import file" aria-label="Import file"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/></svg></button>
    </form>
  </div>
</div>
<table class="table table-sm">
  <thead><tr><th>#</th><th>No Pol</th><th>Jenis</th><th>Model</th><th>Code</th><th>Transporter</th><th></th></tr></thead>
  <tbody>
    @forelse($items as $it)
      <tr>
        <td>{{ $items->firstItem() + $loop->index }}</td>
        <td>{{ $it->no_pol }}</td>
        <td>{{ $it->jenis_kendaraan }}</td>
        <td>{{ $it->model_kendaraan }}</td>
        <td>{{ $it->code_kendaraan }}</td>
        <td>{{ $it->transporter?->nama_transporter }}</td>
        <td>
          <a class="btn btn-icon btn-icon--small" href="{{ route('master.kendaraan.edit',$it->id_kendaraan) }}" title="Edit" aria-label="Edit kendaraan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg></a>
          <form method="POST" action="{{ route('master.kendaraan.destroy',$it->id_kendaraan) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-icon btn-icon--small btn-icon--danger" title="Hapus" aria-label="Hapus kendaraan"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6M14 11v6"/></svg></button></form>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="7" class="text-center text-muted py-4">Belum ada data kendaraan. Tambahkan kendaraan untuk menampilkan data.</td>
      </tr>
    @endforelse
  </tbody>
</table>
{{ $items->links() }}
@endsection
