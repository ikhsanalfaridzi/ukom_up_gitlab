<?php echo $this->extend('layouts/auth') ?>

<?php echo $this->section('content') ?>
<style>
    body.auth-body-bg {
        background: linear-gradient(135deg, #eef2ff 0%, #f5f3ff 45%, #e0e7ff 100%);
    }

    .login-hero {
        position: fixed;
        inset: 0;
        overflow: hidden;
    }

    .login-hero .blob {
        position: absolute;
        border-radius: 50%;
        filter: blur(70px);
        opacity: .65;
        z-index: 0;
        animation: blobFloat 14s ease-in-out infinite alternate;
    }

    .login-hero .b1 {
        width: 420px;
        height: 420px;
        top: -120px;
        left: -100px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
    }

    .login-hero .b2 {
        width: 380px;
        height: 380px;
        bottom: -140px;
        right: -80px;
        background: linear-gradient(135deg, #22d3ee, #818cf8);
        animation-delay: -6s;
    }

    .login-hero .b3 {
        width: 260px;
        height: 260px;
        top: 55%;
        left: 62%;
        background: linear-gradient(135deg, #f0abfc, #a5b4fc);
        animation-delay: -10s;
    }

    @keyframes blobFloat {
        to {
            transform: translateY(24px) scale(1.06);
        }
    }

    .login-hero::after {
        content: "";
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(99, 102, 241, .12) 1px, transparent 1px);
        background-size: 22px 22px;
        z-index: 0;
    }

    .login-shell {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        padding: 1.5rem;
    }

    .login-card {
        width: 100%;
        max-width: 440px;
        background: rgba(255, 255, 255, .78);
        backdrop-filter: blur(18px);
        border: 1px solid rgba(255, 255, 255, .7);
        border-radius: 1.4rem;
        box-shadow: 0 24px 60px -24px rgba(67, 56, 202, .35);
        padding: 2.4rem 2.2rem;
        animation: cardIn .6s cubic-bezier(.16, 1, .3, 1) both;
    }

    @keyframes cardIn {
        from {
            opacity: 0;
            transform: translateY(26px) scale(.98);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }

    .login-card .logo-wrap {
        display: flex;
        justify-content: center;
        margin-bottom: 1.4rem;
    }

    .login-card .logo-wrap img {
        height: 74px;
        object-fit: contain;
    }

    .login-card .login-title {
        font-weight: 700;
        color: #1e1b4b;
        letter-spacing: -.3px;
    }

    .login-card .form-label {
        color: #4338ca;
        font-size: .82rem;
        font-weight: 600;
        letter-spacing: .4px;
        text-transform: uppercase;
    }

    .login-card .form-control {
        background: #ffffff;
        border: 1.5px solid #e0e7ff;
        color: #1e1b4b;
        height: 48px;
        border-radius: .7rem;
        transition: border-color .2s ease, box-shadow .2s ease;
    }

    .login-card .form-control::placeholder {
        color: #a5b4fc;
    }

    .login-card .form-control:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, .14);
        background: #fff;
        color: #1e1b4b;
    }

    .input-icon {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        color: #a5b4fc;
        font-size: 1.1rem;
        pointer-events: none;
    }

    .login-card .auth-pass-inputgroup .password-addon {
        right: 0;
        top: 0;
        height: 48px;
        width: 46px;
        line-height: 48px;
        border: 0;
        color: #94a3b8;
    }

    .login-card .auth-pass-inputgroup .password-addon:hover {
        color: #6366f1;
    }

    .btn-login {
        height: 50px;
        border: 0;
        border-radius: .8rem;
        font-weight: 600;
        letter-spacing: .4px;
        color: #fff;
        background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        box-shadow: 0 14px 28px -10px rgba(99, 102, 241, .6);
        transition: transform .15s ease, box-shadow .2s ease, filter .2s ease;
    }

    .btn-login:hover,
    .btn-login:focus {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 20px 34px -12px rgba(99, 102, 241, .7);
        filter: saturate(1.08);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    .captcha-box {
        background: #ffffff;
        border: 1.5px solid #e0e7ff;
        border-radius: .7rem;
        padding: .5rem;
    }

    .captcha-box img {
        display: block;
        flex: 1;
        min-width: 0;
        height: 46px;
        object-fit: contain;
        border-radius: .45rem;
        cursor: pointer;
        background: #f5f7ff;
    }

    .captcha-box .btn-refresh {
        height: 46px;
        width: 46px;
        flex: 0 0 46px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #e0e7ff;
        color: #6366f1;
        border-radius: .55rem;
        background: #f8faff;
        transition: all .2s ease;
    }

    .captcha-box .btn-refresh:hover {
        border-color: #a5b4fc;
        background: #eef2ff;
        transform: rotate(20deg);
    }

    .login-footer {
        color: #94a3b8;
        font-size: .78rem;
    }

    .alert-error-box {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #b91c1c;
        border-radius: .7rem;
    }

    @keyframes fadeUpS {
        from {
            opacity: 0;
            transform: translateY(14px);
        }
        to {
            opacity: 1;
            transform: none;
        }
    }

    .stagger {
        animation: fadeUpS .45s ease both;
    }

    .stagger-1 {
        animation-delay: .08s;
    }

    .stagger-2 {
        animation-delay: .16s;
    }

    .stagger-3 {
        animation-delay: .24s;
    }

    .stagger-4 {
        animation-delay: .32s;
    }
</style>

<div class="login-hero">
    <div class="blob b1"></div>
    <div class="blob b2"></div>
    <div class="blob b3"></div>
</div>

<div class="login-shell">
    <div class="login-card">
        <div class="logo-wrap stagger">
            <img src="<?php echo base_url('vlz/images/logo-imipas.png') ?>" alt="IMIPAS">
        </div>

        <div class="text-center stagger stagger-1">
            <h3 class="login-title mb-1">Selamat Datang !</h3>
            <p class="text-muted mb-4">Silahkan login untuk melanjutkan ke aplikasi</p>
        </div>

        <div id="alert_error" class="alert alert-error-box alert-dismissible d-none" role="alert">
            <i class="ri-error-warning-line me-1 align-middle"></i>
            <span id="alert_message"></span>
        </div>

        <?php echo form_open($uri_name, ['id' => 'form_input_' . $uri_name_js_id, 'autocomplete' => 'off']) ?>
        <?php echo csrf_field(); ?>

        <div class="mb-3 position-relative stagger stagger-2">
            <?php echo form_label('Username', 'username', ['class' => 'form-label']) ?>
            <?php echo form_input('username', '', 'id="username" class="form-control" placeholder="Masukkan Username" autofocus required') ?>
            <span class="input-icon"><i class="ri-user-3-fill"></i></span>
        </div>

        <div class="mb-3 position-relative stagger stagger-2">
            <div class="mb-1">
                <?php echo form_label('Password', 'password', ['class' => 'form-label mb-0']) ?>
            </div>
            <div class="position-relative auth-pass-inputgroup">
                <?php echo form_password('password', '', 'id="password-input" class="form-control password-input" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" required') ?>
                <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon">
                    <i class="ri-eye-fill align-middle"></i>
                </button>
            </div>
        </div>

        <div class="mb-4 stagger stagger-3">
            <?php echo form_label('Captcha', 'kv-captcha', ['class' => 'form-label']) ?>
            <div class="captcha-box d-flex gap-2 align-items-stretch mb-2">
                <span id="kv-loading" style="display: none; line-height: 46px;" class="text-muted small">Loading...</span>
                <img id="kv-image" src="<?php echo site_url('authentication/captcha') ?>" alt="Captcha Image" onclick="refreshCaptcha()" title="Klik untuk ganti">
                <button type="button" class="btn-refresh" onclick="refreshCaptcha()" aria-label="Ganti Captcha">
                    <i class="ri-refresh-line"></i>
                </button>
            </div>
            <?php echo form_input('kv-captcha', '', 'id="kv-captcha" class="form-control" placeholder="Ketik teks pada gambar" required') ?>
        </div>

        <div class="d-grid mb-3 stagger stagger-4">
            <?php echo form_button(['type' => 'submit', 'content' => 'Masuk <i class="ri-arrow-right-line ms-1"></i>', 'class' => 'btn btn-login']) ?>
        </div>
        <?php echo form_close() ?>

        <div class="text-center">
            <p class="login-footer mb-0">&copy; 2026 IMIPAS &middot; Uji Kompetensi Melalui Wawancara</p>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<script src="<?php echo base_url('vlz/js/form-handler.js'); ?>" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('#announcement').modal('show');

        $('#password-addon').on('click', function() {
            const input = $('#password-input');
            const isText = input.attr('type') === 'text';
            input.attr('type', isText ? 'password' : 'text');
            $(this).find('i').toggleClass('ri-eye-fill ri-eye-off-fill');
        });

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
        const loading = document.getElementById('kv-loading');

        if (loading) {
            loading.style.display = 'inline';
        }

        const done = function() {
            if (loading) {
                loading.style.display = 'none';
            }
        };

        captchaImg.onload = done;
        captchaImg.src = "<?php echo site_url('authentication/captcha') ?>?t=" + Date.now();
    }
</script>
<?php echo $this->endSection() ?>