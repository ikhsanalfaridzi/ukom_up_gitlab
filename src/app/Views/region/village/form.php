<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('css') ?>
<link href="<?php echo base_url('vlz/libs/select2/select2.min.css') ?>" rel="stylesheet" type="text/css" />
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'Wilayah', 'title' => $title]); ?>

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
                                $option = 'id="district_id" class="form-select select2"';
                                $value = isset($result['district_id']) ? $result['district_id'] : "";
                                echo form_label('Kecamatan' . is_required(), 'district_id', ['class' => 'form-label']);
                                echo form_dropdown('district_id', $district_options, $value, $option);
                                ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control',
                                    'name'          => 'village_name',
                                    'id'            => 'village_name',
                                    'placeholder'   => 'Masukkan Nama Keluharan',
                                    'value'         => isset($result['village_name']) ? $result['village_name'] : set_value('village_name')
                                ];
                                echo form_label('Nama Keluharan' . is_required(), 'village_name', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control',
                                    'name'          => 'postal_code',
                                    'id'            => 'postal_code',
                                    'placeholder'   => 'Masukkan Kode Pos',
                                    'value'         => isset($result['postal_code']) ? $result['postal_code'] : set_value('postal_code')
                                ];
                                echo form_label('Kode Pos' . is_required(), 'postal_code', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('description') ? 'is-invalid' : '';
                                $item = [
                                    'class'         => 'form-control',
                                    'name'          => 'description',
                                    'id'            => 'description',
                                    'rows'          => 3,
                                    'value'         => isset($result['description']) ? $result['description'] : set_value('description')
                                ];
                                echo form_label('Deskripsi', 'description', ['class' => 'form-label']);
                                echo form_textarea($item); ?>
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