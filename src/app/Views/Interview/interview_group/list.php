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
        <table class="table table-bordered" id="groupTable">
            <thead>
                <tr>
                    <th class="text-center">Kelompok Wawancara</th>
                    <th class="text-center">Level Kompetensi</th>
                    <th class="text-center"></th>
                </tr>
            </thead>
        </table>
    </div>
    <div class="d-flex justify-content-between align-items-center mt-2">
        <select id="dt-entries-select" class="form-select form-control-sm" style="width: auto;">
            <option value="10">10</option>
            <option value="25" selected>25</option>
            <option value="100">100</option>
            <option value="1000">1000</option>
        </select>
        <div class="d-flex flex-column">
            <div id="dt-pagination" class="d-flex justify-content-end"></div>
            <span id="dt-showing-entries" class="d-flex justify-content-end"></span>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<?php echo $this->include('layouts/partials/datatables-scripts') ?>
<script type="text/javascript">
    $(document).ready(function() {
        let table = $('#groupTable');
        let url = '<?php echo site_url("interview/interview_group/list_data"); ?>'

        generateDataTable('groupTable', url, {
            pageLength: 25,
            columns: [
                { data: 0 },
                { data: 1 },
                { data: 2, orderable: false }
            ],
            buttons: []
        });
    })

    function delete_data(id, name) {
        Swal.fire({
            title: 'Apakah kamu yakin?',
            text: "kamu akan menghapus " + name + ".",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'btn btn-primary',
            cancelButtonColor: 'btn btn-secondary',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire(
                    'Terhapus!',
                    'Data berhasil dihapus.',
                    'success'
                ).then(function() {
                    let xHttp = new XMLHttpRequest();
                    xHttp.onreadystatechange = () => {
                        if (xHttp.readyState == 4 && xHttp.status == 200) {
                            table.ajax.reload();
                        }
                    };
                    xHttp.open('POST', '<?php echo site_url('interview/interview_group/delete/'); ?>' + id, true);
                    xHttp.send();
                });
            }
        });
    }
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