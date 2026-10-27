@extends('layouts.app')
@section('content')
<div class="panel section-panel">
  <form method="POST" action="{{ $item->exists ? route('master.kendaraan.update',$item->id_kendaraan) : route('master.kendaraan.store') }}" class="compact-form">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div class="row g-3">
      <div class="col-md-3"><label class="form-label">No Pol</label><input name="no_pol" value="{{ old('no_pol',$item->no_pol) }}" class="form-control compact-field" required></div>
      <div class="col-md-3"><label class="form-label">Jenis</label><input name="jenis_kendaraan" value="{{ old('jenis_kendaraan',$item->jenis_kendaraan) }}" class="form-control compact-field"></div>
      <div class="col-md-3"><label class="form-label">Model</label><input name="model_kendaraan" value="{{ old('model_kendaraan',$item->model_kendaraan) }}" class="form-control compact-field"></div>
      <div class="col-md-3"><label class="form-label">Code</label><input name="code_kendaraan" value="{{ old('code_kendaraan',$item->code_kendaraan) }}" class="form-control compact-field"></div>
      <div class="col-md-3"><label class="form-label">Transporter</label><select name="id_transporter" class="form-select combo-select compact-field"><option value="">-- Pilih --</option>@foreach($transporters as $t)<option value="{{ $t->id_transporter }}" {{ $item->id_transporter == $t->id_transporter ? 'selected' : '' }}>{{ $t->nama_transporter }}</option>@endforeach</select></div>
    </div>
    <div class="d-flex justify-content-end mt-4">
      <button class="btn btn-primary compact-button">Simpan</button>
    </div>
  </form>
</div>
@endsection
