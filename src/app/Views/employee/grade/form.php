<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('css') ?>
<link href="<?php echo base_url('vlz/libs/select2/select2.min.css') ?>" rel="stylesheet" type="text/css" />
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'ACL', 'title' => $title]); ?>

    <div class="row" data-aos="fade-right">
        <div class="col">
            <div class="card mb-4">
                <h5 class="card-header"><?php echo $title; ?></h5>
                <div class="card-body">
                    <?php echo form_open($uri_name . '/' . $form_action, ['id' => 'form_input_' . $uri_name_js_id, 'autocomplete' => 'off']) ?>
                    <?php echo csrf_field() ?>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $option = 'id="position_group_id" class="form-select select2"';
                                $value = isset($result['position_group_id']) ? $result['position_group_id'] : "";
                                echo form_label('Kelompok Jabatan', 'position_group_id', ['class' => 'form-label']);
                                echo form_dropdown('position_group_id', $position_group_options, $value, $option);
                                ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control',
                                    'name'          => 'code',
                                    'id'            => 'code',
                                    'placeholder'   => 'Masukkan Kode',
                                    'value'         => isset($result['code']) ? $result['code'] : set_value('code')
                                ];
                                echo form_label('Kode', 'code', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control',
                                    'name'          => 'grade',
                                    'id'            => 'grade',
                                    'placeholder'   => 'Masukkan Tingkatan',
                                    'value'         => isset($result['grade']) ? $result['grade'] : set_value('grade')
                                ];
                                echo form_label('Tingkatan', 'grade', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'number',
                                    'class'         => 'form-control',
                                    'name'          => 'level',
                                    'id'            => 'level',
                                    'placeholder'   => 'Masukkan Level Tingkatan',
                                    'value'         => isset($result['level']) ? $result['level'] : set_value('level')
                                ];
                                echo form_label('Level Tingkatan', 'level', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $option = 'id="classification" class="form-select"';
                                $value = isset($result['classification']) ? $result['classification'] : "";
                                echo form_label('Klasifikasi', 'classification', ['class' => 'form-label']);
                                echo form_dropdown('classification', $classification_options, $value, $option);
                                ?>
                                <div class="text-muted text-x-small mb-0 full-width small">
                                    Apabila Klasifikasi belum tersedia, silahkan ketik sebagai data baru.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3">
                        <?php echo form_button(['type' => 'button', 'id' => 'btn_cancel_' . $uri_name_js_id, 'content' => 'Kembali', 'class' => 'btn btn-outline-secondary btn-cancel']) ?>
                        <?php echo form_button(['type' => 'submit', 'id' => 'btn_save_' . $uri_name_js_id, 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>

<?php echo $this->section('javascript'); ?>
<script src="<?php echo base_url('vlz/libs/select2/select2.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo base_url('vlz/js/form-handler.js'); ?>" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: '- Silahkan Pilih -',
            allowClear: true,
            width: '100%'
        });

        $('#classification').select2({
            placeholder: '- Silahkan Pilih -',
            tags: true,
            allowClear: true,
            width: '100%'
        });

        $("#btn_cancel_<?php echo $uri_name_js_id; ?>").click(function() {
            redirect(site_url + uri_name);
        });

        handleFormSubmit(`#form_input_<?php echo $uri_name_js_id; ?>`)
            .onSuccess(function(res) {
                setToast(res.status, res.message);
                redirect(res.redirect_url);
            })
            .onError(function(err) {
                if (err.responseJSON?.message !== 'Validation failed') {
                    showAlert('Error!', err.responseJSON?.message, err.responseJSON?.status);
                }
            })
            .init();
    });
</script>
<?php echo $this->endSection(); ?>