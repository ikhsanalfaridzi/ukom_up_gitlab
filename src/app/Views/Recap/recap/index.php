<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('css') ?>
<?php echo $this->include('layouts/partials/datatables-css') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <h1><?php echo $title ?></h1>
    </div>
    <div class="row">
        <table class="table table-bordered" id="recapTable">
            <thead>
                <tr>
                    <th class="text-center">Kelompok Wawancara</th>
                    <th class="text-center">Level Kompetensi</th>
                    <th class="text-center"></th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<?php echo $this->include('layouts/partials/datatables-scripts') ?>
<script type="text/javascript">
    $(document).ready(function() {
        let table = $('#recapTable');
        let url = '<?php echo site_url("recap/recap/data"); ?>'

        generateDataTable('recapTable', url, {
            pageLength: 25,
            columns: [
                { data: 0 },
                { data: 1 },
                { data: 2, orderable: false }
            ],
            buttons: []
        });
    })
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
<?php echo $this->endSection() ?>