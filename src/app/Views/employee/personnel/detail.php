<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h3 class="pb-1 mb-4"><?php echo $title ?></h3>
    <div class="d-flex justify-content-between">
        <a href="<?php echo site_url('employee/personnel') ?>" class="btn btn-outline-secondary mb-3"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
        <div class="mb-3">
            <a href="<?php echo site_url('assessment/assessment/history/' . $result['id']) ?>" class="badge bg-secondary" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Riwayat Wawancara"><i class="bx bx-history"></i></a>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md">
            <div class="card mb-3">
                <div class="row g-0 px-4 py-3">
                    <div class="col-md-2 d-flex">
                        <img class="rounded-circle" src="<?php echo !empty($result['profile_image']) ? base_url('uploads/employee/profile_image/' . $result['profile_image']) : base_url('vlz/images/users/no-photo.jpg') ?>" style="width: 150px; height: 150px; object-fit: cover;" alt="Foto Profil">
                    </div>
                    <div class="col-md-10">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Data Pegawai</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">NIP/NRP</div>
                                        <div class="col-md-8"><?php echo $result['employee_number'] ? $result['employee_number'] : "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Nama Lengkap</div>
                                        <div class="col-md-8"><?php echo $result['full_name'] ? strtoupper($result['full_name']) : "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Tanggal Wawancara</div>
                                        <div class="col-md-8"><?php echo $result['created_at'] ? format_date($result['created_at'], false) : "-"; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Golongan</div>
                                        <div class="col-md-8"><?php echo $result['grade_name'] ?? "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Jabatan</div>
                                        <div class="col-md-8"><?php echo $result['position_name'] ?? "-"; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>
