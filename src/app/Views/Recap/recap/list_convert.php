<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('css') ?>
<?php echo $this->include('layouts/partials/datatables-css') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <a href="<?php echo site_url('recap/recap') ?>" class="btn btn-primary mb-3">Kembali</a>

    <div class="row mb-4">
        <div class="col-md mb-4 mb-md-0">
            <div class="card">
                <h5 class="card-header">Detail</h5>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Kelompok Wawancara</div>
                        <div class="col-md-8"><?php echo $group['name'] ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Level Kompetensi</div>
                        <div class="col-md-8"><?php echo $group['level'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md mb-4 mb-md-0">
            <div class="card">
                <h5 class="card-header"><?php echo $title ?></h5>
                <div class="card-body">
                    <div class="alert alert-secondary" role="alert">
                        <h6 class="text-primary font-weight-bold mb-2"><i class='bx bx-info-circle me-2'></i></i>Ketentuan Pemeringkatan</h6>
                        <p class="mb-1"><i class="bi bi-sort-numeric-down me-2"></i>Diurutkan berdasarkan nilai tertinggi</p>
                        <p class="mb-1"><i class="bi bi-chevron-bar-up me-2"></i>Jika nilai sama, urutkan berdasarkan pangkat tertinggi</p>
                        <p class="mb-0"><i class="bi bi-calendar3 me-2"></i>Jika nilai dan pangkat sama, urutkan berdasarkan umur tertua</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="recapListTable">
                            <thead>
                                <tr>
                                    <th rowspan="2" class="text-center align-middle">Ranking</th>
                                    <th rowspan="2" class="text-center align-middle" style="min-width: 300px; white-space: nowrap;">Nama Lengkap</th>
                                    <th rowspan="2" class="text-center align-middle">Pangkat</th>
                                    <th rowspan="2" class="text-center align-middle" style="min-width: 200px; white-space: nowrap;">Umur</th>
                                    <th colspan="<?php echo count($aspect); ?>" class="text-center align-middle">Pertanyaan</th>
                                    <th rowspan="2" class="text-center">Total Skor</th>
                                    <th rowspan="2" class="text-center">Hasil Akhir</th>
                                    <th rowspan="2" class="text-center">Kategori</th>
                                    <th rowspan="2" class="text-center"></th>
                                </tr>
                                <tr>
                                    <?php foreach ($aspect as $index => $item) : ?>
                                        <th
                                            class="text-center"
                                            data-bs-toggle="popover"
                                            data-bs-title="Pertanyaan"
                                            data-bs-content="<?php echo $item['question'] ?>">
                                            <?php echo "P" . $index + 1 ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<?php echo $this->include('layouts/partials/datatables-scripts') ?>
<script type="text/javascript">
    $(document).ready(function() {
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
        let dom = "<B<t>lip>";
        let url = '<?php echo site_url("recap/recap/list_data_convert/" . $interview_group_id); ?>';

        let columns = [
            { data: 0 },
            { data: 1 },
            { data: 2 },
            { data: 3 },
        ];
        <?php foreach ($aspect as $i => $item) : ?>
        columns.push({ data: <?php echo 4 + $i ?> });
        <?php endforeach; ?>
        columns.push({ data: <?php echo 4 + count($aspect) ?> });
        columns.push({ data: <?php echo 5 + count($aspect) ?> });
        columns.push({ data: <?php echo 6 + count($aspect) ?> });
        columns.push({ data: <?php echo 7 + count($aspect) ?>, orderable: false });

        generateDataTable('recapListTable', url, { pageLength: 25, columns: columns, buttons: [] }, dom);
    });
</script>
<?php echo $this->endSection() ?>