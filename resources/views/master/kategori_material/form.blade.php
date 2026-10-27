@extends('layouts.app')
@section('content')
<div class="panel section-panel">
  <form method="POST" action="{{ $item->exists ? route('master.kategori_material.update',$item->id_kategori_material) : route('master.kategori_material.store') }}" class="compact-form">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div class="row g-3">
      <div class="col-md-3"><label class="form-label">Code</label><input name="code_material" value="{{ old('code_material',$item->code_material) }}" class="form-control compact-field"></div>
      <div class="col-md-3"><label class="form-label">Qty (kg)</label><input name="qty_material" value="{{ old('qty_material',$item->qty_material) }}" class="form-control compact-field" type="text"></div>
      <div class="col-md-3"><label class="form-label">Satuan</label><select name="satuan_material" class="form-select combo-select compact-field"><option value="" {{ old('satuan_material',$item->satuan_material) === null ? 'selected' : '' }}>-- Pilih --</option><option value="ton" {{ old('satuan_material',$item->satuan_material) === 'ton' ? 'selected' : '' }}>ton</option></select></div>
      <div class="col-md-3"><label class="form-label">Harga</label><input name="harga_material" value="{{ old('harga_material',$item->harga_material) }}" class="form-control compact-field" type="number" step="0.01"></div>
    </div>
    <div class="d-flex justify-content-end mt-4">
      <button class="btn btn-primary compact-button">Simpan</button>
    </div>
  </form>
</div>
@endsection
