<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('css') ?>
<link href="<?php echo base_url('vlz/libs/select2/select2.min.css') ?>" rel="stylesheet" type="text/css" />
<link href="<?php echo base_url('vlz/libs/flatpickr/flatpickr.min.css') ?>" rel="stylesheet" type="text/css" />
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'Pengaturan Umum', 'title' => $title]); ?>

    <?php echo form_open_multipart($uri_name . '/' . $form_action, ['id' => 'form_input_' . $uri_name_js_id, 'autocomplete' => 'off']) ?>
    <?php echo csrf_field() ?>
    <div class="row" data-aos="fade-right">
        <div class="card mb-4">
            <h5 class="card-header d-flex align-items-center gap-2"><i class="ri-user-fill"></i> Identitas Pengguna</h5>
            <div class="card-body">
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <?php
                            $item = [
                                'type'          => 'text',
                                'class'         => 'form-control',
                                'name'          => 'full_name',
                                'id'            => 'full_name',
                                'placeholder'   => 'Masukkan Nama Lengkap',
                                'value'         => isset($personal['full_name']) ? $personal['full_name'] : set_value('full_name')
                            ];
                            echo form_label('Nama Lengkap' . is_required(), 'full_name', ['class' => 'form-label']);
                            echo form_input($item); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <?php
                            $item = [
                                'type'          => 'email',
                                'class'         => 'form-control',
                                'name'          => 'personal_email',
                                'id'            => 'personal_email',
                                'placeholder'   => 'Masukkan Email Pribadi',
                                'value'         => isset($personal['personal_email']) ? $personal['personal_email'] : set_value('personal_email')
                            ];
                            echo form_label('Email Pribadi', 'personal_email', ['class' => 'form-label']);
                            echo form_input($item); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-12 col-lg-6 mb-3">
                            <?php
                            $item = [
                                'type'          => 'number',
                                'class'         => 'form-control',
                                'name'          => 'phone_number',
                                'id'            => 'phone_number',
                                'placeholder'   => 'Masukkan Nomor Telepon',
                                'value'         => isset($personal['phone_number']) ? $personal['phone_number'] : set_value('phone_number')
                            ];
                            echo form_label('Nomor Telepon', 'phone_number', ['class' => 'form-label']);
                            echo form_input($item); ?>
                        </div>
                        <div class="col-sm-12 col-lg-6 mb-3">
                            <?php
                            $item = [
                                'type'          => 'number',
                                'class'         => 'form-control',
                                'name'          => 'wa_number',
                                'id'            => 'wa_number',
                                'placeholder'   => 'Masukkan Nomor Whatsapp',
                                'value'         => isset($personal['wa_number']) ? $personal['wa_number'] : set_value('wa_number')
                            ];
                            echo form_label('Nomor Whatsapp', 'wa_number', ['class' => 'form-label']);
                            echo form_input($item); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-lg-6 mb-3">
                            <?php
                            $gender_options = ['' => '- Silahkan Pilih -', 'L' => 'Laki-laki', 'P' => 'Perempuan'];
                            $gender_value = isset($personal['gender']) ? $personal['gender'] : '';
                            echo form_label('Jenis Kelamin' . is_required(), 'gender', ['class' => 'form-label']);
                            echo form_dropdown('gender', $gender_options, $gender_value, 'id="gender" class="form-select select2"');
                            ?>
                        </div>
                        <div class="col-sm-12 col-lg-6 mb-3">
                            <?php
                            $item = [
                                'type'          => 'text',
                                'class'         => 'form-control',
                                'name'          => 'date_of_birth',
                                'id'            => 'date_of_birth',
                                'placeholder'   => 'Pilih Tanggal Lahir',
                                'value'         => isset($personal['date_of_birth']) ? date('d-m-Y', strtotime($personal['date_of_birth'])) : set_value('date_of_birth')
                            ];
                            echo form_label('Tanggal Lahir' . is_required(), 'date_of_birth', ['class' => 'form-label']);
                            echo form_input($item); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" data-aos="fade-left">
        <div class="card mb-4">
            <h5 class="card-header d-flex align-items-center gap-2"><i class="ri-government-line"></i> Kepegawaian</h5>
            <div class="card-body">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12 col-lg-6 mb-3">
                            <?php
                            $item = [
                                'type'          => 'number',
                                'class'         => 'form-control',
                                'name'          => 'employee_number',
                                'id'            => 'employee_number',
                                'maxlength'     => 18,
                                'placeholder'   => 'Masukkan NIP',
                                'value'         => isset($personnel['employee_number']) ? $personnel['employee_number'] : set_value('employee_number')
                            ];
                            echo form_label('NIP' . is_required(), 'employee_number', ['class' => 'form-label']);
                            echo form_input($item); ?>
                        </div>
                        <div class="col-sm-12 col-lg-6 mb-3">
                            <?php echo form_label('Unit Kerja/Satuan Kerja' . is_required(), 'organization_structure_id', ['class' => 'form-label']); ?>
                            <select name="organization_structure_id" id="organization_structure_id" class="form-select select2">
                                <option value="">- Silahkan Pilih -</option>
                                <?php
                                    $selected_val = isset($personnel['organization_structure_id']) ? $personnel['organization_structure_id'] : '';
                                    
                                    if (is_array($organization_options)) {
                                        foreach($organization_options as $val => $text) {
                                            $selected = ($selected_val == $val) ? 'selected' : '';
                                            echo "<option value=\"$val\" $selected>$text</option>";
                                        }
                                    } else {
                                        echo $organization_options; 
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 col-lg-6 mb-3">
                            <?php
                            $option = 'id="position_id" class="form-select select2"';
                            $value = isset($personnel['position_id']) ? $personnel['position_id'] : "";
                            echo form_label('Jabatan' . is_required(), 'position_id', ['class' => 'form-label']);
                            echo form_dropdown('position_id', $position_options, $value, $option);
                            ?>
                        </div>
                        <div class="col-sm-12 col-lg-6 mb-3">
                            <?php
                            $option = 'id="position_grade_id" class="form-select select2"';
                            $value = isset($personnel['position_grade_id']) ? $personnel['position_grade_id'] : "";
                            echo form_label('Grade / Eselon', 'position_grade_id', ['class' => 'form-label']);
                            echo form_dropdown('position_grade_id', $grade_options, $value, $option);
                            ?>
                        </div>
                        <div class="col-sm-12 col-lg-6 mb-3">
                            <?php
                            $option = 'id="assortment_id" class="form-select select2"';
                            $value = isset($personnel['assortment_id']) ? $personnel['assortment_id'] : "";
                            echo form_label('Pangkat' . is_required(), 'assortment_id', ['class' => 'form-label']);
                            echo form_dropdown('assortment_id', $assortment_options, $value, $option);
                            ?>
                        </div>
                        <div class="col-sm-12 col-lg-6 mb-3">
                            <?php
                            $option = 'id="employment_id" class="form-select select2"';
                            $value = isset($personnel['employment_id']) ? $personnel['employment_id'] : "";
                            echo form_label('Status Kepegawaian' . is_required(), 'employment_id', ['class' => 'form-label']);
                            echo form_dropdown('employment_id', $employment_options, $value, $option);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" data-aos="fade-right">
        <div class="card mb-4">
            <h5 class="card-header d-flex align-items-center gap-2"><i class="ri-account-box-line"></i> Akun</h5>
            <div class="card-body">
                <div class="modal-body">
                    <div class="row">
                        <div class="col mb-3">
                            <?php
                            $item = [
                                'type'          => 'text',
                                'class'         => 'form-control',
                                'name'          => 'username',
                                'id'            => 'username',
                                'placeholder'   => 'Masukkan Username',
                                'value'         => isset($user['username']) ? kv_decrypt($user['username'], getenv('APP_KEY')) : set_value('username')
                            ];
                            echo form_label('Username', 'username', ['class' => 'form-label']);
                            echo form_input($item); ?>
                        </div>
                    </div>

                    <div class="col mb-3">
                        <?php echo form_label('Password' . is_required(), 'password', ['class' => 'form-label']); ?>
                        <div class="position-relative auth-pass-inputgroup mb-3">
                            <?php
                            $item = [
                                'class'         => 'form-control pe-5 password-input',
                                'name'          => 'password',
                                'id'            => 'password-input',
                                'placeholder'   => '&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;',
                            ];
                            echo form_password($item); ?>
                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon"><i class="ri-eye-fill align-middle"></i></button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <?php echo form_label('Konfirmasi Password' . is_required(), 'confirm_password', ['class' => 'form-label']); ?>
                        <div class="position-relative auth-pass-inputgroup mb-3">
                            <?php
                            $item = [
                                'class'         => 'form-control pe-5 password-input',
                                'name'          => 'confirm_password',
                                'id'            => 'confirm-password-input',
                                'placeholder'   => '&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;',
                            ];
                            echo form_password($item); ?>
                            <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="confirm-password-addon"><i class="ri-eye-fill align-middle"></i></button>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <?php
                        $option = 'id="group_id" class="form-select select2" multiple="multiple"';
                        $value = isset($group_ids) ? $group_ids : "";
                        echo form_label('Grup Akses' . is_required(), 'group_id', ['class' => 'form-label']);
                        echo form_dropdown('group_id[]', $group_options, $value, $option);
                        ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col d-flex gap-5">
                        <div>
                            <?php
                            $active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_active',
                                'id'        => 'active_value',
                                'value'     => 1,
                                'checked'   => isset($user['is_active']) && $user['is_active'] === "1" ? true : false
                            ];
                            $non_active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_active',
                                'id'        => 'non_active_value',
                                'value'     => 0,
                                'checked'   => !isset($user['is_active']) || $user['is_active'] !== "1" ? true : false
                            ];
                            echo form_label('Aktif' . is_required(), 'is_active', ['class' => 'form-label']);
                            ?>
                            <div class="d-flex gap-3">
                                <div class="d-flex flex-row gap-2 mb-1">
                                    <?php
                                    echo form_radio($active);
                                    echo form_label('Ya', 'active_value');
                                    ?>
                                </div>
                                <div class="d-flex flex-row gap-2">
                                    <div class="mb-2">
                                        <?php
                                        echo form_radio($non_active);
                                        echo form_label('Tidak', 'non_active_value');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <?php
                            $active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_change_password',
                                'id'        => 'change_password_value',
                                'value'     => 1,
                                'checked'   => isset($user['is_change_password']) && $user['is_change_password'] === "1" ? true : false
                            ];
                            $non_active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_change_password',
                                'id'        => 'non_change_password_value',
                                'value'     => 0,
                                'checked'   => !isset($user['is_change_password']) || $user['is_change_password'] !== "1" ? true : false
                            ];
                            echo form_label('Wajib Ganti Password' . is_required(), 'is_change_password', ['class' => 'form-label']);
                            ?>
                            <div class="d-flex gap-3">
                                <div class="d-flex flex-row gap-2 mb-1">
                                    <?php
                                    echo form_radio($active);
                                    echo form_label('Ya', 'change_password_value');
                                    ?>
                                </div>
                                <div class="d-flex flex-row gap-2">
                                    <div class="mb-2">
                                        <?php
                                        echo form_radio($non_active);
                                        echo form_label('Tidak', 'non_change_password_value');
                                        ?>
                                    </div>
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
                            echo form_label('Catatan', 'description', ['class' => 'form-label']);
                            echo form_textarea($item); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-12 d-flex flex-row justify-content-center gap-3">
            <?php echo form_button(['type' => 'button', 'id' => 'btn_cancel_' . $uri_name_js_id, 'content' => 'Kembali', 'class' => 'btn btn-outline-secondary btn-cancel']) ?>
            <?php echo form_button(['type' => 'submit', 'id' => 'btn_save_' . $uri_name_js_id, 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
        </div>
    </div>
    <?php echo form_close() ?>
</div>
<?php echo $this->endSection(); ?>

<?php echo $this->section('javascript'); ?>
<script src="<?php echo base_url('vlz/libs/select2/select2.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo base_url('vlz/libs/flatpickr/flatpickr.min.js'); ?>"></script>
<script src="<?php echo base_url('vlz/js/form-handler.js'); ?>" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: '- Silahkan Pilih -',
            allowClear: true,
            width: '100%'
        });

        flatpickr("#date_of_birth", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d-m-Y",
            allowInput: true
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
                let message = err.responseJSON?.message;

                if (err.responseJSON?.message === 'Validation failed') {
                    const errors = err.responseJSON?.errors;
                    message = errors && Object.keys(errors).length
                        ? 'Mohon periksa kembali isian berikut:<br>- ' + Object.values(errors).join('<br>- ')
                        : 'Mohon periksa kembali isian Anda.';
                    showAlert('Error!', message, err.responseJSON?.status);
                    return;
                }

                try {
                    // If message is JSON
                    const parsed = JSON.parse(message);
                    // Get message from object
                    message = Object.values(parsed).join('<br>'); // Error if too many fields
                } catch (e) {
                    // If parse fail do nothing
                }

                showAlert('Error!', message, err.responseJSON?.status);
            })

            .init();
    });
</script>
<?php echo $this->endSection(); ?>