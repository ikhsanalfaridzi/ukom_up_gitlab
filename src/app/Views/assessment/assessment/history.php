<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('css') ?>
<?php echo $this->include('layouts/partials/datatables-css') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h3 class="pb-1 mb-4"><?php echo $title ?></h3>
    <div class="d-flex justify-content-between">
        <a href="<?php echo site_url('interview/interview_group/list') ?>" class="btn btn-primary mb-3">Kembali</a>
    </div>

    <div class="row mb-5">
        <div class="col-md">
            <div class="card mb-3">
                <div class="row g-0 px-4">
                    <div class="col-md-2 d-flex">
                        <img class="rounded-circle header-profile-user" src="<?php echo !empty($data['profile_image']) ? base_url('uploads/employee/profile_image/' . $data['profile_image']) : base_url('vlz/images/users/no-photo.jpg') ?>" style="width: 150px; height: 150px; object-fit: cover;" alt="Header Avatar">
                    </div>
                    <div class="col-md-10">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Data Pegawai</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">NIP/NRP</div>
                                        <div class="col-md-8"><?php echo $data['employee_number'] ? $data['employee_number'] : "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Nama Lengkap</div>
                                        <div class="col-md-8"><?php echo strtoupper($data['full_name']) ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Golongan</div>
                                        <div class="col-md-8"><?php echo $data['grade_name'] ?? "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Jabatan</div>
                                        <div class="col-md-8"><?php echo $data['position_name'] ?? "-"; ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h5 class="card-header">Riwayat Wawancara</h5>
        <div class="card-body">
            <?php if (empty($history)) : ?>
                <div class="alert alert-info mb-0">Belum ada data wawancara untuk pegawai ini.</div>
            <?php else : ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Kelompok Wawancara</th>
                            <th class="text-center">Level</th>
                            <th class="text-center">Tipe</th>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Jumlah Penilai</th>
                            <th class="text-center">Rata-rata Skor</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; ?>
                        <?php foreach ($history as $row) : ?>
                            <tr>
                                <td class="text-center"><?php echo $i++; ?></td>
                                <td><?php echo $row['group_name'] ?? "-"; ?></td>
                                <td class="text-center"><?php echo $row['level'] ?? "-"; ?></td>
                                <td class="text-center"><?php echo $row['type'] ?? "-"; ?></td>
                                <td class="text-center"><?php echo $row['tanggal'] ? format_date($row['tanggal'], false) : "-"; ?></td>
                                <td class="text-center"><?php echo $row['jumlah_evaluator']; ?></td>
                                <td class="text-center"><?php echo $row['score']; ?></td>
                                <td class="text-center">
                                    <a href="<?php echo site_url('assessment/assessment/result/' . $employee_id . '/' . $row['type'] . '/' . $row['interview_participant_id']) ?>" class="badge bg-success" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Hasil Wawancara"><i class='bx bx-book-open'></i></a>
                                    <a href="<?php echo site_url('assessment/assessment/convert/' . $employee_id . '/' . $row['type'] . '/' . $row['interview_participant_id']) ?>" class="badge bg-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Convert"><i class='bx bx-transfer-alt'></i></a>
                                    <a href="<?php echo site_url('assessment/assessment/pdf_convert/' . $employee_id . '/' . $row['interview_participant_id']) ?>" class="badge bg-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Export PDF"><i class='bx bxs-file-pdf'></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>
