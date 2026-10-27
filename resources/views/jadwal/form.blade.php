@extends('layouts.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
  .ts-wrapper.combo-select {
    padding: 0 !important;
    border: none !important;
    background: transparent !important;
    box-shadow: none !important;
  }
  .ts-wrapper .ts-control {
    min-height: 38px !important;
    border-radius: 8px !important;
    border: 1px solid rgba(148, 163, 184, 0.45) !important;
    background-color: #fff !important;
    padding: 6px 12px !important;
    font-size: 0.875rem !important;
    box-shadow: none !important;
    display: flex;
    align-items: center;
  }
  .ts-wrapper.focus .ts-control {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.15) !important;
  }
  .ts-wrapper .ts-control input {
    font-size: 0.875rem !important;
  }
  .ts-dropdown {
    border-radius: 8px !important;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
    border: 1px solid rgba(148, 163, 184, 0.3) !important;
    z-index: 1050 !important;
  }
  .ts-dropdown .option {
    padding: 8px 12px !important;
    font-size: 0.875rem !important;
  }
  .ts-dropdown .active {
    background-color: #2563eb !important;
    color: #fff !important;
  }
</style>
@endpush

@section('content')
@php $isEdit = isset($jadwal); @endphp

<div class="panel section-panel">
  <form method="POST" action="{{ $isEdit ? route('jadwal.update',$jadwal->id_jadwal) : route('jadwal.store') }}" class="jadwal-form compact-form">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="row g-3">
      <div class="col-md-4"><label class="form-label">Tanggal</label><input type="date" name="tanggal" value="{{ old('tanggal',$jadwal->tanggal ?? '') }}" class="form-control compact-field" required></div>
      <div class="col-md-4"><label class="form-label">Driver</label><select name="id_driver" class="form-select combo-select compact-field"><option value="">Pilih Driver..</option>@foreach($drivers as $d)<option value="{{ $d->id_driver }}" {{ ($jadwal->id_driver ?? '') == $d->id_driver ? 'selected' : '' }}>{{ $d->nama_driver }}</option>@endforeach</select></div>
      <div class="col-md-4"><label class="form-label">No Pol</label><select name="no_pol" class="form-select combo-select compact-field"><option value="">Pilih No Pol..</option>@foreach($kendaraans as $k)<option value="{{ $k->no_pol }}" {{ ($jadwal->no_pol ?? '') == $k->no_pol ? 'selected' : '' }}>{{ $k->no_pol }}</option>@endforeach</select></div>
    </div>

    <div class="row g-3 mt-0">
      <div class="col-md-4"><label class="form-label">Transporter</label><select name="id_transporter" class="form-select combo-select compact-field"><option value="">Pilih Transporter..</option>@foreach($transporters as $t)<option value="{{ $t->id_transporter }}" {{ ($jadwal->id_transporter ?? '') == $t->id_transporter ? 'selected' : '' }}>{{ $t->nama_transporter }}</option>@endforeach</select></div>
      <div class="col-md-4"><label class="form-label">Asal</label><select name="id_asal" class="form-select combo-select compact-field"><option value="">Pilih Asal..</option>@foreach($asals as $a)<option value="{{ $a->id_asal }}" {{ ($jadwal->id_asal ?? '') == $a->id_asal ? 'selected' : '' }}>{{ $a->nama_asal }}</option>@endforeach</select></div>
      <div class="col-md-4"><label class="form-label">Tujuan</label><select name="id_tujuan" class="form-select combo-select compact-field"><option value="">Pilih Tujuan..</option>@foreach($tujuans as $tgt)<option value="{{ $tgt->id_tujuan }}" {{ ($jadwal->id_tujuan ?? '') == $tgt->id_tujuan ? 'selected' : '' }}>{{ $tgt->nama_tujuan }}</option>@endforeach</select></div>
    </div>

    <div class="row g-3 mt-0">
      <div class="col-md-4"><label class="form-label">Material</label><select name="id_material" class="form-select combo-select compact-field"><option value="">Pilih Material..</option>@foreach($materials as $m)<option value="{{ $m->id_material }}" {{ ($jadwal->id_material ?? '') == $m->id_material ? 'selected' : '' }}>{{ $m->nama_material }}</option>@endforeach</select></div>
      <div class="col-md-4"><label class="form-label">Material Code</label><select name="id_kategori_material" class="form-select combo-select compact-field"><option value="">Pilih Material Code..</option>@foreach($kategoriMaterials as $km)<option value="{{ $km->id_kategori_material }}" data-code="{{ $km->code_material }}" {{ old('id_kategori_material', $jadwal->id_kategori_material ?? '') == $km->id_kategori_material ? 'selected' : '' }}>{{ $km->code_material }}</option>@endforeach</select><input type="hidden" name="code_material" id="code-material" value="{{ old('code_material', $jadwal->code_material ?? '') }}"></div>
      <div class="col-md-4"><label class="form-label">Priority</label><select name="priority" class="form-select combo-select compact-field"><option value="normal" {{ ($jadwal->priority ?? '')=='normal' ? 'selected' : '' }}>Normal</option><option value="high" {{ ($jadwal->priority ?? '')=='high' ? 'selected' : '' }}>High (Prioritas)</option><option value="urgent" {{ ($jadwal->priority ?? '')=='urgent' ? 'selected' : '' }}>Urgent (Mendesak)</option></select></div>
    </div>

    <div class="row g-3 mt-0">
      <div class="col-md-3"><label class="form-label">No Rec</label><input name="no_rec" value="{{ old('no_rec', ($jadwal->no_rec ?? null) ?: ($measurement?->no_rec ?: '')) }}" class="form-control compact-field"></div>
      <div class="col-md-3"><label class="form-label">Jam Berangkat</label><input name="jam_berangkat" value="{{ old('jam_berangkat', ($jadwal->jam_berangkat ?? null) ?: ($measurement?->jam_berangkat ?: '')) }}" class="form-control compact-field" type="time"></div>
      <div class="col-md-3"><label class="form-label">Volume (kg)</label><input name="vol_kg" value="{{ old('vol_kg', $measurement?->vol_kg !== null ? number_format($measurement->vol_kg, 0, ',', '.') : '') }}" class="form-control compact-field" type="text" inputmode="numeric"></div>
      <div class="col-md-3"><label class="form-label">Timbangan CV SR</label><input name="timbangan_cv_sr" value="{{ old('timbangan_cv_sr', $measurement?->timbangan_cv_sr !== null ? number_format($measurement->timbangan_cv_sr, 0, ',', '.') : '') }}" class="form-control compact-field" type="text" inputmode="numeric"></div>
    </div>

    <div class="row g-3 mt-0">
      <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select combo-select compact-field"><option value="">Pilih Status..</option><option value="Terjadwal" {{ ($jadwal->status ?? '')=='Terjadwal' ? 'selected' : '' }}>Terjadwal</option><option value="Proses" {{ ($jadwal->status ?? '')=='Proses' ? 'selected' : '' }}>Proses</option><option value="Pending" {{ ($jadwal->status ?? '')=='Pending' ? 'selected' : '' }}>Pending</option><option value="Selesai" {{ ($jadwal->status ?? '')=='Selesai' ? 'selected' : '' }}>Selesai</option></select></div>
      <div class="col-md-4" id="col-catatan"><label class="form-label">Catatan (jika Pending)</label><textarea name="catatan" class="form-control compact-field" rows="1">{{ old('catatan') }}</textarea></div>
    </div>

    <div class="d-flex justify-content-end mt-4">
      <button class="btn btn-primary compact-button">{{ $isEdit ? 'Simpan Perubahan' : 'Simpan' }}</button>
    </div>
  </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function(){
    const codeInput = document.getElementById('code-material');
    const kolCatatan = document.getElementById('col-catatan');

    // Initialize TomSelect on all combo-select elements
    document.querySelectorAll('select.combo-select').forEach(function(selectEl) {
      const ts = new TomSelect(selectEl, {
        create: false,
        allowEmptyOption: true,
        plugins: ['dropdown_input'],
        maxOptions: 500,
        sortField: {
          field: 'text',
          direction: 'asc'
        }
      });

      // Special event handlers
      if (selectEl.name === 'id_kategori_material') {
        ts.on('change', function(val) {
          const opt = selectEl.querySelector('option[value="' + val + '"]');
          if (codeInput) {
            codeInput.value = opt ? (opt.dataset.code || '') : '';
          }
        });
      }

      if (selectEl.name === 'status') {
        ts.on('change', function(val) {
          if (kolCatatan) {
            kolCatatan.style.display = val === 'Pending' ? '' : 'none';
          }
        });
        // initial state
        if (kolCatatan) {
          kolCatatan.style.display = selectEl.value === 'Pending' ? '' : 'none';
        }
      }
    });
  });
</script>
@endpush
@endsection
