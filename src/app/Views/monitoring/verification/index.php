<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('css') ?>
<?php echo $this->include('layouts/partials/datatables-css') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h1 class="mb-0"><?php echo $title ?></h1>
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="autoRefresh" checked>
                <label class="form-check-label" for="autoRefresh">Real-time (30 detik)</label>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label" for="filterStart">Tanggal Mulai</label>
                    <input type="date" class="form-control" id="filterStart" value="<?php echo esc($filter_start ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="filterEnd">Tanggal Selesai</label>
                    <input type="date" class="form-control" id="filterEnd" value="<?php echo esc($filter_end ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="filterJenis">Jenis Uji</label>
                    <select class="form-select" id="filterJenis">
                        <option value="">Semua</option>
                        <option value="UKKJ" <?php echo ($filter_jenis ?? '') === 'UKKJ' ? 'selected' : '' ?>>UKKJ</option>
                        <option value="PDJL" <?php echo ($filter_jenis ?? '') === 'PDJL' ? 'selected' : '' ?>>PDJL</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-primary w-100" id="btnApply">
                        <i class="bx bx-filter-alt"></i> Terapkan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <?php
        $cards = [
            'draft'     => ['Draft', 'secondary', 'bx bx-edit'],
            'menunggu'  => ['Menunggu Verifikasi', 'warning', 'bx bx-time-five'],
            'disetujui' => ['Disetujui', 'success', 'bx bx-check-circle'],
            'ditolak'   => ['Ditolak/Perbaikan', 'danger', 'bx bx-x-circle'],
        ];
        foreach ($cards as $key => [$label, $color, $icon]) : ?>
            <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
                <div class="card h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted d-block mb-1"><?php echo $label ?></span>
                            <h3 class="mb-0" id="count<?php echo ucfirst($key) ?>"><?php echo number_format((int) ($summary[$key] ?? 0)) ?></h3>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-<?php echo $color ?>">
                                <i class="<?php echo $icon ?> bx-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row mb-3">
        <div class="col-lg-5 mb-3 mb-lg-0">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Diagram Status Berkas</h5>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 240px;">
                        <canvas id="chartVerificationStatus"></canvas>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">Total kegiatan: <span id="countTotal"><?php echo number_format((int) ($summary['total'] ?? 0)) ?></span></small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Rincian Pengusul Kegiatan</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2 mb-3" id="statusFilter">
                        <button type="button" class="btn btn-sm btn-outline-secondary active" data-status="">Semua</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-status="draft">Draft</button>
                        <button type="button" class="btn btn-sm btn-outline-warning" data-status="menunggu">Menunggu Verifikasi</button>
                        <button type="button" class="btn btn-sm btn-outline-success" data-status="disetujui">Disetujui</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" data-status="ditolak">Ditolak/Perbaikan</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="verificationTable" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Kegiatan</th>
                                    <th class="text-center">Jenis</th>
                                    <th>Pengusul / Unit</th>
                                    <th class="text-center">Peserta</th>
                                    <th class="text-center">Terverifikasi</th>
                                    <th class="text-center">Tanggal</th>
                                    <th class="text-center">Status Berkas</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <select id="dt-entries-select" class="form-select form-control-sm" style="width: auto;">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="100">100</option>
                        </select>
                        <div class="d-flex flex-column">
                            <div id="dt-pagination" class="d-flex justify-content-end"></div>
                            <span id="dt-showing-entries" class="d-flex justify-content-end"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<?php echo $this->include('layouts/partials/datatables-scripts') ?>
