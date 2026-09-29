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
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('name') ? 'is-invalid' : '';
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control ' . $error,
                                    'name'          => 'name',
                                    'id'            => 'name',
                                    'placeholder'   => 'Masukkan Nama Komponen',
                                    'value'         => isset($data['records']['name']) ? $data['records']['name'] : set_value('name')
                                ];
                                echo form_label('Nama Komponen', 'name', ['class' => 'form-label']);
                                echo form_input($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('name') ?>
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
                                    'placeholder'   => 'Masukkan Urutan Tampil Komponen',
                                    'value'         => isset($data['records']['order']) ? $data['records']['order'] : set_value('order')
                                ];
                                echo form_label('Urutan Tampil Komponen', 'order', ['class' => 'form-label']);
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
                            echo form_label('Status Komponen', 'is_active', ['class' => 'form-label']);
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
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('description') ? 'is-invalid' : '';
                                $item = [
                                    'class'         => 'form-control ' . $error,
                                    'name'          => 'description',
                                    'id'            => 'description',
                                    'rows'          => 3,
                                    'value'         => isset($data['records']['description']) ? $data['records']['description'] : set_value('description')
                                ];
                                echo form_label('description', 'description', ['class' => 'form-label']);
                                echo form_textarea($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('description') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?php echo site_url('assessment/assessment_component') ?>" class="btn btn-outline-secondary">Kembali</a>
                        <?php echo form_button(['type' => 'submit', 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>