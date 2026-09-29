<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col">
            <div class="card mb-4">
                <h5 class="card-header"><?php echo $data['subtitle']; ?></h5>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Pertanyaan</div>
                        <div class="col-md-8"><?php echo $question['question'] ?></div>
                    </div>
                    <?php echo form_open($data['url'], ['autocomplete' => 'off']) ?>
                    <?php echo csrf_field() ?>
                    <div class="modal-body">
                        <?= form_hidden('aspect_question_id', $aspect_question_id) ?>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('instrument') ? 'is-invalid' : '';
                                $item = [
                                    'class'         => 'form-control ' . $error,
                                    'name'          => 'instrument',
                                    'id'            => 'instrument',
                                    'rows'          => 3,
                                    'value'         => isset($data['records']['instrument']) ? $data['records']['instrument'] : set_value('instrument')
                                ];
                                echo form_label('Instrumen Penilaian', 'instrument', ['class' => 'form-label']);
                                echo form_textarea($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('instrument') ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('value') ? 'is-invalid' : '';
                                $item = [
                                    'type'          => 'number',
                                    'step'          => 'any',
                                    'class'         => 'form-control ' . $error,
                                    'name'          => 'value',
                                    'id'            => 'value',
                                    'placeholder'   => 'Masukkan Standar Nilai',
                                    'value'         => isset($data['records']['value']) ? $data['records']['value'] : set_value('value')
                                ];
                                echo form_label('Standar Nilai', 'value', ['class' => 'form-label']);
                                echo form_input($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('value') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="<?php echo site_url('assessment/question_instrument/list/' . $aspect_question_id) ?>" class="btn btn-outline-secondary">Kembali</a>
                        <?php echo form_button(['type' => 'submit', 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>