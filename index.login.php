<?php echo $this->extend('layouts/auth') ?>

<?php echo $this->section('content') ?>

<?php echo form_open($uri_name, ['id' => 'form_input_' . $uri_name_js_id, 'autocomplete' => 'off']) ?>
<?php echo csrf_field(); ?>

<div class="mb-3">
    <?php echo form_label('Username', 'username', ['class' => 'form-label']) ?>
    <?php echo form_input('username', '', 'id="username" class="form-control" placeholder="Masukkan Username" autofocus required') ?>
</div>

<div class="mb-3">
    <?php echo form_label('Password', 'password', ['class' => 'form-label']) ?>
    <div class="input-group-wrapper">
        <?php echo form_password('password', '', 'id="password-input" class="form-control password-input" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required') ?>
        <button class="btn-eye password-addon" type="button" id="password-addon">
            <i class="ri-eye-fill"></i>
        </button>
    </div>
</div>

<div class="mb-3">
    <div class="captcha-wrapper">
        <span id="kv-loading" style="display: none; font-size:0.82rem; color:#8a94a8;">Loading...</span>
        <img id="kv-image" src="<?php echo site_url('authentication/captcha') ?>" alt="Captcha Image">
        <button type="button" class="btn-refresh" onclick="refreshCaptcha()">
            <i class="ri-refresh-line"></i>
        </button>
    </div>
    <?php echo form_input('kv-captcha', '', 'id="kv-captcha" class="form-control" placeholder="Masukkan Captcha" required') ?>
</div>

<div class="mb-2">
    <?php echo form_button(['type' => 'submit', 'content' => 'Masuk', 'class' => 'btn-login']) ?>
</div>

<?php echo form_close() ?>

<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<script src="<?php echo base_url('vlz/js/form-handler.js'); ?>" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#announcement').modal('show');

        handleFormSubmit(`#form_input_<?php echo $uri_name_js_id; ?>`)
            .onSuccess(function(res) {
                redirect(res.redirect_url);
            })
            .onError(function(err) {
                refreshCaptcha();

                $('#alert_message').html(err.responseJSON?.message || 'Terjadi kesalahan ketika melakukan login!');
                $('#alert_error').removeClass('d-none');
            })
            .init();
    });

    function refreshCaptcha() {
        const captchaImg = document.getElementById('kv-image');
        captchaImg.src = "<?php echo site_url('authentication/captcha') ?>?t=" + Date.now();
    }
</script>
<?php echo $this->endSection() ?>