<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('css') ?>
<?php echo $this->include('layouts/partials/datatables-css') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <h1><?php echo $title ?></h1>
    </div>

    <!-- Filter -->
    <div class="row mb-3">
        <div class="col-lg-4 mb-1">
            <?php
            $option = 'id="position_grade" class="form-select select2"';
            $list = [
                ""   => "- Silahkan Pilih -",
                "ES1" => "Eselon I",
                "ES2" => "Eselon II",
                "ES3" => "Eselon III",
                "ES4" => "Eselon IV",
                "ES5" => "Eselon V",
                "FUNG" => "Fungsional",
            ];

            echo form_label('Wawancara Untuk', 'position_grade', ['class' => 'form-label']);
            ?>
            <i class="bx bx-info-circle"
                data-bs-toggle="popover"
                data-bs-title="Filter Rules"
                data-bs-content='<b>Jika Filter Eselon I</b>, maka yang muncul Eselon I.a, I.b, II.a.<br> <b>Jika Filter Eselon II</b>, maka yang muncul Eselon II.b dan Eselon III.a.<br> <b>Jika Filter Eselon III</b>, maka yang muncul Eselon III.b dan Eselon IV.a.<br> <b>Jika Filter Eselon IV</b>, maka yang muncul Eselon IV.b, Eselon V, dan Pelaksana.<br> <b>Jika Filter Eselon V</b>, maka yang muncul Pelaksana.<br> <b>Jika Filter Fungsional</b>, maka yang muncul Jabatan Fungsional (Ahli Utama, Ahli Madya, Ahli Muda, Ahli Pertama)'
                data-bs-html="true"
                data-bs-trigger="hover focus"></i>

            <?php echo form_dropdown('position_grade', $list, "", $option); ?>
        </div>
        <div class="col-lg-2 d-flex align-items-end">
            <button id="filter-btn" class="btn btn-primary mb-1">
                <i class='bx bx-filter-alt'></i>
                <span>Filter</span>
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="row">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center py-3">
                <h5 class="card-title mb-0">Daftar Peserta</h5>
                <span class="text-muted small">Hasil berikut tampil sesuai filter eselon yang dipilih</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle" id="tableAssessment">
                        <thead>
                            <tr>
                                <th class="text-center">NIP/NRP</th>
                                <th class="text-center">Nama Lengkap</th>
                                <th class="text-center">Pangkat</th>
                                <th class="text-center">Tingkatan</th>
                                <th class="text-center">Jabatan</th>
                                <th class="text-center" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <select id="dt-entries-select" class="form-select form-control-sm" style="width: auto;">
                        <option value="10">10</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="500">500</option>
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
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<?php echo $this->include('layouts/partials/datatables-scripts') ?>
<script type="text/javascript">
    var table;

    $(document).ready(function() {
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
        var tableId = $('#tableAssessment');
        var url = '<?php echo site_url("assessment/assessment/data/"); ?>'

        generate_datatables(tableId, url);

        $('#filter-btn').on('click', function(e) {
            e.preventDefault();

            var tableId = $('#tableAssessment');
            var positionGrade = $('#position_grade').val();

            if (positionGrade === '') {
                showToast('error', 'Silahkan pilih eselon terlebih dahulu');
                return;
            }

            var newUrl = '<?php echo site_url("assessment/assessment/data/"); ?>' + positionGrade;

            // Destroy dulu instance lama sebelum re-init
            if ($.fn.DataTable.isDataTable(tableId)) {
                tableId.DataTable().destroy();
            }

            generate_datatables(tableId, newUrl);
        });
    })
</script>
<?php echo $this->endSection() ?>