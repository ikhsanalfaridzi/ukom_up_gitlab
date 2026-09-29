<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('css') ?>
<link href="<?php echo base_url('vlz/libs/select2/select2.min.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('vlz/libs/flatpickr/flatpickr.min.css') ?>" rel="stylesheet" type="text/css" />
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'Instansi', 'title' => $title]); ?>

    <?php echo form_open_multipart($uri_name . '/' . $form_action, ['id' => 'form_input_' . $uri_name_js_id, 'autocomplete' => 'off']) ?>
    <?php echo csrf_field() ?>

    <div class="row" data-aos="fade-right">
        <div class="col-lg-8 col-md-10 mx-auto">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-white mb-0"><?php echo $title; ?></h5>
                </div>
                <div class="card-body">
                    <!-- Dropdown Single: Nama Pegawai -->
                    <div class="mb-3">
                        <label for="employee_personnel_id" class="form-label">
                            Nama Pegawai <?php echo is_required(); ?>
                        </label>
                        <?php
                        $option = 'id="employee_personnel_id" class="form-select select2" style="width: 100%;"';
                        $value = isset($result['employee_personnel_id']) ? $result['employee_personnel_id'] : set_value('employee_personnel_id');
                        echo form_dropdown('employee_personnel_id', ['' => '-- Pilih Pegawai --'] + $employee_options, $value, $option);
                        ?>
                    </div>

                    <!-- Dropdown Single: Unit Kerja -->
                    <div class="mb-3">
                        <label for="organization_id" class="form-label">
                            Unit Kerja <?php echo is_required(); ?>
                        </label>
                        <?php
                        $option = 'id="organization_id" class="form-select select2" style="width: 100%;"';
                        $value = isset($result['organization_id']) ? $result['organization_id'] : set_value('organization_id');
                        echo form_dropdown('organization_id', ['' => '-- Pilih Unit Kerja --'] + $organization_options, $value, $option);
                        ?>
                    </div>

                    <!-- Multiple Select (Select2): Tunjangan / Benefits -->
                    <div class="mb-3">
                        <label for="benefits" class="form-label">
                            Tunjangan / Benefits Pegawai (Multiple Choice)
                        </label>
                        <select name="benefits[]" id="benefits" class="form-select select2-multiple" multiple="multiple" style="width: 100%;">
                            <?php foreach ($benefit_options as $key => $label): ?>
                                <?php $selected = in_array($key, $selected_benefits) ? 'selected' : ''; ?>
                                <option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Pilih satu atau lebih tunjangan yang didapatkan pegawai.</small>
                    </div>

                    <!-- Input Nominal Gaji -->
                    <div class="mb-3">
                        <label for="salary" class="form-label">
                            Nominal Gaji (Rp) <?php echo is_required(); ?>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="number" name="salary" id="salary" class="form-control" placeholder="Contoh: 10000000" value="<?php echo isset($result['salary']) ? $result['salary'] : set_value('salary'); ?>" required min="0" step="1000">
                        </div>
                    </div>

                    <!-- Input Tanggal Dibuat / Tanggal Gaji -->
                    <div class="mb-3">
                        <label for="created_at" class="form-label">
                            Tanggal Dibuat / Tanggal Gaji
                        </label>
                        <input type="datetime-local" name="created_at" id="created_at" class="form-control flatpickr-datetime" placeholder="Pilih Tanggal & Waktu" value="<?php echo isset($result['created_at']) ? date('Y-m-d\TH:i', strtotime($result['created_at'])) : date('Y-m-d\TH:i'); ?>">
                    </div>

                    <!-- Upload File Dokumen / Slip Gaji -->
                    <div class="mb-3">
                        <label for="document_file" class="form-label">
                            Upload Dokumen / Slip Gaji
                        </label>
                        <input type="file" name="document_file" id="document_file" class="form-control" accept=".pdf,.png,.jpg,.jpeg">
                        <?php if (isset($result['document_path']) && !empty($result['document_path'])): ?>
                            <div class="mt-2">
                                <span class="badge bg-success">Berkas Terlampir:</span>
                                <a href="<?php echo base_url($result['document_path']); ?>" target="_blank" class="ms-1 text-decoration-underline">
                                    Lihat / Unduh Dokumen Saat Ini
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card-footer d-flex justify-content-between">
                    <a href="<?php echo site_url($uri_name) ?>" class="btn btn-light">
                        <i class="ri-arrow-left-line align-middle me-1"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ri-save-line align-middle me-1"></i> Simpan Data
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php echo form_close() ?>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<script src="<?php echo base_url('vlz/libs/select2/select2.min.js') ?>"></script>
<script src="<?php echo base_url('vlz/libs/flatpickr/flatpickr.min.js') ?>"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // Initialize Single Select2
        $('.select2').select2({
            placeholder: '-- Pilih --',
            allowClear: true
        });

        // Initialize Multiple Select2
        $('.select2-multiple').select2({
            placeholder: '-- Pilih Tunjangan --',
            allowClear: true
        });

        // Form Submit Handler
        var form = 'form_input_<?php echo $uri_name_js_id ?>';
        submit_data(form);
    });
</script>
<?php echo $this->endSection() ?>
