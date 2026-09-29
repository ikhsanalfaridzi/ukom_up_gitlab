<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h4 class="fw-bold py-3 mb-4">Dashboard</h4>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card" style="background: linear-gradient(135deg, #3FA2F6, #1a73e8); color: whitesmoke;">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="fw-bold" style="color: whitesmoke;"><?php echo number_format($sum_employee); ?></h2>
                        <h6 class="mb-0" style="color: #d4e8fc;">Total Pegawai</h6>
                    </div>
                    <div class="icon">
                        <i class="bx bx-group fs-1" style="color: rgba(255,255,255,0.7);"></i>
                    </div>
                </div>
                <a href="<?php echo site_url('employee/personnel') ?>" class="btn border-0 btn-block" style="background-color: rgba(255,255,255,0.15); color: whitesmoke;">Lihat Detail <i class="bx bx-right-arrow-alt"></i></a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card" style="background: linear-gradient(135deg, #2DCB73, #1a9c54); color: whitesmoke;">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="fw-bold" style="color: whitesmoke;"><?php echo number_format($sum_assessment); ?></h2>
                        <h6 class="mb-0" style="color: #d4f5e2;">Pegawai Dinilai</h6>
                    </div>
                    <div class="icon">
                        <i class="bx bx-check-circle fs-1" style="color: rgba(255,255,255,0.7);"></i>
                    </div>
                </div>
                <a href="<?php echo site_url('assessment/assessment') ?>" class="btn border-0 btn-block" style="background-color: rgba(255,255,255,0.15); color: whitesmoke;">Lihat Detail <i class="bx bx-right-arrow-alt"></i></a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card" style="background: linear-gradient(135deg, #F5A623, #d48b0a); color: whitesmoke;">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="fw-bold" style="color: whitesmoke;"><?php echo number_format($sum_groups); ?></h2>
                        <h6 class="mb-0" style="color: #fde8c8;">Kelompok Wawancara</h6>
                    </div>
                    <div class="icon">
                        <i class="bx bx-folder-open fs-1" style="color: rgba(255,255,255,0.7);"></i>
                    </div>
                </div>
                <a href="<?php echo site_url('interview/interview_group/list') ?>" class="btn border-0 btn-block" style="background-color: rgba(255,255,255,0.15); color: whitesmoke;">Lihat Detail <i class="bx bx-right-arrow-alt"></i></a>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card" style="background: linear-gradient(135deg, #E8575A, #c0392b); color: whitesmoke;">
                <div class="card-body d-flex align-items-center justify-content-between">
                    <div>
                        <h2 class="fw-bold" style="color: whitesmoke;"><?php echo number_format($sum_responses); ?></h2>
                        <h6 class="mb-0" style="color: #f9d4d5;">Jawaban Wawancara</h6>
                    </div>
                    <div class="icon">
                        <i class="bx bx-message-rounded-dots fs-1" style="color: rgba(255,255,255,0.7);"></i>
                    </div>
                </div>
                <a href="<?php echo site_url('interview_employee') ?>" class="btn border-0 btn-block" style="background-color: rgba(255,255,255,0.15); color: whitesmoke;">Lihat Detail <i class="bx bx-right-arrow-alt"></i></a>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pegawai Berdasarkan Tingkatan</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartGrade" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Pegawai Berdasarkan Instansi</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartInstance" height="300"></canvas>
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
                    <canvas id="chartKelompok" height="280"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Rata-rata Skor per Komponen Penilaian</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartRadar" height="280"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<script src="<?php echo base_url('vlz/libs/chart.js/plugins/chartjs-plugin-datalabels.js'); ?>"></script>
<script type="text/javascript">
$(document).ready(function() {
    Chart.register(ChartDataLabels);

    const palette = ['#3FA2F6', '#2DCB73', '#F5A623', '#E8575A', '#9B59B6', '#1ABC9C', '#E67E22', '#34495E', '#E91E63', '#00BCD4'];

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
