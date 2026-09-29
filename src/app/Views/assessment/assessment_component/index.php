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
        <table class="table table-bordered" id="componentTable">
            <thead>
                <tr>
                    <th class="text-center">Nama Komponen</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Urutan</th>
                    <th class="text-center">Deskripsi</th>
                    <th class="text-center">
                        <a href="<?php echo site_url('assessment/assessment_component/form'); ?>" class="btn btn-sm btn-icon btn-primary rounded-pill">
                            <span class="tf-icons bx bx-plus-circle"></span>
                        </a>
                    </th>
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
        let dom = "<<t>lip>";
        let url = '<?php echo site_url("assessment/assessment_component/data"); ?>'

        generateDataTable('componentTable', url, {
            columns: [
                { data: 0 },
                { data: 1 },
                { data: 2 },
                { data: 3 },
                { data: 4, orderable: false }
            ],
            buttons: []
        }, dom);
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
                            window.location = "<?php echo site_url('assessment/assessment_component') ?>";
                        }
                    };
                    xHttp.open('POST', '<?php echo site_url('assessment/assessment_component/delete/'); ?>' + id, true);
                    xHttp.send();
                });
            }
        });
    }

    function delete_associated(id, name) {
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
                            window.location = "<?php echo site_url('assessment/assessment_component') ?>";
                        }
                    };
                    xHttp.open('POST', '<?php echo site_url('assessment/assessment_component/delete_associated/'); ?>' + id, true);
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