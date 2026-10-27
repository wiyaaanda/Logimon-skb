@extends('layouts.app')
@section('content')
  @php
    $jadwalImportForm = '<form method="POST" action="'.route('master.import.execute', 'jadwal').'" enctype="multipart/form-data" class="master-import-form js-import-form" data-preview-url="'.route('master.import.preview', 'jadwal').'" data-import-url="'.route('master.import.execute', 'jadwal').'">'.csrf_field().'<input type="file" name="file" class="form-control js-import-file" accept=".xlsx" hidden><button type="button" class="btn btn-icon btn-icon--muted import-trigger" title="Import jadwal" aria-label="Import jadwal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4"/></svg></button></form>';
  @endphp
  @component('components.admin-page-header', [
      'actions' => '<a href="'.route('jadwal.create').'" class="btn btn-icon" title="Tambah Jadwal" aria-label="Tambah Jadwal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg></a>'.$jadwalImportForm.'<a href="'.route('jadwal.import.template').'" class="btn btn-icon btn-icon--muted" title="Download template import" aria-label="Download template import"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12"/><path d="m7 11 5 5 5-5"/><path d="M5 21h14"/></svg></a>'
  ])
  @endcomponent

  <div class="panel section-panel">
    <table class="table-modern">
      <thead>
        <tr>
          <th>#</th>
          <th>Tanggal</th>
          <th>Driver</th>
          <th>Tujuan</th>
          <th>Material</th>
          <th>Code Material</th>
          <th>No Rec</th>
          <th>Jam Berangkat</th>
          <th>Volume</th>
          <th>Timbangan CV SR</th>
          <th>Priority</th>
          <th>Nilai Bobot</th>
          <th>Progress</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        @forelse($items as $it)
          <tr>
            <td>{{ $items->firstItem() ? ($items->firstItem() + $loop->index) : $loop->iteration }}</td>
            <td>{{ strtoupper(\Carbon\Carbon::parse($it->tanggal)->locale('id')->translatedFormat('l, d F Y')) }}</td>
            <td>{{ $it->driver?->nama_driver }}</td>
            <td>{{ $it->tujuan?->nama_tujuan }}</td>
            <td>{{ $it->material?->nama_material }}</td>
            <td>{{ $it->code_material }}</td>
            <td>{{ $it->no_rec ?: $it->pengirimans->sortByDesc('id_pengiriman')->first()?->no_rec }}</td>
            <td>{{ $it->jam_berangkat ?: $it->pengirimans->sortByDesc('id_pengiriman')->first()?->jam_berangkat }}</td>
            <td>{{ number_format($it->pengirimans->sortByDesc('id_pengiriman')->first()?->vol_kg ?? 0, 0, ',', '.') }}</td>
            <td>{{ number_format($it->pengirimans->sortByDesc('id_pengiriman')->first()?->timbangan_cv_sr ?? 0, 0, ',', '.') }}</td>
            <td>
              @if($it->priority === 'urgent')
                <span class="status-pill danger">Urgent</span>
              @elseif($it->priority === 'high')
                <span class="status-pill warning">High</span>
              @else
                <span class="status-pill info">Normal</span>
              @endif
            </td>
            <td>{{ number_format($it->nilai_bobot,2) }}</td>
            <td><span class="status-pill info">{{ $it->progress_tujuan_persen }}%</span></td>
            <td>
              @if($it->status === 'Selesai')
                <span class="status-pill success">Selesai</span>
              @elseif($it->status === 'Proses')
                <span class="status-pill warning">Proses</span>
              @elseif($it->status === 'Pending')
                <span class="status-pill danger">Pending</span>
              @else
                <span class="status-pill info">Terjadwal</span>
              @endif
            </td>
            <td>
              <a class="btn btn-icon btn-icon--small" href="{{ route('jadwal.edit',$it->id_jadwal) }}" title="Edit" aria-label="Edit jadwal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5Z"/></svg></a>
              <form method="POST" action="{{ route('jadwal.destroy',$it->id_jadwal) }}" style="display:inline">@csrf @method('DELETE')<button class="btn btn-icon btn-icon--small btn-icon--danger" title="Hapus" aria-label="Hapus jadwal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M6 6l1 14h10l1-14"/><path d="M10 11v6M14 11v6"/></svg></button></form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="15" class="text-center text-muted py-4">Belum ada data jadwal. Tambahkan jadwal untuk menampilkan data.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    @if($items->hasPages())
      <div class="p-3 border-top">
        {{ $items->links() }}
      </div>
    @endif
  </div>

@endsection
