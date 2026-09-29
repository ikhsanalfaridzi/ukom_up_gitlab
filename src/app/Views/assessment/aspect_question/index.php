<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('css') ?>
<?php echo $this->include('layouts/partials/datatables-css') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <a href="<?php echo site_url('assessment/component_aspect/list/' . $data['assessment_component_id']) ?>" class="btn btn-primary mb-3">Kembali</a>
    <!-- Detail -->
    <div class="row mb-4">
        <div class="col-md mb-4 mb-md-0">
            <div class="card">
                <h5 class="card-header">Detail</h5>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Nama Komponen</div>
                        <div class="col-md-8"><?php echo $data['name'] ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Aspek Penilaian</div>
                        <div class="col-md-8"><?php echo $data['aspect'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Unsur Komponen -->
    <div class="row mb-4">
        <div class="col-md mb-4 mb-md-0">
            <div class="card">
                <h5 class="card-header">Pertanyaan</h5>
                <div class="card-body">
                    <table class="table table-bordered" id="questionTable">
                        <thead>
                            <tr>
                                <th class="text-center">Pertanyaan</th>
                                <th class="text-center">Kriteria Penilaian/Indikator Penilaian</th>
                                <th class="text-center">Level</th>
                                <th class="text-center">Urutan</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">
                                    <a href="<?php echo site_url('assessment/aspect_question/form/' . $data['id']); ?>" class="btn btn-sm btn-icon btn-primary rounded-pill">
                                        <span class="tf-icons bx bx-plus-circle"></span>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                    </table>
                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label mb-0">Show</label>
                            <select id="dt-entries-select" class="form-select form-select-sm w-auto">
                                <option value="10">10</option>
                                <option value="25" selected>25</option>
                                <option value="100">100</option>
                                <option value="1000">1000</option>
                            </select>
                            <label class="form-label mb-0">entries</label>
                            <div id="dt-showing-entries" class="text-muted"></div>
                        </div>
                        <nav><ul id="dt-pagination" class="pagination pagination-sm mb-0"></ul></nav>
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
        let dom = "<<t>lip>";
        let table = $('#questionTable');
        let url = '<?php echo site_url("assessment/aspect_question/data/" . $data['id']); ?>'

        generate_datatables(table, url, dom);
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
                            window.location = "<?php echo site_url('assessment/aspect_question/list/' . $data['id']) ?>";
                        }
                    };
                    xHttp.open('POST', '<?php echo site_url('assessment/aspect_question/delete/'); ?>' + id, true);
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
                            window.location = "<?php echo site_url('assessment/aspect_question/list/' . $data['id']) ?>";
                        }
                    };
                    xHttp.open('POST', '<?php echo site_url('assessment/aspect_question/delete_associated/'); ?>' + id, true);
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