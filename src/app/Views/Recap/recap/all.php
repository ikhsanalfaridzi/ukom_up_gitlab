<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('css') ?>
<?php echo $this->include('layouts/partials/datatables-css') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <h1><?php echo $title ?></h1>
    </div>
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
                "FUNG" => "Keseluruhan",
            ];

            echo form_label('Wawancara Untuk', 'position_grade', ['class' => 'form-label']);
            ?>
            <i class="bx bx-info-circle"
                data-bs-toggle="popover"
                data-bs-title="Filter Rules"
                data-bs-content='<b>Jika Filter Eselon I</b>, maka yang muncul Eselon I.a, I.b, II.a.<br> <b>Jika Filter Eselon II</b>, maka yang muncul Eselon II.b dan Eselon III.a.<br> <b>Jika Filter Eselon III</b>, maka yang muncul Eselon III.b dan Eselon IV.a.<br> <b>Jika Filter Eselon IV</b>, maka yang muncul Eselon IV.b, Eselon V, dan Pelaksana.<br> <b>Jika Filter Eselon V</b>, maka yang muncul Pelaksana.<br> <b>Jika Filter Keseluruhan</b>, maka yang muncul seluruh pegawai (Eselon I s.d. V dan Fungsional)'
                data-bs-html="true"
                data-bs-trigger="hover focus"></i>
            <?php echo form_dropdown('position_grade', $list, "", $option); ?>
        </div>
        <div class="col-lg-2 d-flex justify-content-between align-items-center">
            <button id="filter-btn" class="text-center btn btn-primary flex mt-4 gap-3">
                <i class='bx bx-filter-alt'></i>
                <span>Filter</span>
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-secondary" role="alert">
                <h6 class="text-primary font-weight-bold mb-2"><i class='bx bx-info-circle me-2'></i></i>Ketentuan Pemeringkatan</h6>
                <p class="mb-1"><i class="bi bi-sort-numeric-down me-2"></i>Diurutkan berdasarkan nilai tertinggi</p>
                <p class="mb-1"><i class="bi bi-chevron-bar-up me-2"></i>Jika nilai sama, urutkan berdasarkan pangkat tertinggi</p>
                <p class="mb-0"><i class="bi bi-calendar3 me-2"></i>Jika nilai dan pangkat sama, urutkan berdasarkan umur tertua</p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="table-responsive" id="recapTable"></div>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<?php echo $this->include('layouts/partials/datatables-scripts') ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('#filter-btn').on('click', async function(e) {
            e.preventDefault();

            let positionGrade = $('#position_grade').val();

            try {
                // Fetch Aspect
                let response = await fetch('<?php echo site_url("recap/recap/aspect/"); ?>' + positionGrade);
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                let aspects = await response.json();

                let url = '<?php echo site_url("recap/recap/all_data/"); ?>' + positionGrade;
                let dom = "<B<t>>";

                generate_recap_table('recapTable', 'recapAllTable', url, dom, aspects);
            } catch (error) {
                console.error('Error fetching aspects:', error);
                alert('Terjadi kesalahan saat memuat data aspek. Silakan coba lagi.');
            }
        });
    });
</script>

<?php echo $this->endSection() ?>