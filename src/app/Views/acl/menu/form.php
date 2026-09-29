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
                                $option = 'id="parent_id" class="form-select select2"';
                                $value = isset($result['parent_id']) ? $result['parent_id'] : "";
                                echo form_label('Sub Dari Menu', 'parent_id', ['class' => 'form-label']);
                                echo form_dropdown('parent_id', $parent_options, $value, $option);
                                ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $option = 'id="module_id" class="form-select select2"';
                                $value = isset($result['module_id']) ? $result['module_id'] : "";
                                echo form_label('Modul' . is_required(), 'module_id', ['class' => 'form-label']);
                                echo form_dropdown('module_id', $module_options, $value, $option);
                                ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $option = 'id="icon_name" class="form-select select2"';
                                $value = isset($result['icon_name']) ? $result['icon_name'] : "";
                                echo form_label('Icon', 'icon_name', ['class' => 'form-label']);
                                echo form_dropdown('icon_name', $icon_options, $value, $option);
                                ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control',
                                    'name'          => 'menu_code',
                                    'id'            => 'menu_code',
                                    'placeholder'   => 'Masukkan Kode Menu',
                                    'value'         => isset($result['menu_code']) ? $result['menu_code'] : set_value('menu_code')
                                ];
                                echo form_label('Kode Menu' . is_required(), 'menu_code', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control',
                                    'name'          => 'menu_name',
                                    'id'            => 'menu_name',
                                    'placeholder'   => 'Masukkan Nama Menu',
                                    'value'         => isset($result['menu_name']) ? $result['menu_name'] : set_value('menu_name')
                                ];
                                echo form_label('Nama Menu' . is_required(), 'menu_name', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control',
                                    'name'          => 'url',
                                    'id'            => 'url',
                                    'placeholder'   => 'Masukkan URL',
                                    'value'         => isset($result['url']) ? $result['url'] : set_value('url')
                                ];
                                echo form_label('URL', 'url', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'number',
                                    'class'         => 'form-control',
                                    'name'          => 'sequence_no',
                                    'id'            => 'sequence_no',
                                    'placeholder'   => 'Masukkan No. Urut',
                                    'value'         => isset($result['sequence_no']) ? $result['sequence_no'] : set_value('sequence_no')
                                ];
                                echo form_label('No. Urut', 'sequence_no', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <?php
                            $active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_display',
                                'id'        => 'display_value',
                                'value'     => 1,
                                'checked'   => $result && $result['is_display'] === "1" ? true : false
                            ];
                            $non_active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_display',
                                'id'        => 'non_display_value',
                                'value'     => 0,
                                'checked'   => $result && $result['is_display'] !== "1" ? true : false
                            ];
                            echo form_label('Tampilkan' . is_required(), 'is_display', ['class' => 'form-label']);
                            ?>
                            <div class="d-flex gap-3">
                                <div class="d-flex flex-row gap-2 mb-1">
                                    <?php
                                    echo form_radio($active);
                                    echo form_label('Ya', 'display_value');
                                    ?>
                                </div>
                                <div class="d-flex flex-row gap-2">
                                    <div class="mb-2">
                                        <?php
                                        echo form_radio($non_active);
                                        echo form_label('Tidak', 'non_display_value');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
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