<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('css') ?>
<link href="<?php echo base_url('vlz/libs/select2/select2.min.css') ?>" rel="stylesheet" type="text/css" />
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'System', 'title' => $title]); ?>

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
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control',
                                    'name'          => 'description',
                                    'id'            => 'description',
                                    'placeholder'   => 'Masukkan Deskripsi',
                                    'value'         => isset($result['description']) ? $result['description'] : set_value('description')
                                ];
                                echo form_label('Deskripsi' . is_required(), 'description', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $option = 'id="groups" class="form-select select2"';
                                $value = isset($result['groups']) ? $result['groups'] : "";
                                echo form_label('Grup' . is_required(), 'groups', ['class' => 'form-label']);
                                echo form_dropdown('groups', $group_options, $value, $option);
                                ?>
                                <div class="text-muted text-x-small mb-0 full-width small">
                                    Apabila Grup belum tersedia, silahkan ketik sebagai data baru.
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'class'         => 'form-control',
                                    'name'          => 'notes',
                                    'id'            => 'notes',
                                    'rows'          => 3,
                                    'value'         => isset($result['notes']) ? $result['notes'] : set_value('notes')
                                ];
                                echo form_label('Catatan', 'notes', ['class' => 'form-label']);
                                echo form_textarea($item); ?>
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
                        <div class="row mb-3">
                            <?php
                            $active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_value_option',
                                'id'        => 'value',
                                'value'     => 1,
                                'checked'   => $result && $result['is_value_option'] === "1" ? true : false
                            ];
                            $non_active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_value_option',
                                'id'        => 'non_value',
                                'value'     => 0,
                                'checked'   => $result && $result['is_value_option'] !== "1" ? true : false
                            ];
                            echo form_label('Opsi Deskripsi', 'is_value_option', ['class' => 'form-label']);
                            ?>
                            <div class="d-flex gap-3">
                                <div class="d-flex flex-row gap-2 mb-1">
                                    <?php
                                    echo form_radio($active);
                                    echo form_label('Ya', 'value');
                                    ?>
                                </div>
                                <div class="d-flex flex-row gap-2">
                                    <div class="mb-2">
                                        <?php
                                        echo form_radio($non_active);
                                        echo form_label('Tidak', 'non_value');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <?php
                            $active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_note_option',
                                'id'        => 'note_value',
                                'value'     => 1,
                                'checked'   => $result && $result['is_note_option'] === "1" ? true : false
                            ];
                            $non_active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_note_option',
                                'id'        => 'non_note_value',
                                'value'     => 0,
                                'checked'   => $result && $result['is_note_option'] !== "1" ? true : false
                            ];
                            echo form_label('Opsi Catatan', 'is_note_option', ['class' => 'form-label']);
                            ?>
                            <div class="d-flex gap-3">
                                <div class="d-flex flex-row gap-2 mb-1">
                                    <?php
                                    echo form_radio($active);
                                    echo form_label('Ya', 'note_value');
                                    ?>
                                </div>
                                <div class="d-flex flex-row gap-2">
                                    <div class="mb-2">
                                        <?php
                                        echo form_radio($non_active);
                                        echo form_label('Tidak', 'non_note_value');
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