<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('css') ?>
<link href="<?php echo base_url('vlz/libs/select2/select2.min.css') ?>" rel="stylesheet" type="text/css" />
<?php echo $this->endSection() ?>

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
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('name') ? 'is-invalid' : '';
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control ' . $error,
                                    'name'          => 'name',
                                    'id'            => 'name',
                                    'placeholder'   => 'Masukkan Nama Kelompok',
                                    'value'         => isset($data['records']['name']) ? $data['records']['name'] : set_value('name')
                                ];
                                echo form_label('Nama Kelompok', 'name', ['class' => 'form-label']);
                                echo form_input($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('name') ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('instance_code') ? 'is-invalid' : '';
                                $selected = isset($data['records']['instance_code']) ? $data['records']['instance_code'] : set_value('instance_code');
                                $options = ['' => '- Silahkan Pilih Kode Instansi -'];
                                if (!empty($instance_code_list)) {
                                    foreach ($instance_code_list as $org) {
                                        $options[$org['organization_code']] = $org['organization_code'] . ' - ' . $org['organization'];
                                    }
                                }
                                echo form_label('Kode Instansi', 'instance_code', ['class' => 'form-label']);
                                echo form_dropdown('instance_code', $options, $selected, 'class="form-control select2 ' . $error . '" id="instance_code"'); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('instance_code') ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('level') ? 'is-invalid' : '';
                                $selected = isset($data['records']['level']) ? $data['records']['level'] : set_value('level');
                                $level_options = [
                                    ''  => '- Silahkan Pilih Level Kompetensi -',
                                    '5' => '5 (Eselon I & II)',
                                    '3' => '3 (Eselon III)',
                                    '2' => '2 (Eselon IV)',
                                    '1' => '1 (Eselon V)',
                                ];
                                echo form_label('Level Kompetensi', 'level', ['class' => 'form-label']);
                                echo form_dropdown('level', $level_options, $selected, 'class="form-control select2 ' . $error . '" id="level"'); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('level') ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('jenis') ? 'is-invalid' : '';
                                $selected = isset($data['records']['jenis']) ? $data['records']['jenis'] : set_value('jenis');
                                $jenis_options = [
                                    ''     => '- Silahkan Pilih Jenis Uji Kompetensi -',
                                    'UKKJ' => 'UKKJ (Uji Kompetensi Kenaikan Jenjang)',
                                    'PDJL' => 'PDJL (Perpindahan Dari Jabatan Lain)',
                                ];
                                echo form_label('Jenis Uji Kompetensi', 'jenis', ['class' => 'form-label']);
                                echo form_dropdown('jenis', $jenis_options, $selected, 'class="form-control select2 ' . $error . '" id="jenis"'); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('jenis') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?php echo site_url('interview/interview_group') ?>" class="btn btn-outline-secondary">Kembali</a>
                        <?php echo form_button(['type' => 'submit', 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>

<?php echo $this->section('javascript') ?>
<script src="<?php echo base_url('vlz/libs/select2/select2.min.js'); ?>" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: '- Silahkan Pilih -'
        });
    });
</script>
<?php echo $this->endSection() ?>