<script src="<?php echo base_url('vlz/libs/chart.js/plugins/chartjs-plugin-datalabels.js'); ?>"></script>
<script type="text/javascript">
    let currentStatus = '';
    let verificationTable = null;
    let verificationChart = null;

    const dataUrl = '<?php echo site_url('monitoring/verification/data') ?>';
    const statusUrl = '<?php echo site_url('monitoring/verification/status') ?>';
    const updateUrl = '<?php echo site_url('monitoring/verification/update_status') ?>';

    function filterParams() {
        return {
            start_date: $('#filterStart').val(),
            end_date: $('#filterEnd').val(),
            jenis: $('#filterJenis').val(),
        };
    }

    function refreshSummary() {
        $.getJSON(statusUrl, {
            start: $('#filterStart').val(),
            end: $('#filterEnd').val(),
            jenis: $('#filterJenis').val(),
        }).done(function(res) {
            if (verificationChart) {
                verificationChart.data.datasets[0].data = [
                    Number(res.draft || 0),
                    Number(res.menunggu || 0),
                    Number(res.disetujui || 0),
                    Number(res.ditolak || 0)
                ];
                verificationChart.update();
            }

            $('#countDraft').text(Number(res.draft || 0).toLocaleString('id-ID'));
            $('#countMenunggu').text(Number(res.menunggu || 0).toLocaleString('id-ID'));
            $('#countDisetujui').text(Number(res.disetujui || 0).toLocaleString('id-ID'));
            $('#countDitolak').text(Number(res.ditolak || 0).toLocaleString('id-ID'));
            $('#countTotal').text(Number(res.total || 0).toLocaleString('id-ID'));
        });
    }

    function refreshAll() {
        refreshSummary();
        if (verificationTable) {
            verificationTable.ajax.reload(null, false);
        }
    }

    $(document).ready(function() {
        generateDataTable('verificationTable', dataUrl, {
            pageLength: 25,
            columns: [
                { data: 'name' },
                { data: 'jenis', className: 'text-center' },
                { data: 'pengusul' },
                { data: 'peserta', className: 'text-center' },
                { data: 'terverifikasi', className: 'text-center' },
                { data: 'created_at', className: 'text-center' },
                { data: 'status', className: 'text-center' },
                { data: 'action', orderable: false, className: 'text-center' }
            ],
            buttons: []
        });

        verificationTable = $('#verificationTable').DataTable();

        verificationTable.on('preXhr.dt', function(e, settings, data) {
            const params = filterParams();
            data.start_date = params.start_date;
            data.end_date = params.end_date;
            data.jenis = params.jenis;
            data.status = currentStatus;
        });

        verificationTable.ajax.reload(null, false);

        const chartEl = document.getElementById('chartVerificationStatus');
        const initial = <?php echo json_encode([
            'draft'     => (int) ($summary['draft'] ?? 0),
            'menunggu'  => (int) ($summary['menunggu'] ?? 0),
            'disetujui' => (int) ($summary['disetujui'] ?? 0),
            'ditolak'   => (int) ($summary['ditolak'] ?? 0),
        ]); ?>;

        verificationChart = new Chart(chartEl.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Draft', 'Menunggu Verifikasi', 'Disetujui', 'Ditolak/Perbaikan'],
                datasets: [{
                    data: [initial.draft, initial.menunggu, initial.disetujui, initial.ditolak],
                    backgroundColor: ['#8592A3', '#FFAB00', '#71DD37', '#FF3E1D'],
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
                        formatter: function(value, ctx) {
                            const sum = ctx.dataset.data.reduce(function(a, b) { return a + b; }, 0);
                            if (!sum || !value) return '';
                            return ((value / sum) * 100).toFixed(1) + '%';
                        }
                    }
                }
            }
        });

        $('#btnApply').on('click', function() {
            refreshAll();
        });

        $('#filterJenis').on('change', function() {
            refreshAll();
        });

        $('#statusFilter').on('click', 'button[data-status]', function() {
            $('#statusFilter button').removeClass('active');
            $(this).addClass('active');
            currentStatus = $(this).data('status') || '';
            verificationTable.ajax.reload(null, false);
        });

        $(document).on('change', '.status-select', function() {
            const select = $(this);
            const id = select.data('id');
            const status = select.val();

            $.ajax({
                url: updateUrl + '/' + id,
                type: 'POST',
                dataType: 'json',
                data: { status: status },
            }).done(function(res) {
                if (res && res.success) {
                    showAlert('Berhasil', res.message, 'success');
                    refreshAll();
                } else {
                    showAlert('Gagal', (res && res.message) || 'Gagal memperbarui status.', 'error');
                    verificationTable.ajax.reload(null, false);
                }
            }).fail(function() {
                showAlert('Gagal', 'Terjadi kesalahan saat memperbarui status.', 'error');
                verificationTable.ajax.reload(null, false);
            });
        });

        setInterval(function() {
            if ($('#autoRefresh').is(':checked')) {
                refreshAll();
            }
        }, 30000);
    });
</script>
<?php echo $this->endSection() ?>
