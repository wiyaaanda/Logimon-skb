@extends('layouts.app')
@section('content')
<div class="panel section-panel">
  <form method="POST" action="{{ $item->exists ? route('master.driver.update',$item->id_driver) : route('master.driver.store') }}" class="compact-form">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Nama</label><input name="nama_driver" value="{{ old('nama_driver',$item->nama_driver) }}" class="form-control compact-field" required></div>
      <div class="col-md-6"><label class="form-label">No Telp</label><input name="no_telp_driver" value="{{ old('no_telp_driver',$item->no_telp_driver) }}" class="form-control compact-field"></div>
    </div>
    <div class="row g-3 mt-0">
      <div class="col-md-4"><label class="form-label">Tempat</label><input name="tempat_driver" value="{{ old('tempat_driver',$item->tempat_driver) }}" class="form-control compact-field"></div>
      <div class="col-md-4"><label class="form-label">Tanggal Lahir</label><input name="tanggal_lahir_driver" value="{{ old('tanggal_lahir_driver',$item->tanggal_lahir_driver) }}" class="form-control compact-field" type="date"></div>
      <div class="col-md-4"><label class="form-label">SIM</label><input name="sim_driver" value="{{ old('sim_driver',$item->sim_driver) }}" class="form-control compact-field"></div>
    </div>
    <div class="row g-3 mt-0">
      <div class="col-md-6"><label class="form-label">Status Aktif</label><select name="status_aktif_driver" class="form-select combo-select compact-field"><option value="">Pilih status..</option><option value="aktif" {{ $item->status_aktif_driver=='aktif' ? 'selected' : '' }}>AKTIF</option><option value="nonaktif" {{ $item->status_aktif_driver=='nonaktif' ? 'selected' : '' }}>TIDAK AKTIF</option></select></div>
      <div class="col-md-6"><label class="form-label">Transporter</label><select name="id_transporter" class="form-select combo-select compact-field"><option value="">Pilih transporter...</option>@foreach($transporters as $t)<option value="{{ $t->id_transporter }}" {{ $item->id_transporter == $t->id_transporter ? 'selected' : '' }}>{{ $t->nama_transporter }}</option>@endforeach</select></div>
    </div>
    <div class="d-flex justify-content-end mt-4">
      <button class="btn btn-primary compact-button">Simpan</button>
    </div>
  </form>
</div>
@endsection
