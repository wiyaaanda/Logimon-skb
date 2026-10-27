@extends('layouts.app')
@section('content')
<div class="panel section-panel">
  <form method="POST" action="{{ $item->exists ? route('master.asal.update',$item->id_asal) : route('master.asal.store') }}" class="compact-form">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Nama</label><input name="nama_asal" value="{{ old('nama_asal',$item->nama_asal) }}" class="form-control compact-field" required></div>
      <div class="col-md-6"><label class="form-label">Kota</label><input name="kota_asal" value="{{ old('kota_asal',$item->kota_asal) }}" class="form-control compact-field"></div>
    </div>
    <div class="row g-3 mt-0">
      <div class="col-md-6"><label class="form-label">Provinsi</label><input name="provensi_asal" value="{{ old('provensi_asal',$item->provensi_asal) }}" class="form-control compact-field"></div>
      <div class="col-md-6"><label class="form-label">No Telp</label><input name="no_telp_asal" value="{{ old('no_telp_asal',$item->no_telp_asal) }}" class="form-control compact-field"></div>
    </div>
    <div class="d-flex justify-content-end mt-4">
      <button class="btn btn-primary compact-button">Simpan</button>
    </div>
  </form>
</div>
@endsection
