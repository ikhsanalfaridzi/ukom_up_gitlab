<?php echo $this->extend('layouts/auth') ?>

<?php echo $this->section('content') ?>
<div class="container-xxl container-p-y d-flex justify-content-center align-items-center" style="height: 100vh;">
    <div class="misc-wrapper text-center">
        <h1 class="mb-2 mx-2">Unauthorized!</h1>
        <p class="mb-4 mx-2">You do not have permission to view this page using the credentials that you have provided while login.
            <br> Please contact your site administrator.
        </p>
        <a href="<?php echo site_url('/') ?>" class="btn btn-primary">Back to home</a>
        <div class="mt-3">
            <img src="<?php echo base_url('snt/assets') ?>/img/illustrations/unauthorized.png" alt="page-misc-error-light" width="500" class="img-fluid" data-app-dark-img="illustrations/page-misc-error-dark.png" data-app-light-img="illustrations/page-misc-error-light.png" />
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>