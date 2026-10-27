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
    <form method="POST" action="{{ $item->exists ? route('akun_kelola.updateAdmin', $item->id_user) : route('akun_kelola.storeAdmin') }}">
      @csrf
      @if($item->exists) @method('PUT') @endif

      <div class="mb-3">
        <label class="form-label">Nama <span class="text-danger">*</span></label>
        <input name="name" value="{{ old('name', $item->name) }}" class="form-control @error('name') is-invalid @enderror" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      <div class="mb-3">
        <label class="form-label">Email <span class="text-danger">*</span></label>
        <input name="email" type="email" value="{{ old('email', $item->email) }}" class="form-control @error('email') is-invalid @enderror" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
      </div>

      @if($item->exists)
        <div class="mb-3 p-3 bg-light rounded">
          <small class="text-muted">
            <strong>Username:</strong> <code>{{ $item->username }}</code><br>
            <strong>Catatan:</strong> Untuk reset password, gunakan tombol "Reset Pass" di halaman daftar admin
          </small>
        </div>
      @endif

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">{{ $item->exists ? 'Update' : 'Simpan' }}</button>
      </div>
    </form>
  </div>
</div>
@endsection
