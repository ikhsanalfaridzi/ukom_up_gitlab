<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col">
            <div class="card mb-4">
                <h5 class="card-header"><?php echo $data['subtitle']; ?></h5>
                <div class="card-body">
                    <?php echo form_open($data['url'], ['autocomplete' => 'off']) ?>
                    <?php echo csrf_field() ?>
                    <div class="modal-body">
                        <?= form_hidden('interview_group_id', $interview_group_id) ?>

                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('employee_id') ? 'is-invalid' : '';
                                $option = 'id="employee_id" class="form-select select2 ' . ($error ? 'is-invalid' : '') . '"';
                                $value = isset($data['records']['employee_id']) ? $data['records']['employee_id'] : "";
                                echo form_label('Pilih Pegawai', 'employee_id', ['class' => 'form-label']);
                                echo form_dropdown('employee_id', $employee_list, $value, $option);
                                ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('employee_id') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?php echo site_url('interview/interview_participant/list/' . $interview_group_id) ?>" class="btn btn-outline-secondary">Kembali</a>
                        <?php echo form_button(['type' => 'submit', 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>

<?php echo $this->section('javascript'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: '- Silahkan Pilih -',
            allowClear: true,
            width: '100%'
        });
    });
</script>
<?php echo $this->endSection(); ?>