<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>SKBtrans Monitoring</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="landing-page-body">
        <div class="landing-shell">
            <main class="landing-hero">
                <div class="hero-copy">
                    <!-- Logo diletakkan di atas badge dengan kontrol ukuran kecil -->
                    <div class="landing-brand" aria-label="SKB Trans logo">
                        <img src="{{ asset('images/logo.png') }}" alt="SKBTrans" style="height: 44px; width: auto; object-fit: contain;" />
                    </div>

                    <span class="hero-badge">Operations System</span>
                    <h1>Monitoring transportasi dan pengiriman.</h1>
                    <p>
                        SKBTrans membantu dan memantau jadwal pengiriman, status peniriman,
                        material, dan real-time dalam satu dashboard terintegrasi.
                    </p>

                    <div class="hero-actions">
                        <a href="{{ route('login') }}" class="primary-cta">Masuk</a>
                    </div>

                    <ul class="hero-metrics" aria-label="Key performance numbers">
                        <li>
                            <strong>24/7</strong>
                            <span>Monitoring</span>
                        </li>
                        <li>
                            <strong>99.9%</strong>
                            <span>Data terintegrasi</span>
                        </li>
                        <li>
                            <strong>Live</strong>
                            <span>Status real-time</span>
                        </li>
                    </ul>
                </div>

                <div class="hero-panel" aria-label="Operational summary panel">
                    <div class="panel-topbar">
                        <span class="dot dot-warning"></span>
                        <span class="dot dot-success"></span>
                        <span class="dot dot-danger"></span>
                    </div>

                    <div class="panel-body">
                        <div class="panel-head">
                            <div>
                                <small>Ringkasan Operasional</small>
                                <h3>Hari ini</h3>
                            </div>
                            <span class="mini-badge success">Live</span>
                        </div>

                        <div class="summary-grid">
                            <div class="summary-card">
                                <span>Total jadwal</span>
                                <strong>{{ number_format($operationalSummary['total'] ?? 0) }}</strong>
                            </div>
                            <div class="summary-card accent">
                                <span>Dalam proses</span>
                                <strong>{{ number_format($operationalSummary['proses'] ?? 0) }}</strong>
                            </div>
                            <div class="summary-card">
                                <span>Selesai</span>
                                <strong>{{ number_format($operationalSummary['selesai'] ?? 0) }}</strong>
                            </div>
                            <div class="summary-card warning">
                                <span>Pending</span>
                                <strong>{{ number_format($operationalSummary['pending'] ?? 0, 0, ',', '.') }}</strong>
                            </div>
                        </div>

                        <div class="mini-table">
                            @forelse($recentSchedules as $schedule)
                                @php
                                    $statusClass = match ($schedule['status']) {
                                        'Selesai' => 'success',
                                        'Pending' => 'danger',
                                        'Proses' => 'warning',
                                        default => 'info',
                                    };
                                @endphp
                                <div class="mini-row">
                                    <span>{{ $schedule['reference'] }}</span>
                                    <span class="mini-pill {{ $statusClass }}">{{ $schedule['status'] }}</span>
                                </div>
                            @empty
                                <div class="mini-row">
                                    <span>Belum ada jadwal hari ini</span>
                                    <span class="mini-pill info">Kosong</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </main>

            <section class="feature-section" id="features">
                <div class="section-heading">
                    <span class="section-eyebrow">Fitur</span>
                    <h2>Semua kebutuhan operasional dalam satu ekosistem</h2>
                </div>

                <div class="feature-grid">
                    <article class="feature-card">
                        <div class="feature-icon">01</div>
                        <h3>Jadwal Pengiriman</h3>
                        <p>Perencanaan pengiriman, prioritas, dan progress status tersusun rapi untuk tim lapangan.</p>
                    </article>
                    <article class="feature-card">
                        <div class="feature-icon">02</div>
                        <h3>Tracking Real-Time</h3>
                        <p>Deteksi status aktif, tertunda, dan selesai dengan timeline yang mudah dibaca.</p>
                    </article>
                    <article class="feature-card">
                        <div class="feature-icon">03</div>
                        <h3>Rekap Material</h3>
                        <p>Laporan keluar masuk material terdokumentasi dengan format yang siap cetak dan ekspor.</p>
                    </article>
                </div>
            </section>

            <footer class="landing-footer" id="contact">
                <div>
                    <strong>SKBTrans</strong>
                    <span>Your partner in industrial service</span>
                </div>
                <a href="{{ route('login') }}" class="footer-cta">Masuk now</a>
            </footer>
        </div>
    </body>
</html>
