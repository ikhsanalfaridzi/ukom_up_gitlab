<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('content') ?>
<div class="container my-5">
    <!-- Employee Data Card -->
    <div class="row justify-content-center mb-4">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="row g-0 align-items-center">
                    <div class="col-md-3 text-center p-3">
                        <img class="rounded-circle header-profile-user" src="<?php echo !empty($employee_data['profile_image']) ? base_url('uploads/employee/profile_image/' . $employee_data['profile_image']) : base_url('vlz/images/users/no-photo.jpg') ?>" style="width: 150px; height: 150px; object-fit: cover;" alt="Header Avatar">
                    </div>
                    <div class="col-md-9">
                        <div class="card-body">
                            <h4 class="card-title text-primary mb-4">Data Pegawai</h4>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="row mb-3">
                                        <div class="col-5 fw-bold">NIP/NRP</div>
                                        <div class="col-7"><?php echo !empty($employee_data['employee_number']) ? $employee_data['employee_number'] : "-"; ?></div>
                                       </div>
                                    <div class="row mb-3">
                                        <div class="col-5 fw-bold">Nama Lengkap</div>
                                        <div class="col-7"><?php echo strtoupper($employee_data['full_name'] ?? '-') ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-5 fw-bold">Tanggal Lahir</div>
                                        <div class="col-7"><?php echo !empty($employee_data['birth_date']) ? format_date($employee_data['birth_date'], false) : "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-5 fw-bold">Umur</div>
                                        <div class="col-7"><?php echo !empty($employee_data['birth_date']) ? calculate_age($employee_data['birth_date']) : "-" ?></div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="row mb-3">
                                        <div class="col-5 fw-bold">Golongan</div>
                                        <div class="col-7"><?php echo $employee_data['grade_name'] ?? "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-5 fw-bold">Jabatan</div>
                                        <div class="col-7"><?php echo !empty($employee_data['position_name']) ? $employee_data['position_name'] : "-"; ?></div>
                                    </div>
                                    <!-- <div class="row mb-3">
                                        <div class="col-5 fw-bold">Asal Instansi</div>
                                        <div class="col-7"><?php //echo $employee_data['instance_name'] ?? $employee_data['instance_text'] ?? "-"; 
                                                            ?></div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment Form Section -->
    <div class="row">
        <div class="col-lg-12">
            <h2 class="text-center text-primary mb-4 mt-4"><?php echo $title ?></h2>
            <?php echo form_open($record['url'], ['id' => 'assesmentForm', 'autocomplete' => 'off', 'class' => 'shadow-sm p-4 rounded bg-light']) ?>
            <?php echo form_hidden('user_id', $user_id) ?>
            <?php echo form_hidden('employee_id', $employee_id) ?>
            <?php echo form_hidden('type', $type) ?>
            <?php echo form_hidden('interview_participant_id', $interview_participant_id ?? '') ?>

            <!-- Evaluator's Name Field -->
            <div class="col mb-3">
                <?php
                $error = validation_show_error('evaluator') ? 'is-invalid' : '';
                $item = [
                    'type'          => 'text',
                    'class'         => 'form-control ' . $error,
                    'name'          => 'evaluator',
                    'id'            => 'evaluator',
                    'placeholder'   => 'Masukkan Nama Penilai',
                    'value'         => isset($data['records']['evaluator']) ? $data['records']['evaluator'] : set_value('evaluator'),
                    'required'      => true,
                ];
                echo form_label('Nama Penilai', 'evaluator', ['class' => 'form-label']);
                echo form_input($item); ?>
                <div class="invalid-feedback">
                    <?php echo validation_show_error('evaluator') ?>
                </div>
            </div>

            <!-- List Assessment -->
            <?php if ($data) : ?>
                <?php foreach ($data as $component => $aspects) : ?>
                    <div class="card mb-4 border-primary shadow-sm">
                        <h5 class="card-header bg-primary text-white fw-bold d-flex justify-content-between align-items-center">
                            <?php echo $component; ?>
                            <span class="badge bg-info"><?php echo count($aspects); ?> Aspek</span>
                        </h5>
                        <div class="card-body mt-3">
                            <?php foreach ($aspects as $aspect => $detail) : ?>
                                <div class="mb-4 p-4 border rounded shadow-sm">
                                    <h5 class="fw-semibold text-secondary mb-3">
                                        <i class="bi bi-box-seam me-2"></i><?php echo $aspect; ?>
                                        <span class="badge bg-success ms-2"><?php echo count($detail['questions']); ?> Pertanyaan</span>
                                    </h5>

                                    <?php foreach ($detail['questions'] as $question) : ?>
                                        <div class="row my-3">
                                            <div class="col-12">
                                                <div class="mb-2">
                                                    <strong class="fs-5">Pertanyaan: <?php echo $question['question']; ?></strong>
                                                </div>
                                                <div class="alert alert-secondary" role="alert">
                                                    <?php echo str_replace(["\\n", "\n"], "<br>", $question['assessment_criteria']); ?>
                                                </div>
                                            </div>
                                            <div class="col-12 mt-2">
                                                <?php
                                                $option = 'class="form-select select2 w-100" required';
                                                $value = '';
                                                if (!empty($record['records'])) {
                                                    foreach ($record['records'] as $instrumen) {
                                                        if ($instrumen['aspect_question_id'] === $question['aspect_question_id']) {
                                                            $value = isset($instrumen['id_instrumen']) ? $instrumen['id_instrumen'] : "";
                                                            break;
                                                        }
                                                    }
                                                }
                                                $name = $question['aspect_question_id'];
                                                echo form_dropdown($name, $question['question_instrument'], $value, $option);
                                                ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else : ?>
                <h4 class="text-center text-muted">Komponen Penilaian Belum Diatur</h4>
            <?php endif; ?>

            <!-- Submit Button -->
            <div class="text-end mt-4">
                <a href="<?php echo site_url('interview/interview_group/list') ?>" class="btn btn-outline-secondary">Kembali</a>
                <?php echo form_button(['type' => 'submit', 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
            </div>
            <?php echo form_close() ?>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: '- Silahkan Pilih Penilaian -'
        });

        // Submit form pakai AJAX
        $('#assesmentForm').on('submit', function(e) {
            e.preventDefault(); // stop form submit biasa (reload halaman)

            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalBtnText = submitBtn.text();

            $.ajax({
                url: form.attr('action'), // otomatis ambil dari form_open($record['url'])
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                beforeSend: function() {
                    submitBtn.prop('disabled', true).text('Menyimpan...');
                },
                success: function(response) {
                    if (response.status === 'success') {
                        alert(response.message || 'Data berhasil disimpan');

                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    } else {
                        alert(response.error || 'Terjadi kesalahan, silakan coba lagi');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    alert('Gagal menyimpan data');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text(originalBtnText);
                }
            });
        });
    });
</script>
<?php echo $this->endSection() ?>