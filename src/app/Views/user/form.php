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
                                $error = validation_show_error('nama_lengkap') ? 'is-invalid' : '';
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control ' . $error,
                                    'name'          => 'nama_lengkap',
                                    'id'            => 'nama_lengkap',
                                    'placeholder'   => 'Masukkan Nama Lengkap',
                                    'value'         => isset($data['records']['nama_lengkap']) ? $data['records']['nama_lengkap'] : set_value('nama_lengkap')
                                ];
                                echo form_label('Nama Lengkap', 'nama_lengkap', ['class' => 'form-label']);
                                echo form_input($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('nama_lengkap') ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('username') ? 'is-invalid' : '';
                                $item = [
                                    'type'        => 'text',
                                    'class'       => 'form-control ' . $error,
                                    'name'        => 'username',
                                    'id'          => 'username',
                                    'placeholder' => 'Masukkan Username',
                                    'value'       => isset($data['records']['username']) ? $data['records']['username'] : set_value('username')
                                ];
                                echo form_label('Username', 'username', ['class' => 'form-label']);
                                echo form_input($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('username') ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('email') ? 'is-invalid' : '';
                                $item = [
                                    'type'          => 'email',
                                    'class'         => 'form-control ' . $error,
                                    'name'          => 'email',
                                    'id'            => 'email',
                                    'placeholder'   => 'Masukkan Email',
                                    'value'         => isset($data['records']['email']) ? $data['records']['email'] : set_value('email')
                                ];
                                echo form_label('Email', 'email', ['class' => 'form-label']);
                                echo form_input($item); ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('email') ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $error = validation_show_error('role') ? 'is-invalid' : '';
                                $item = [
                                    ''                => '',
                                    'Super Admin'     => 'Super Admin',
                                    'Penyelenggara'   => 'Penyelenggara',
                                    'Penilai'         => 'Penilai',
                                ];
                                $option = 'id="role" class="form-select select2 ' . ($error ? 'is-invalid' : '') . '"';
                                $value = isset($data['records']['role']) ? $data['records']['role'] : "";
                                echo form_label('Role', 'role', ['class' => 'form-label']);
                                echo form_dropdown('role', $item, $value, $option);
                                ?>
                                <div class="invalid-feedback">
                                    <?php echo validation_show_error('role') ?>
                                </div>
                            </div>
                        </div>
                        <div class="row form-password-toggle">
                            <div class="col mb-3">
                                <?php echo form_label('Password', 'password', ['class' => 'form-label']); ?>
                                <div class="input-group input-group-merge">
                                    <?php
                                    $error = validation_show_error('password') ? 'is-invalid' : '';
                                    $item = [
                                        'type'          => 'password',
                                        'class'         => 'form-control ' . $error,
                                        'name'          => 'password',
                                        'id'            => 'password',
                                        'placeholder'   => '&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;',
                                    ];
                                    echo form_input($item); ?>
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                    <div class="invalid-feedback">
                                        <?php echo validation_show_error('password') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row form-password-toggle">
                            <div class="col mb-3">
                                <?php echo form_label('Konfirmasi Password', 'confirm_password', ['class' => 'form-label']); ?>
                                <div class="input-group input-group-merge">
                                    <?php
                                    $error = validation_show_error('confirm_password') ? 'is-invalid' : '';
                                    $item = [
                                        'type'          => 'password',
                                        'class'         => 'form-control ' . $error,
                                        'name'          => 'confirm_password',
                                        'id'            => 'confirm_password',
                                        'placeholder'   => '&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;',
                                    ];
                                    echo form_input($item); ?>
                                    <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
                                    <div class="invalid-feedback">
                                        <?php echo validation_show_error('confirm_password') ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <small>
                            <?php echo isset($data['text']) ? $data['text'] : ''; ?>
                        </small>
                    </div>
                    <div class="modal-footer">
                        <a href="<?php echo site_url('user/user') ?>" class="btn btn-outline-secondary">Kembali</a>
                        <?php echo form_button(['type' => 'submit', 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
                    </div>
                    <?php echo form_close() ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection(); ?>

<?php echo $this->section('javascript'); ?>
<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: '- Silahkan Pilih Role -',
            allowClear: true,
            width: '100%'
        });
    })
</script>
<?php echo $this->endSection(); ?>