<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Laman</h4>

    <!-- Filter Periode -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="<?php echo site_url('home/dashboard'); ?>" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label" for="start">Dari Tanggal</label>
                    <input type="date" class="form-control" id="start" name="start" value="<?php echo esc($filter_start ?? ''); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="end">Sampai Tanggal</label>
                    <input type="date" class="form-control" id="end" name="end" value="<?php echo esc($filter_end ?? ''); ?>">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bx bx-filter-alt"></i> Terapkan</button>
                    <a href="<?php echo site_url('home/dashboard'); ?>" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
            <?php
            $export_query = http_build_query(array_filter([
                'start' => $filter_start ?? '',
                'end'   => $filter_end ?? '',
            ]));
            $export_suffix = $export_query ? '?' . $export_query : '';
            ?>
            <div class="d-flex gap-2 mt-3">
                <a href="<?php echo site_url('home/dashboard/export_excel'); ?><?php echo $export_suffix; ?>" class="btn btn-success btn-sm">
                    <i class="bx bx-file"></i> Export Excel
                </a>
                <a href="<?php echo site_url('home/dashboard/export_pdf'); ?><?php echo $export_suffix; ?>" class="btn btn-danger btn-sm">
                    <i class="bx bxs-file-pdf"></i> Export PDF
                </a>
            </div>
            <small class="text-muted d-block mt-2">
                Menampilkan data kelompok wawancara berdasarkan tanggal dibuat
                <?php if (!empty($filter_start) || !empty($filter_end)) : ?>
                    (<?php echo !empty($filter_start) ? esc($filter_start) : 'awal'; ?> s.d. <?php echo !empty($filter_end) ? esc($filter_end) : 'sekarang'; ?>)
                <?php else : ?>
                    (seluruh periode)
                <?php endif; ?>
            </small>
        </div>
    </div>

    <!-- Navigasi tab dashboard -->
    <ul class="nav nav-pills nav-justified mb-4" id="dashboardTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-ringkasan-btn" data-bs-toggle="pill" data-bs-target="#tab-ringkasan" type="button" role="tab" aria-controls="tab-ringkasan" aria-selected="true">
                <i class="bx bx-pie-chart-alt-2"></i> Ringkasan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-riwayat-btn" data-bs-toggle="pill" data-bs-target="#tab-riwayat" type="button" role="tab" aria-controls="tab-riwayat" aria-selected="false">
                <i class="bx bx-history"></i> Riwayat UKKJ
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-riwayat-pdjl-btn" data-bs-toggle="pill" data-bs-target="#tab-riwayat-pdjl" type="button" role="tab" aria-controls="tab-riwayat-pdjl" aria-selected="false">
                <i class="bx bx-transfer-alt"></i> Riwayat PDJL
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-rasio-btn" data-bs-toggle="pill" data-bs-target="#tab-rasio" type="button" role="tab" aria-controls="tab-rasio" aria-selected="false">
                <i class="bx bx-pie-chart"></i> Rasio Kelulusan
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-analitik-btn" data-bs-toggle="pill" data-bs-target="#tab-analitik" type="button" role="tab" aria-controls="tab-analitik" aria-selected="false">
                <i class="bx bx-map-alt"></i> Analitik Wilayah
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-statistik-btn" data-bs-toggle="pill" data-bs-target="#tab-statistik" type="button" role="tab" aria-controls="tab-statistik" aria-selected="false">
                <i class="bx bx-bar-chart-alt-2"></i> Statistik Pegawai
            </button>
        </li>
    </ul>

    <div class="tab-content" id="dashboardTabsContent">
    <div class="tab-pane fade show active" id="tab-ringkasan" role="tabpanel" aria-labelledby="tab-ringkasan-btn">

    <!-- Ringkasan UKKJ & PDJL -->
    <?php
    $summary_data  = $summary_data ?? ['UKKJ' => [], 'PDJL' => []];
    $summary_types = [
        'UKKJ' => 'UKKJ (Uji Kompetensi Kenaikan Jenjang)',
        'PDJL' => 'PDJL (Perpindahan Dari Jabatan Lain)',
    ];
    ?>
    <div class="row mb-4">
        <?php foreach ($summary_types as $key => $label) :
            $stat = array_merge([
                'pendaftar'     => 0,
                'dinilai'       => 0,
                'lulus'         => 0,
                'tidak_lulus'   => 0,
                'belum_dinilai' => 0,
                'kelompok'      => 0,
            ], $summary_data[$key] ?? []); ?>
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><?php echo $label; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col-4">
                                <h3 class="mb-0" style="color:#3FA2F6;"><?php echo number_format($stat['pendaftar']); ?></h3>
                                <small class="text-muted">Total Pendaftar</small>
                            </div>
                            <div class="col-4">
                                <h3 class="mb-0" style="color:#2DCB73;"><?php echo number_format($stat['lulus']); ?></h3>
                                <small class="text-muted">Lulus (MS)</small>
                            </div>
                            <div class="col-4">
                                <h3 class="mb-0" style="color:#E8575A;"><?php echo number_format($stat['tidak_lulus']); ?></h3>
                                <small class="text-muted">Tidak Lulus</small>
                            </div>
                        </div>
                        <div class="text-center mb-2">
                            <small class="text-muted"><?php echo number_format($stat['kelompok']); ?> kelompok &middot; <?php echo number_format($stat['belum_dinilai']); ?> belum dinilai</small>
                        </div>
                        <div style="position: relative; height: 140px;">
                            <canvas id="chart<?php echo $key; ?>"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    </div><!-- /tab-ringkasan -->

    <div class="tab-pane fade" id="tab-riwayat" role="tabpanel" aria-labelledby="tab-riwayat-btn">

    <!-- Riwayat UKKJ -->
    <?php $ukkj_history_data = $ukkj_history_data ?? ['groups' => [], 'years' => [], 'jenjang' => []]; ?>
    <div class="row mb-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tren Partisipasi UKKJ per Tahun</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 240px;">
                        <canvas id="chartUkkjTrend"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Breakdown Peserta UKKJ per Jenjang Target</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 240px;">
                        <canvas id="chartUkkjJenjang"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Tabel Riwayat UKKJ</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tahun</th>
                        <th>Gelombang</th>
                        <th>Tanggal</th>
                        <th class="text-end">Pendaftar</th>
                        <th class="text-end">Lulus</th>
                        <th class="text-end">Tidak Lulus</th>
                        <th class="text-end">Belum Dinilai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($ukkj_history_data['groups'])) : ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data UKKJ pada periode ini.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($ukkj_history_data['groups'] as $h) : ?>
                            <tr>
                                <td><?php echo esc($h['year']); ?></td>
                                <td><?php echo esc($h['name']); ?></td>
                                <td><?php echo esc($h['date']); ?></td>
                                <td class="text-end"><?php echo number_format($h['pendaftar']); ?></td>
                                <td class="text-end text-success"><?php echo number_format($h['lulus']); ?></td>
                                <td class="text-end text-danger"><?php echo number_format($h['tidak_lulus']); ?></td>
                                <td class="text-end text-muted"><?php echo number_format($h['belum_dinilai']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    </div><!-- /tab-riwayat -->

    <div class="tab-pane fade" id="tab-riwayat-pdjl" role="tabpanel" aria-labelledby="tab-riwayat-pdjl-btn">

    <!-- Riwayat PDJL -->
    <?php $pdjl_history_data = $pdjl_history_data ?? ['groups' => [], 'years' => [], 'jabatan' => []]; ?>
    <div class="row mb-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Tren Partisipasi PDJL per Tahun</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 240px;">
                        <canvas id="chartPdjlTrend"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Distribusi Peserta PDJL per Asal Jabatan</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 240px;">
                        <canvas id="chartPdjlJabatan"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">Tabel Riwayat PDJL</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th>Tahun</th>
                        <th>Gelombang</th>
                        <th>Tanggal</th>
                        <th class="text-end">Pendaftar</th>
                        <th class="text-end">Lulus</th>
                        <th class="text-end">Tidak Lulus</th>
                        <th class="text-end">Belum Dinilai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pdjl_history_data['groups'])) : ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data PDJL pada periode ini.</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($pdjl_history_data['groups'] as $h) : ?>
                            <tr>
                                <td><?php echo esc($h['year']); ?></td>
                                <td><?php echo esc($h['name']); ?></td>
                                <td><?php echo esc($h['date']); ?></td>
                                <td class="text-end"><?php echo number_format($h['pendaftar']); ?></td>
                                <td class="text-end text-success"><?php echo number_format($h['lulus']); ?></td>
                                <td class="text-end text-danger"><?php echo number_format($h['tidak_lulus']); ?></td>
                                <td class="text-end text-muted"><?php echo number_format($h['belum_dinilai']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    </div><!-- /tab-riwayat-pdjl -->

    <div class="tab-pane fade" id="tab-rasio" role="tabpanel" aria-labelledby="tab-rasio-btn">

    <!-- Rasio Kelulusan -->
    <?php
    $ratio_stat        = $ratio_stat ?? [
        'UKKJ' => ['dinilai' => 0, 'lulus' => 0, 'tidak_lulus' => 0],
        'PDJL' => ['dinilai' => 0, 'lulus' => 0, 'tidak_lulus' => 0],
    ];
    $verification_data = $verification_data ?? ['terverifikasi' => 0, 'belum' => 0, 'total' => 0];
    ?>
    <div class="row mb-4">
        <?php foreach (['UKKJ' => 'Rasio Kelulusan UKKJ', 'PDJL' => 'Rasio Kelulusan PDJL'] as $key => $title) :
            $stat = $ratio_stat[$key]; ?>
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><?php echo $title; ?></h5>
                    </div>
                    <div class="card-body">
                        <div style="position: relative; height: 220px;">
                            <canvas id="chartRatio<?php echo $key; ?>"></canvas>
                        </div>
                        <div class="text-center mt-2">
                            <small class="text-muted">
                                Lulus <?php echo number_format($stat['lulus']); ?> &middot;
                                Tidak Lulus <?php echo number_format($stat['tidak_lulus']); ?> &middot;
                                Dinilai <?php echo number_format($stat['dinilai']); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Persentase Kelulusan UKKJ per Tahun</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 220px;">
                        <canvas id="chartRatioYearUKKJ"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Persentase Kelulusan PDJL per Tahun</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 220px;">
                        <canvas id="chartRatioYearPDJL"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Rata-rata Nilai Kompetensi per Jenis Uji</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 220px;">
                        <canvas id="chartAvgComponent"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Verifikasi Berkas Peserta</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 220px;">
                        <canvas id="chartVerification"></canvas>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            Terverifikasi <?php echo number_format($verification_data['terverifikasi']); ?> &middot;
                            Belum <?php echo number_format($verification_data['belum']); ?> &middot;
                            Total <?php echo number_format($verification_data['total']); ?> peserta
                        </small>
                        <?php if (is_authorized('monitoring_verification', 'view')) : ?>
                            <div class="mt-2">
                                <a href="<?php echo site_url('monitoring/verification'); ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bx bx-link-external"></i> Lihat Monitoring Verifikasi
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div><!-- /tab-rasio -->

    <div class="tab-pane fade" id="tab-analitik" role="tabpanel" aria-labelledby="tab-analitik-btn">

    <?php
    $region_breakdown = $region_breakdown_data ?? ['instansi' => [], 'provinsi' => [], 'totals' => []];
    $region_totals    = $region_breakdown['totals'] ?? [];
    ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info mb-0">
                <i class="bx bx-info-circle"></i>
                Sebaran dihitung dari unit kerja mayoritas peserta tiap kegiatan. Kegiatan tanpa unit kerja ter-resolve
                dialihkan ke kode instansi (mis. <code>imipas</code> &rarr; Imipas). Total kegiatan:
                <strong><?php echo number_format((int) ($region_totals['groups'] ?? 0)); ?></strong> &middot;
                Peserta: <strong><?php echo number_format((int) ($region_totals['peserta'] ?? 0)); ?></strong>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Kelulusan per Instansi Pembina</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="chartInstansiKelulusan"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Verifikasi Berkas per Instansi Pembina</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="chartInstansiVerifikasi"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Kelulusan per Provinsi Wilayah Kerja</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="chartProvinsiKelulusan"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Status Verifikasi Berkas per Provinsi Wilayah Kerja</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="chartProvinsiVerifikasi"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div><!-- /tab-analitik -->

    <div class="tab-pane fade" id="tab-statistik" role="tabpanel" aria-labelledby="tab-statistik-btn">

    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pegawai Berdasarkan Tingkatan</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 220px;">
                        <canvas id="chartGrade"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pegawai Berdasarkan Instansi</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 220px;">
                        <canvas id="chartInstance"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Peserta Wawancara per Kelompok</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 200px;">
                        <canvas id="chartKelompok"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Rata-rata Skor per Komponen Penilaian</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 200px;">
                        <canvas id="chartRadar"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div><!-- /tab-statistik -->
    </div><!-- /tab-content -->
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<script src="<?php echo base_url('vlz/libs/chart.js/plugins/chartjs-plugin-datalabels.js'); ?>"></script>
<script type="text/javascript">
$(document).ready(function() {
    Chart.register(ChartDataLabels);

    // Chart.js perlu di-resize saat tab ditampilkan (canvas di tab tersembunyi berukuran 0)
    document.querySelectorAll('#dashboardTabs [data-bs-toggle="pill"]').forEach(function (tab) {
        tab.addEventListener('shown.bs.tab', function () {
            document.querySelectorAll('.tab-pane.active canvas').forEach(function (canvas) {
                const chart = Chart.getChart(canvas);
                if (chart) {
                    chart.resize();
                }
            });
        });
    });

    const palette = ['#3FA2F6', '#2DCB73', '#F5A623', '#E8575A', '#9B59B6', '#1ABC9C', '#E67E22', '#34495E', '#E91E63', '#00BCD4'];

    // Ringkasan UKKJ & PDJL
    const summaryData = <?php echo $summary; ?>;

    function renderSummary(canvasId, stat) {
        new Chart(document.getElementById(canvasId).getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Pendaftar', 'Lulus', 'Tidak Lulus'],
                datasets: [{
                    data: [stat.pendaftar, stat.lulus, stat.tidak_lulus],
                    backgroundColor: ['#3FA2F6', '#2DCB73', '#E8575A'],
                    borderColor: ['#3FA2F6', '#2DCB73', '#E8575A'],
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: '#333',
                        font: { weight: 'bold', size: 11 },
                        formatter: (v) => Number(v).toLocaleString('id-ID')
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0f0f0' } }
                }
            }
        });
    }

    renderSummary('chartUKKJ', summaryData.UKKJ);
    renderSummary('chartPDJL', summaryData.PDJL);

    // Riwayat UKKJ
    const ukkjHistory = <?php echo $ukkj_history; ?>;
    const ukkjYears = Object.keys(ukkjHistory.years || {});

    new Chart(document.getElementById('chartUkkjTrend').getContext('2d'), {
        type: 'bar',
        data: {
            labels: ukkjYears,
            datasets: [
                {
                    label: 'Pendaftar',
                    data: ukkjYears.map(y => ukkjHistory.years[y].pendaftar),
                    backgroundColor: '#3FA2F6CC',
                    borderColor: '#3FA2F6',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Lulus',
                    data: ukkjYears.map(y => ukkjHistory.years[y].lulus),
                    backgroundColor: '#2DCB73CC',
                    borderColor: '#2DCB73',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Tidak Lulus',
                    data: ukkjYears.map(y => ukkjHistory.years[y].tidak_lulus),
                    backgroundColor: '#E8575ACC',
                    borderColor: '#E8575A',
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } },
                datalabels: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0f0f0' } }
            }
        }
    });

    const jenjangLabels = Object.keys(ukkjHistory.jenjang || {});
    const jenjangValues = jenjangLabels.map(k => ukkjHistory.jenjang[k]);
    const jenjangColors = ['#2DCB73', '#3FA2F6', '#F5A623', '#9B59B6', '#E8575A', '#1ABC9C'];

    new Chart(document.getElementById('chartUkkjJenjang').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: jenjangLabels,
            datasets: [{
                data: jenjangValues,
                backgroundColor: jenjangLabels.map((_, i) => jenjangColors[i % jenjangColors.length]),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 11 },
                    formatter: (v, ctx) => {
                        let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        if (!sum) return '';
                        let pct = ((v / sum) * 100).toFixed(1);
                        return pct > 5 ? pct + '%' : '';
                    }
                }
            }
        }
    });

    // Riwayat PDJL
    const pdjlHistory = <?php echo $pdjl_history; ?>;
    const pdjlYears = Object.keys(pdjlHistory.years || {});

    new Chart(document.getElementById('chartPdjlTrend').getContext('2d'), {
        type: 'bar',
        data: {
            labels: pdjlYears,
            datasets: [
                {
                    label: 'Pendaftar',
                    data: pdjlYears.map(y => pdjlHistory.years[y].pendaftar),
                    backgroundColor: '#3FA2F6CC',
                    borderColor: '#3FA2F6',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Lulus',
                    data: pdjlYears.map(y => pdjlHistory.years[y].lulus),
                    backgroundColor: '#2DCB73CC',
                    borderColor: '#2DCB73',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Tidak Lulus',
                    data: pdjlYears.map(y => pdjlHistory.years[y].tidak_lulus),
                    backgroundColor: '#E8575ACC',
                    borderColor: '#E8575A',
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } },
                datalabels: { display: false }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0f0f0' } }
            }
        }
    });

    const pdjlJabatanLabels = Object.keys(pdjlHistory.jabatan || {});
    const pdjlJabatanValues = pdjlJabatanLabels.map(k => pdjlHistory.jabatan[k]);

    new Chart(document.getElementById('chartPdjlJabatan').getContext('2d'), {
        type: 'bar',
        data: {
            labels: pdjlJabatanLabels,
            datasets: [{
                label: 'Peserta',
                data: pdjlJabatanValues,
                backgroundColor: '#9B59B6CC',
                borderColor: '#9B59B6',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'right',
                    color: '#333',
                    font: { weight: 'bold', size: 11 },
                    formatter: (v) => v
                }
            },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f0f0f0' } },
                y: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 10 },
                        callback: function (value) {
                            const label = this.getLabelForValue(value);
                            return label.length > 35 ? label.substring(0, 35) + '...' : label;
                        }
                    }
                }
            }
        }
    });

    // Rasio Kelulusan & Verifikasi
    const ratioData = <?php echo $ratio_data; ?>;
    const ratioYears = <?php echo $ratio_years; ?>;

    function renderRatioDoughnut(canvasId, stat) {
        const lulus = Number(stat.lulus || 0);
        const tidakLulus = Number(stat.tidak_lulus || 0);

        new Chart(document.getElementById(canvasId).getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Lulus', 'Tidak Lulus'],
                datasets: [{
                    data: [lulus, tidakLulus],
                    backgroundColor: ['#2DCB73', '#E8575A'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } },
                    datalabels: {
                        color: '#fff',
                        font: { weight: 'bold', size: 11 },
                        formatter: (v, ctx) => {
                            const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            if (!sum) return '';
                            return ((v / sum) * 100).toFixed(1) + '%';
                        }
                    }
                }
            }
        });
    }

    renderRatioDoughnut('chartRatioUKKJ', ratioData.UKKJ);
    renderRatioDoughnut('chartRatioPDJL', ratioData.PDJL);

    function renderRatioYear(canvasId, years) {
        const labels = Object.keys(years || {});
        const lulusPct = labels.map(y => {
            const d = years[y];
            return d.dinilai ? (d.lulus / d.dinilai) * 100 : 0;
        });
        const tidakPct = labels.map(y => {
            const d = years[y];
            return d.dinilai ? (d.tidak_lulus / d.dinilai) * 100 : 0;
        });

        new Chart(document.getElementById(canvasId).getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Lulus (%)', data: lulusPct, backgroundColor: '#2DCB73CC', borderColor: '#2DCB73', borderWidth: 1 },
                    { label: 'Tidak Lulus (%)', data: tidakPct, backgroundColor: '#E8575ACC', borderColor: '#E8575A', borderWidth: 1 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } },
                    datalabels: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ctx.dataset.label + ': ' + Number(ctx.parsed.y).toFixed(1) + '%'
                        }
                    }
                },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true, max: 100, ticks: { callback: (v) => v + '%' }, grid: { color: '#f0f0f0' } }
                }
            }
        });
    }

    renderRatioYear('chartRatioYearUKKJ', ratioYears.UKKJ);
    renderRatioYear('chartRatioYearPDJL', ratioYears.PDJL);

    const avgComponentData = <?php echo $avg_component_jenis; ?>;
    const avgComponentLabels = [...new Set(avgComponentData.map(d => d.name))];

    function avgComponentValues(jenis) {
        return avgComponentLabels.map(label => {
            const row = avgComponentData.find(d => d.name === label && d.jenis === jenis);
            return row ? parseFloat(row.avg_score) : null;
        });
    }

    new Chart(document.getElementById('chartAvgComponent').getContext('2d'), {
        type: 'bar',
        data: {
            labels: avgComponentLabels.map(label => label.length > 24 ? label.substring(0, 24) + '...' : label),
            datasets: [
                { label: 'UKKJ', data: avgComponentValues('UKKJ'), backgroundColor: '#3FA2F6CC', borderColor: '#3FA2F6', borderWidth: 1, borderRadius: 4 },
                { label: 'PDJL', data: avgComponentValues('PDJL'), backgroundColor: '#9B59B6CC', borderColor: '#9B59B6', borderWidth: 1, borderRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    color: '#333',
                    font: { weight: 'bold', size: 10 },
                    formatter: (v) => (v === null || v === undefined) ? '' : v
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                y: { beginAtZero: true, grid: { color: '#f0f0f0' } }
            }
        }
    });

    const verification = <?php echo $verification; ?>;
    new Chart(document.getElementById('chartVerification').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Terverifikasi', 'Belum Terverifikasi'],
            datasets: [{
                data: [Number(verification.terverifikasi), Number(verification.belum)],
                backgroundColor: ['#2DCB73', '#F5A623'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 11 },
                    formatter: (v, ctx) => {
                        const sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        if (!sum) return '';
                        return ((v / sum) * 100).toFixed(1) + '%';
                    }
                }
            }
        }
    });

    // Analitik Wilayah / Instansi
    const regionBreakdown = <?php echo $region_breakdown; ?>;

    function renderRegionChart(canvasId, bucket, mode) {
        const source = regionBreakdown[bucket] || {};
        let labels = Object.keys(source);
        const maxItems = 10;

        if (labels.length > maxItems) {
            labels = labels.slice(0, maxItems);
        }

        const isVerifikasi = mode === 'verifikasi';
        const keys = isVerifikasi
            ? ['draft', 'menunggu', 'disetujui', 'ditolak']
            : ['lulus', 'tidak_lulus', 'belum_dinilai'];
        const titles = isVerifikasi
            ? ['Draft', 'Menunggu', 'Disetujui', 'Ditolak/Perbaikan']
            : ['Lulus', 'Tidak Lulus', 'Belum Dinilai'];
        const colors = isVerifikasi
            ? ['#8592A3CC', '#FFAB00CC', '#71DD37CC', '#FF3E1DCC']
            : ['#2DCB73CC', '#E8575ACC', '#B0B7C3CC'];
        const borders = isVerifikasi
            ? ['#8592A3', '#FFAB00', '#71DD37', '#FF3E1D']
            : ['#2DCB73', '#E8575A', '#B0B7C3'];

        const datasets = keys.map((key, idx) => ({
            label: titles[idx],
            data: labels.map(label => {
                const row = source[label];

                if (!row) return 0;
                if (isVerifikasi) return Number((row.verifikasi || {})[key] || 0);
                return Number(row[key] || 0);
            }),
            backgroundColor: colors[idx],
            borderColor: borders[idx],
            borderWidth: 1,
        }));

        new Chart(document.getElementById(canvasId).getContext('2d'), {
            type: 'bar',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } },
                    datalabels: { display: false },
                    tooltip: {
                        callbacks: {
                            title: (items) => {
                                const label = items[0].label;
                                const row = source[label];
                                return label + (row ? ' (' + row.peserta + ' peserta)' : '');
                            }
                        }
                    }
                },
                scales: {
                    x: { stacked: true, beginAtZero: true, grid: { color: '#f0f0f0' } },
                    y: { stacked: true, grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });
    }

    renderRegionChart('chartInstansiKelulusan', 'instansi', 'kelulusan');
    renderRegionChart('chartInstansiVerifikasi', 'instansi', 'verifikasi');
    renderRegionChart('chartProvinsiKelulusan', 'provinsi', 'kelulusan');
    renderRegionChart('chartProvinsiVerifikasi', 'provinsi', 'verifikasi');

    // Chart 1: Grade (Horizontal Bar)
    const gradeData = <?php echo $sum_grade; ?>;
    new Chart(document.getElementById('chartGrade').getContext('2d'), {
        type: 'bar',
        data: {
            labels: gradeData.map(d => d.name),
            datasets: [{
                label: 'Jumlah Pegawai',
                data: gradeData.map(d => parseInt(d.total)),
                backgroundColor: gradeData.map((_, i) => palette[i % palette.length] + 'CC'),
                borderColor: gradeData.map((_, i) => palette[i % palette.length]),
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    color: '#333',
                    font: { weight: 'bold', size: 11 },
                    formatter: (v) => v.toLocaleString('id-ID') + ' orang'
                }
            },
            scales: {
                x: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                y: { grid: { display: false } }
            }
        }
    });

    // Chart 2: Instansi (Doughnut)
    const instanceData = <?php echo $sum_instance; ?>;
    new Chart(document.getElementById('chartInstance').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: instanceData.map(d => d.name),
            datasets: [{
                data: instanceData.map(d => parseInt(d.total)),
                backgroundColor: palette.slice(0, instanceData.length),
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '55%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 12, padding: 10, font: { size: 11 } } },
                datalabels: {
                    color: '#fff',
                    font: { weight: 'bold', size: 11 },
                    formatter: (v, ctx) => {
                        let sum = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        let pct = ((v / sum) * 100).toFixed(1);
                        return pct > 5 ? pct + '%' : '';
                    }
                }
            }
        }
    });

    // Chart 3: Kelompok Wawancara (Bar)
    const kelompokData = <?php echo $sum_kelompok; ?>;
    new Chart(document.getElementById('chartKelompok').getContext('2d'), {
        type: 'bar',
        data: {
            labels: kelompokData.map(d => {
                let name = d.name;
                return name.length > 25 ? name.substring(0, 25) + '...' : name;
            }),
            datasets: [{
                label: 'Peserta',
                data: kelompokData.map(d => parseInt(d.total)),
                backgroundColor: '#3FA2F6CC',
                borderColor: '#3FA2F6',
                borderWidth: 1,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: {
                    anchor: 'end',
                    align: 'top',
                    color: '#333',
                    font: { weight: 'bold', size: 11 },
                    formatter: (v) => v + ' orang'
                }
            },
            scales: {
                x: {
                    ticks: { maxRotation: 45, minRotation: 45, font: { size: 10 } },
                    grid: { display: false }
                },
                y: { beginAtZero: true, grid: { color: '#f0f0f0' } }
            }
        }
    });

    // Chart 4: Radar (Skor per Komponen)
    const radarData = <?php echo $avg_score_component; ?>;
    new Chart(document.getElementById('chartRadar').getContext('2d'), {
        type: 'radar',
        data: {
            labels: radarData.map(d => d.name),
            datasets: [{
                label: 'Rata-rata Skor',
                data: radarData.map(d => parseFloat(d.avg_score)),
                backgroundColor: 'rgba(63, 162, 246, 0.2)',
                borderColor: '#3FA2F6',
                borderWidth: 2,
                pointBackgroundColor: '#3FA2F6',
                pointBorderColor: '#fff',
                pointRadius: 5,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                datalabels: {
                    align: 'end',
                    color: '#333',
                    font: { weight: 'bold', size: 11 },
                    formatter: (v) => v
                }
            },
            scales: {
                r: {
                    beginAtZero: true,
                    grid: { color: '#e0e0e0' },
                    angleLines: { color: '#e0e0e0' },
                    pointLabels: { font: { size: 11 } }
                }
            }
        }
    });
});
</script>
<?php echo $this->endSection() ?>
