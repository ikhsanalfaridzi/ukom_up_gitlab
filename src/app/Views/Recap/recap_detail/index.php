<?php echo $this->extend('layouts/app') ?>

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
        <!-- <div class="col-lg-4 mb-1">
            <?php
            // $option = 'id="is_grade_active" class="form-select select2"';
            // $list = [
            //     "0" => "Tidak Digunakan",
            //     "1" => "Digunakan",
            // ];

            // echo form_label('Persyaratan Golongan', 'is_grade_active', ['class' => 'form-label']);
            // echo form_dropdown('is_grade_active', $list, "", $option);
            ?>
        </div> -->
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
        <div class="table-responsive">
            <table class="table table-bordered" id="recapTable">
                <thead>
                    <tr>
                        <th rowspan="2" class="text-center align-middle">Ranking</th>
                        <th rowspan="2" class="text-center align-middle" style="min-width: 300px; white-space: nowrap;">Nama Lengkap</th>
                        <th rowspan="2" class="text-center align-middle">Pangkat</th>
                        <th rowspan="2" class="text-center align-middle" style="min-width: 200px; white-space: nowrap;">Umur</th>
                        <th rowspan="2" class="text-center align-middle">Penilai</th>
                        <th colspan="<?php echo count($aspect); ?>" class="text-center align-middle">Pertanyaan</th>
                        <th rowspan="2" class="text-center">Total Skor</th>
                        <th rowspan="2" class="text-center">Hasil Akhir</th>
                        <th rowspan="2" class="text-center">Kategori</th>
                        <th rowspan="2" class="text-center"></th>
                        <th rowspan="2" class="text-center">Waktu Penilaian</th>
                        <th rowspan="2" class="text-center">Operator</th>
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
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<script type="text/javascript">
    $(document).ready(function() {
        const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
        const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));
        let dom = "<B<t>lip>";
        let table = $('#recapTable');
        let url = '<?php echo site_url("recap_detail/data/"); ?>';

        generate_datatables(table, url, dom);

        $('#filter-btn').on('click', function(e) {
            e.preventDefault();

            var tableId = $('#recapTable');
            var positionGrade = $('#position_grade').val();
            // var isGradeActive = $('#is_grade_active').val();

            // var newUrl = '<?php //echo site_url("recap/data/"); 
                                ?>' + positionGrade + "/" + isGradeActive;
            var newUrl = '<?php echo site_url("recap_detail/data/"); ?>' + positionGrade;

            generate_datatables(tableId, newUrl);
        });
    });
</script>

<?php if (!empty(session()->getFlashdata('message'))) : ?>
    <script type="text/javascript">
        Swal.fire({
            position: 'center',
            icon: 'success',
            title: '<?php echo session()->getFlashdata('message'); ?>',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
<?php
    unset($_SESSION['message']);
endif;
?>

<?php if (!empty(session()->getFlashdata('warning'))) : ?>
    <script type="text/javascript">
        Swal.fire({
            position: 'center',
            icon: 'warning',
            title: '<?php echo session()->getFlashdata('warning'); ?>',
            showConfirmButton: false,
            timer: 3000
        });
    </script>
<?php
    unset($_SESSION['warning']);
endif;
?>
<?php echo $this->endSection() ?>