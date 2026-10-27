@extends('layouts.app')
@section('content')
<div class="master-toolbar mb-3">
  <div class="master-actions">
    <a href="{{ route('master.kategori_material.create') }}" class="btn btn-icon" title="Tambah Kategori Material" aria-label="Tambah Kategori Material"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg></a>
    <a href="{{ route('master.kategori_material.downloadTemplate') }}" class="btn btn-icon btn-icon--muted" title="Download Template" aria-label="Download Template"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="M7 17l5 5 5-5"/></svg></a>
    <a href="{{ route('master.kategori_material.export') }}" class="btn btn-icon btn-icon--success" title="Export" aria-label="Export">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h8"/></svg>
    </a>
  </div>

  <div class="master-tools">
    <form method="GET" class="master-search-form">
      <input name="q" value="{{ $q ?? '' }}" class="form-control" placeholder="Cari...">
      <button class="btn btn-outline-secondary" title="Cari" aria-label="Cari"><svg class="search-button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg></button>
    </form>

    <form method="POST" action="{{ route('master.kategori_material.import') }}" enctype="multipart/form-data" class="master-import-form js-import-form" data-preview-url="{{ route('master.import.preview', 'kategori_material') }}" data-import-url="{{ route('master.import.execute', 'kategori_material') }}">
      @csrf
      <input type="file" name="file" class="form-control js-import-file" accept=".xlsx" hidden>
      <button type="button" class="btn btn-icon btn-icon--muted import-trigger" title="Import file" aria-label="Import file"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/></svg></button>
    </form>
  </div>
</div>
<table class="table table-sm">
  <thead><tr><th>#</th><th>Code</th><th>Qty (kg)</th><th>Satuan</th><th>Harga</th><th></th></tr></thead>
  <tbody>
    @forelse($items as $it)
      <tr>
        <td>{{ $items->firstItem() + $loop->index }}</td>
        <td>{{ $it->code_material }}</td>
        <td>{{ $it->qty_material }}</td>
        <td>{{ $it->satuan_material }}</td>
        <td>{{ $it->harga_material }}</td>
        <td>
          <a class="btn btn-icon btn-icon--small" href="{{ route('master.kategori_material.edit',$it->id_kategori_material) }}" title="Edit" aria-label="Edit kategori material"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg></a>
          <form method="POST" action="{{ route('master.kategori_material.destroy',$it->id_kategori_material) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-icon btn-icon--small btn-icon--danger" title="Hapus" aria-label="Hapus kategori material"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6M14 11v6"/></svg></button></form>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="6" class="text-center text-muted py-4">Belum ada data kategori material. Tambahkan kategori untuk menampilkan code material.</td>
      </tr>
    @endforelse
  </tbody>
</table>
{{ $items->links() }}
@endsection
