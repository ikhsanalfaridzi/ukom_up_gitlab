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
        <div class="col">
            <div class="card mb-4">
                <h5 class="card-header">Detail Komponen</h5>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Nama Komponen</div>
                        <div class="col-md-8"><?php echo $data['name'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <table class="table table-bordered" id="aspectTable">
            <thead>
                <tr>
                    <th class="text-center">Aspek Penilaian</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Urutan</th>
                    <th class="text-center">
                        <a href="<?php echo site_url('assessment/component_aspect/form?component_id=' . $data['id']); ?>" class="btn btn-sm btn-icon btn-primary rounded-pill">
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
        let url = '<?php echo site_url("assessment/component_aspect/data/" . $data['id']); ?>'

        generateDataTable('aspectTable', url, {
            columns: [
                { data: 0 },
                { data: 1 },
                { data: 2 },
                { data: 3, orderable: false }
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
                            window.location.reload();
                        }
                    };
                    xHttp.open('POST', '<?php echo site_url('assessment/component_aspect/delete/'); ?>' + id, true);
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
