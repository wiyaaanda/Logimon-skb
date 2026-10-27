@extends('layouts.app')
@section('content')
<div class="panel section-panel">
  <form method="POST" action="{{ $item->exists ? route('master.material.update',$item->id_material) : route('master.material.store') }}" class="compact-form">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div class="row g-3">
      <div class="col-md-3"><label class="form-label">Nama</label><input name="nama_material" value="{{ old('nama_material',$item->nama_material) }}" class="form-control compact-field" required></div>
      <div class="col-md-3"><label class="form-label">Code Material</label><select name="id_kategori_material" class="form-select compact-field"><option value="">Pilih Code...</option>@foreach($kategoris as $kategori)<option value="{{ $kategori->id_kategori_material }}" {{ old('id_kategori_material',$item->id_kategori_material) == $kategori->id_kategori_material ? 'selected' : '' }}>{{ $kategori->code_material }}</option>@endforeach</select></div>
      <div class="col-md-3"><label class="form-label">Jenis</label><input name="jenis_material" value="{{ old('jenis_material',$item->jenis_material) }}" class="form-control compact-field"></div>
      <div class="col-md-3"><label class="form-label">Stock Status</label><input name="stock_status_material" value="{{ old('stock_status_material',$item->stock_status_material) }}" class="form-control compact-field"></div>
    </div>
    <div class="d-flex justify-content-end mt-4">
      <button class="btn btn-primary compact-button">Simpan</button>
    </div>
  </form>
</div>
@endsection
