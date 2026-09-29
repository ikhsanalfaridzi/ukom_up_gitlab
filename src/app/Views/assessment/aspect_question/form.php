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
                        <?= form_hidden('component_aspect_id', $component_aspect_id) ?>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('question') ? 'is-invalid' : '';
                                $item = [
                                    'class'         => 'form-control ' . $error,
                                    'name'          => 'question',
                                    'id'            => 'question',
                                    'rows'          => 3,
                                    'value'         => isset($data['records']['question']) ? $data['records']['question'] : set_value('question')
                                ];
                                echo form_label('Pertanyaan', 'question', ['class' => 'form-label']);
                                echo form_textarea($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('question') ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('level') ? 'is-invalid' : '';
                                $item = [
                                    'type'          => 'number',
                                    'class'         => 'form-control ' . $error,
                                    'name'          => 'level',
                                    'id'            => 'level',
                                    'placeholder'   => 'Masukkan Level Pertanyaan',
                                    'value'         => isset($data['records']['level']) ? $data['records']['level'] : set_value('level')
                                ];
                                echo form_label('Level Pertanyaan', 'level', ['class' => 'form-label']);
                                echo form_input($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('level') ?>
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
                                    'placeholder'   => 'Masukkan Urutan Tampil Pertanyaan',
                                    'value'         => isset($data['records']['order']) ? $data['records']['order'] : set_value('order')
                                ];
                                echo form_label('Urutan Tampil Pertanyaan', 'order', ['class' => 'form-label']);
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
                                $error = validation_show_error('assessment_criteria') ? 'is-invalid' : '';
                                $item = [
                                    'class'         => 'form-control ' . $error,
                                    'name'          => 'assessment_criteria',
                                    'id'            => 'assessment_criteria',
                                    'rows'          => 5,
                                    'value'         => isset($data['records']['assessment_criteria']) ? $data['records']['assessment_criteria'] : set_value('assessment_criteria')
                                ];
                                echo form_label('Kriteria Penilaian/Indikator Kompetensi', 'assessment_criteria', ['class' => 'form-label']);
                                echo form_textarea($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('assessment_criteria') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?php echo site_url('assessment/aspect_question/list/' . $component_aspect_id) ?>" class="btn btn-outline-secondary">Kembali</a>
                        <?php echo form_button(['type' => 'submit', 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>