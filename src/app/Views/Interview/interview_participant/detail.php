<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('css') ?>
<?php echo $this->include('layouts/partials/datatables-css') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header Section -->
    <div class="card border-0 mb-4 overflow-hidden">
        <div class="card-body bg-primary p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bx bxs-user-account text-white fs-3"></i>
                        <h3 class="mb-0 text-white"><?php echo $data['name'] ?></h3>
                    </div>
                    <p class="mb-0 text-white-50"><i class="bx bx-folder-open me-1"></i> Kelompok Wawancara / Detail Peserta</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo previous_url() ?>" class="btn btn-outline-light">
                        <i class="bx bx-arrow-back me-1"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="row mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm mb-3 mb-xl-0">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="avatar-md rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center flex-shrink-0">
                        <i class="bx bxs-user-account fs-3 text-primary"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <h5 class="mb-0" id="total_participant">-</h5>
                        <span class="text-muted small">Total Peserta</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm mb-3 mb-xl-0">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="avatar-md rounded-circle bg-info-subtle d-flex align-items-center justify-content-center flex-shrink-0">
                        <i class="bx bx-bar-chart-alt-2 fs-3 text-info"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <h5 class="mb-0"><?php echo get_eselon_type($data['level']) ?></h5>
                        <span class="text-muted small">Level Kompetensi</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4 d-none d-xl-block">
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="avatar-md rounded-circle bg-success-subtle d-flex align-items-center justify-content-center flex-shrink-0">
                        <i class="bx bx-book-open fs-3 text-success"></i>
                    </div>
                    <div class="ms-3 flex-grow-1">
                        <h5 class="mb-0">Wawancara</h5>
                        <span class="text-muted small">Penilaian Peserta</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Participant Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0"><i class="bx bx-list-ul text-primary me-1"></i> Daftar Peserta Wawancara</h5>
        </div>
        <div class="card-body">
            <div style="max-height: 500px; overflow-y: auto;">
                <table class="table table-hover table-bordered align-middle" id="interviewParticipantTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">NIP</th>
                            <th class="text-center">Nama Lengkap</th>
                            <th class="text-center">Pangkat</th>
                            <th class="text-center">Tingkatan</th>
                            <th class="text-center">Jabatan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<?php echo $this->include('layouts/partials/datatables-scripts') ?>
<script type="text/javascript">
    $(document).ready(function() {
        let url = '<?php echo site_url("interview/interview_participant/list_data/" . $data['id'] . '/' . $data['level']); ?>'

        generateDataTable('interviewParticipantTable', url, {
            buttons: [],
            columns: [{
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 0, className: 'text-center' },
                { data: 1 },
                { data: 2 },
                { data: 3 },
                { data: 4 },
                { data: 5, className: 'text-center', orderable: false }
            ],
        });

        $.ajax({
            url: url,
            type: 'POST',
            data: { length: -1 },
            success: function(res) {
                $('#total_participant').text(res.recordsTotal || res.data.length || 0);
            }
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
<?php echo $this->endSection() ?>