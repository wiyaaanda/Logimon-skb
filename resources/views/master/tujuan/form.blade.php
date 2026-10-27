@extends('layouts.app')
@section('content')
<div class="panel section-panel">
  <form method="POST" action="{{ $item->exists ? route('master.tujuan.update',$item->id_tujuan) : route('master.tujuan.store') }}" class="compact-form">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Nama</label><input name="nama_tujuan" value="{{ old('nama_tujuan',$item->nama_tujuan) }}" class="form-control compact-field" required></div>
      <div class="col-md-6"><label class="form-label">Kota</label><input name="kota_tujuan" value="{{ old('kota_tujuan',$item->kota_tujuan) }}" class="form-control compact-field"></div>
    </div>
    <div class="row g-3 mt-0">
      <div class="col-md-6"><label class="form-label">Provinsi</label><input name="provensi_tujuan" value="{{ old('provensi_tujuan',$item->provensi_tujuan) }}" class="form-control compact-field"></div>
      <div class="col-md-6"><label class="form-label">No Telp</label><input name="no_telp_tujuan" value="{{ old('no_telp_tujuan',$item->no_telp_tujuan) }}" class="form-control compact-field"></div>
    </div>
    <div class="d-flex justify-content-end mt-4">
      <button class="btn btn-primary compact-button">Simpan</button>
    </div>
  </form>
</div>
@endsection
