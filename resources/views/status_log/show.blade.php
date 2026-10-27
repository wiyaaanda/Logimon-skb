@extends('layouts.app')
@section('content')
  @component('components.admin-page-header', [
      'title' => 'Detail Status Log',
      'description' => 'Riwayat status dan keterangan operasional lengkap untuk pengiriman ini.',
        'eyebrow' => 'Audit'
  ])
  @endcomponent

  <div class="detail-grid">
    <section class="info-card">
      <h3>Informasi Pengiriman</h3>
      <dl class="info-list">
        <div class="info-row"><dt>Driver</dt><dd>{{ $r->driver }}</dd></div>
        <div class="info-row"><dt>Username</dt><dd>{{ $r->username }}</dd></div>
        <div class="info-row"><dt>Dari</dt><dd>{{ $r->dari }}</dd></div>
        <div class="info-row"><dt>Tujuan</dt><dd>{{ $r->tujuan_field }}</dd></div>
        <div class="info-row"><dt>Material</dt><dd>{{ $r->material }}</dd></div>
        <div class="info-row"><dt>Code Material</dt><dd>{{ $r->code_material ?? '-' }}</dd></div>
        <div class="info-row"><dt>No Rec</dt><dd>{{ $r->no_rec ?? '-' }}</dd></div>
      </dl>
    </section>

    <section class="info-card">
      <h3>Status & Timeline</h3>
      <dl class="info-list">
        <div class="info-row"><dt>Waktu Pending</dt><dd>{{ $r->waktu_pending }}</dd></div>
        <div class="info-row"><dt>Waktu Penyelesaian</dt><dd>{{ $r->waktu_penyelesaian }}</dd></div>
        <div class="info-row"><dt>Status Update</dt><dd>{{ $r->status_update }}</dd></div>
        <div class="info-row"><dt>Catatan</dt><dd>{{ $r->keterangan_log ?? '-' }}</dd></div>
      </dl>
    </section>
  </div>
@endsection
