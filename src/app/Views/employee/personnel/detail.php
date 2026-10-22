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
                                        <div class="col-md-8"><?php echo $result['employee_number'] ?: "-"; ?></div>
                                    </div>
                                    <?php if (!empty($result['old_employee_number'])) : ?>
                                        <div class="row mb-3">
                                            <div class="col-md-4 fw-bold">NIP/NRP Lama</div>
                                            <div class="col-md-8"><?php echo $result['old_employee_number']; ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Nama Lengkap</div>
                                        <div class="col-md-8"><?php echo $result['full_name'] ? strtoupper($result['full_name']) : "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Pangkat/Golongan</div>
                                        <div class="col-md-8"><?php echo $result['assortment'] ? $result['assortment'] . (!empty($result['assortment_description']) ? ' - ' . $result['assortment_description'] : '') : "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Jabatan</div>
                                        <div class="col-md-8"><?php echo $result['position'] ?: "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Tingkatan</div>
                                        <div class="col-md-8"><?php echo $result['position_grade'] ?: "-"; ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Instansi Asal</div>
                                        <div class="col-md-8"><?php echo $result['organization'] ?: "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Status Kepegawaian</div>
                                        <div class="col-md-8"><?php echo $result['employment'] ? $result['employment'] . (!empty($result['employment_type']) ? ' - ' . $result['employment_type'] : '') : "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Tanggal Lahir</div>
                                        <div class="col-md-8"><?php echo !empty($result['date_of_birth']) ? format_date($result['date_of_birth'], false) . ' (' . calculate_age($result['date_of_birth']) . ' th)' : "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Email Pribadi</div>
                                        <div class="col-md-8"><?php echo $result['personal_email'] ?: "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">No HP / WA</div>
                                        <div class="col-md-8"><?php echo $result['phone_number'] ?: "-"; ?><?php echo !empty($result['wa_number']) ? ' / ' . $result['wa_number'] : ''; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title mb-4">Akun &amp; Verifikasi</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Username</div>
                                <div class="col-md-8"><?php echo $result['username'] ?: "-"; ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Grup Akses</div>
                                <div class="col-md-8"><?php echo $result['role'] ?: "-"; ?></div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Status Akun</div>
                                <div class="col-md-8">
                                    <?php if ($result['is_active']) : ?>
                                        <span class="badge bg-label-success">Aktif</span>
                                    <?php else : ?>
                                        <span class="badge bg-label-danger">Tidak Aktif</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Status Verifikasi</div>
                                <div class="col-md-8">
                                    <?php if ($result['is_verified']) : ?>
                                        <span class="badge bg-label-success">Terverifikasi</span>
                                    <?php else : ?>
                                        <span class="badge bg-label-warning">Belum Terverifikasi</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4 fw-bold">Diverifikasi Pada</div>
                                <div class="col-md-8"><?php echo $result['verified_at'] ? format_date($result['verified_at'], false) : "-"; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>
