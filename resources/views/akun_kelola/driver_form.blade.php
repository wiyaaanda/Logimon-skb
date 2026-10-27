@extends('layouts.app')
@section('content')
<div class="mb-3">
</div>

@if($errors->any())
  <div class="alert alert-danger">
    <ul class="mb-0">
      @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
@endif

<div class="card">
  <div class="card-body">
    <form method="POST" action="{{ $item->exists ? route('akun_kelola.updateDriver', $item->id_user) : route('akun_kelola.storeDriver') }}">
      @csrf
      @if($item->exists) @method('PUT') @endif

      @if($item->exists)
        <div class="mb-3">
          <label class="form-label">Nama Driver <span class="text-danger">*</span></label>
          <input name="nama_driver" value="{{ old('nama_driver', $item->name) }}" class="form-control @error('nama_driver') is-invalid @enderror" required>
          @error('nama_driver') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
      @else
        <div class="mb-3">
          <label class="form-label">Nama Driver <span class="text-danger">*</span></label>
          <input name="nama_driver" value="{{ old('nama_driver') }}" class="form-control @error('nama_driver') is-invalid @enderror" required>
          @error('nama_driver') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
      @endif

      @if($item->exists)
        <div class="mb-3 p-3 bg-light rounded">
          <small class="text-muted">
            <strong>Username:</strong> <code>{{ $item->username }}</code><br>
            <strong>Status:</strong> <span class="badge bg-success">Aktif</span><br>
            <strong>Catatan:</strong> Untuk mengubah status atau reset password, gunakan tombol di halaman daftar driver
          </small>
        </div>
      @else
        <div class="alert alert-info">
          <small>Username dan password akan di-generate otomatis setelah driver dibuat.</small>
        </div>
      @endif

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $item->exists ? 'Update' : 'Simpan' }}</button>
      </div>
    </form>
  </div>
</div>
@endsection
