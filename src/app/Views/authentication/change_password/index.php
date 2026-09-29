<?php echo $this->extend('layouts/auth') ?>

<?php echo $this->section('content') ?>
<div class="container">
    <div class="row d-flex justify-content-center">
        <div class="col-8">
            <div class="card overflow-hidden">
                <div class="row g-0">
                    <div class="col">
                        <div class="p-lg-5 p-4">
                            <div class="text-center">
                                <h5 class="text-primary">Selamat Datang !</h5>
                                <p class="text-muted">Silahkan perbarui kata sandi anda untuk melanjutkan.</p>
                            </div>

                            <div class="mt-4">
                                <?php echo form_open($uri_name, ['id' => 'form_input_' . $uri_name_js_id, 'autocomplete' => 'off']) ?>
                                <?php echo csrf_field(); ?>

                                <div class="mb-3">
                                    <?php echo form_label('Password Baru', 'password', ['class' => 'form-label']) ?>
                                    <div class="position-relative auth-pass-inputgroup mb-3">
                                        <?php echo form_password('password', '', 'id="password" class="form-control pe-5 password-input" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required') ?>
                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password_btn"><i class="ri-eye-fill align-middle"></i></button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <?php echo form_label('Konfirmasi Password', 'confirm_password', ['class' => 'form-label']) ?>
                                    <div class="position-relative auth-pass-inputgroup mb-3">
                                        <?php echo form_password('confirm_password', '', 'id="confirm_password" class="form-control pe-5 password-input" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required') ?>
                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="confirm_password_btn"><i class="ri-eye-fill align-middle"></i></button>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-3">
                                    <a href="<?php echo site_url('authentication/logout') ?>" class="btn btn-outline-danger">Logout</a>
                                    <?php echo form_button(['type' => 'submit', 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
                                </div>
                                </form>
                                <?php echo form_close() ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<script src="<?php echo base_url('vlz/js/form-handler.js'); ?>" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        handleFormSubmit(`#form_input_<?php echo $uri_name_js_id; ?>`)
            .onSuccess(function(res) {
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
<?php echo $this->endSection() ?>