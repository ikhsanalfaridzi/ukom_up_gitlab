<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h3 class="pb-1 mb-4"><?php echo $title ?></h3>

    <?php if (session()->getFlashdata('success')) : ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <?php echo session()->getFlashdata('success'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Data Pegawai -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3">Data Pegawai</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">NIP/NRP</div>
                        <div class="col-md-8"><?php echo $employee['employee_number'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">Nama Lengkap</div>
                        <div class="col-md-8"><?php echo strtoupper($employee['full_name'] ?? '-'); ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">Golongan</div>
                        <div class="col-md-8"><?php echo $employee['grade_name'] ?? '-'; ?></div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-4 fw-bold">Jabatan</div>
                        <div class="col-md-8"><?php echo $employee['position_name'] ?? '-'; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Jawaban -->
    <?php echo form_open('interview_employee/create', ['id' => 'form_interview']) ?>
    <?php echo csrf_field(); ?>

    <?php
    $current_component = '';
    $current_aspect = '';
    $i = 1;
    ?>

    <?php foreach ($questions as $q) : ?>
        <?php if ($q['component_name'] !== $current_component) : ?>
            <?php $current_component = $q['component_name']; ?>
            <?php $current_aspect = ''; ?>
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><?php echo $current_component ?></h6>
                </div>
                <div class="card-body">
        <?php endif; ?>

        <?php if ($q['aspect'] !== $current_aspect) : ?>
            <?php $current_aspect = $q['aspect']; ?>
            <h6 class="text-primary mt-2 mb-3"><?php echo $current_aspect ?></h6>
        <?php endif; ?>

        <div class="mb-4">
            <label class="form-label fw-bold"><?php echo $i++ . '. ' . $q['question'] ?></label>
            <textarea name="response[<?php echo $q['id'] ?>]" class="form-control" rows="3" placeholder="Tulis jawaban Anda..."><?php echo $existing[$q['id']] ?? '' ?></textarea>
            <?php if (!empty($q['assessment_criteria'])) : ?>
                <small class="text-muted"><?php echo $q['assessment_criteria'] ?></small>
            <?php endif; ?>
        </div>

    <?php endforeach; ?>

    <?php if ($current_component !== '') : ?>
                </div>
            </div>
    <?php endif; ?>

    <div class="mb-3">
        <button type="submit" class="btn btn-primary">Simpan Jawaban</button>
    </div>
    <?php echo form_close() ?>
</div>
<?php echo $this->endSection() ?>
