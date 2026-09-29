<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('css') ?>
<link href="<?php echo base_url('vlz/libs/select2/select2.min.css') ?>" rel="stylesheet" type="text/css" />
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'Instansi', 'title' => $title]); ?>

    <div class="row">
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
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control ',
                                    'name'          => 'structure_code',
                                    'id'            => 'structure_code',
                                    'placeholder'   => 'Masukkan Kode Struktur',
                                    'value'         => isset($result['structure_code']) ? $result['structure_code'] : set_value('structure_code')
                                ];
                                echo form_label('Kode Struktur' . is_required(), 'structure_code', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control ',
                                    'name'          => 'archive_code',
                                    'id'            => 'archive_code',
                                    'placeholder'   => 'Masukkan Kode Persuratan',
                                    'value'         => isset($result['archive_code']) ? $result['archive_code'] : set_value('archive_code')
                                ];
                                echo form_label('Kode Persuratan', 'archive_code', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control ',
                                    'name'          => 'structure_name',
                                    'id'            => 'structure_name',
                                    'placeholder'   => 'Masukkan Nama Struktur/Bidang',
                                    'value'         => isset($result['structure_name']) ? $result['structure_name'] : set_value('structure_name')
                                ];
                                echo form_label('Nama Struktur/Bidang' . is_required(), 'structure_name', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $option = 'id="organization_id" class="form-select select2"';
                                $value = isset($result['organization_id']) ? $result['organization_id'] : "";
                                echo form_label('Unit Kerja' . is_required(), 'organization_id', ['class' => 'form-label']);
                                echo form_dropdown('organization_id', $organization_options, $value, $option);
                                ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <?php
                            $active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_active',
                                'id'        => 'active_value',
                                'value'     => 1,
                                'checked'   => $result && $result['is_active'] === "1" ? true : false
                            ];
                            $non_active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_active',
                                'id'        => 'non_active_value',
                                'value'     => 0,
                                'checked'   => $result && $result['is_active'] !== "1" ? true : false
                            ];
                            echo form_label('Status' . is_required(), 'is_active', ['class' => 'form-label']);
                            ?>
                            <div class="d-flex gap-3">
                                <div class="d-flex flex-row gap-2 mb-1">
                                    <?php
                                    echo form_radio($active);
                                    echo form_label('Aktif', 'active_value');
                                    ?>
                                </div>
                                <div class="d-flex flex-row gap-2">
                                    <div class="mb-2">
                                        <?php
                                        echo form_radio($non_active);
                                        echo form_label('Tidak Aktif', 'non_active_value');
                                        ?>
                                    </div>
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