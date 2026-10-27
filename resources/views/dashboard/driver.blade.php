@extends('layouts.app')

@section('content')
  <div class="page-header">
    <h2>Ringkasan Driver</h2>
  </div>

  <div class="metric-grid">
    <article class="metric-card">
      <div class="label">
        <span>Jadwal Hari Ini</span>
        <span class="trend">3 tugas</span>
      </div>
      <div class="value">03</div>
    </article>

    <article class="metric-card">
      <div class="label">
        <span>Status Selesai</span>
        <span class="trend">+8%</span>
      </div>
      <div class="value">02</div>
    </article>

    <article class="metric-card">
      <div class="label">
        <span>Dalam Proses</span>
        <span class="trend">Watch</span>
      </div>
      <div class="value">01</div>
    </article>

    <article class="metric-card">
      <div class="label">
        <span>Alarm</span>
        <span class="trend">1 issue</span>
      </div>
      <div class="value">01</div>
    </article>
  </div>

  <section class="panel section-panel">
    <div class="section-header">
      <h3>Aktivitas terbaru</h3>
      <span class="status-pill info">Live</span>
    </div>

    <table class="table-modern">
      <thead>
        <tr>
          <th>Pengiriman</th>
          <th>Tujuan</th>
          <th>Waktu</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>TRX-2048</td>
          <td>Jakarta Selatan</td>
          <td>08:30</td>
          <td><span class="status-pill success">Selesai</span></td>
        </tr>
        <tr>
          <td>TRX-2051</td>
          <td>Bekasi Timur</td>
          <td>11:15</td>
          <td><span class="status-pill warning">Proses</span></td>
        </tr>
        <tr>
          <td>TRX-2057</td>
          <td>Bandung</td>
          <td>14:40</td>
          <td><span class="status-pill danger">Terlambat</span></td>
        </tr>
      </tbody>
    </table>
  </section>
@endsection
