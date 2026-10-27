@extends('layouts.app')
@section('content')
<div class="panel section-panel">
  @if($errors->any())<div class="alert alert-danger">{{ implode(', ', $errors->all()) }}</div>@endif
  <form method="POST" action="{{ $item->exists ? route('master.transporter.update',$item->id_transporter) : route('master.transporter.store') }}" class="compact-form">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Nama</label><input name="nama_transporter" value="{{ old('nama_transporter',$item->nama_transporter) }}" class="form-control compact-field" required></div>
      <div class="col-md-6"><label class="form-label">No Telp</label><input name="no_telp_transporter" value="{{ old('no_telp_transporter',$item->no_telp_transporter) }}" class="form-control compact-field"></div>
    </div>
    <div class="row g-3 mt-0">
      <div class="col-md-4"><label class="form-label">Tempat</label><input name="tempat_transporter" value="{{ old('tempat_transporter',$item->tempat_transporter) }}" class="form-control compact-field"></div>
      <div class="col-md-4"><label class="form-label">Email</label><input name="email_transporter" value="{{ old('email_transporter',$item->email_transporter) }}" class="form-control compact-field" type="email"></div>
      <div class="col-md-4"><label class="form-label">PIC</label><input name="pic_transporter" value="{{ old('pic_transporter',$item->pic_transporter) }}" class="form-control compact-field"></div>
    </div>
    <div class="d-flex justify-content-end mt-4">
      <button class="btn btn-primary compact-button">Simpan</button>
    </div>
  </form>
</div>
@endsection
