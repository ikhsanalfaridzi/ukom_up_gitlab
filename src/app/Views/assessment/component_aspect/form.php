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
                        <?= form_hidden('assessment_component_id', $assessment_component_id ?? '') ?>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('aspect') ? 'is-invalid' : '';
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control ' . $error,
                                    'name'          => 'aspect',
                                    'id'            => 'aspect',
                                    'placeholder'   => 'Masukkan Nama Aspek Penilaian',
                                    'value'         => isset($data['records']['aspect']) ? $data['records']['aspect'] : set_value('aspect')
                                ];
                                echo form_label('Aspek Penilaian', 'aspect', ['class' => 'form-label']);
                                echo form_input($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('aspect') ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('order') ? 'is-invalid' : '';
                                $item = [
                                    'type'          => 'number',
                                    'class'         => 'form-control ' . $error,
                                    'name'          => 'order',
                                    'id'            => 'order',
                                    'placeholder'   => 'Masukkan Urutan Tampil Aspek',
                                    'value'         => isset($data['records']['order']) ? $data['records']['order'] : set_value('order')
                                ];
                                echo form_label('Urutan Tampil Aspek', 'order', ['class' => 'form-label']);
                                echo form_input($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('order') ?>
                                </div>
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
                                'checked'   => $data['records'] && $data['records']['is_active'] === "t" ? true : false
                            ];
                            $non_active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_active',
                                'id'        => 'non_active_value',
                                'value'     => 0,
                                'checked'   => $data['records'] && $data['records']['is_active'] !== "t" ? true : false
                            ];
                            echo form_label('Status Aspek', 'is_active', ['class' => 'form-label']);
                            ?>
                            <div class="d-flex gap-3">
                                <div class="d-flex flex-row gap-2 mb-1">
                                    <?php
                                    echo form_radio($active);
                                    echo form_label('Aktif', 'is_active');
                                    ?>
                                </div>
                                <div class="d-flex flex-row gap-2">
                                    <div class="mb-2">
                                        <?php
                                        echo form_radio($non_active);
                                        echo form_label('Tidak Aktif', 'is_active');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="javascript:history.back()" class="btn btn-outline-secondary">Kembali</a>
                        <?php echo form_button(['type' => 'submit', 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>
