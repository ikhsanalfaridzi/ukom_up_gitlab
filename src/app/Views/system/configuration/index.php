<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('css') ?>
<?php echo $this->include('layouts/partials/datatables-css') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'System', 'title' => $title]); ?>

    <div class="row" data-aos="fade-up">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?php echo $title ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 border-end border-2">
                        <div class="nav flex-column nav-pills text-center" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <a class="nav-link mb-2 active" id="web-app-tab" data-bs-toggle="pill" href="#web-app" role="tab" aria-controls="web-app" aria-selected="true">Web App Configuration</a>
                            <a class="nav-link mb-2" id="recaptcha-tab" data-bs-toggle="pill" href="#recaptcha" role="tab" aria-controls="recaptcha" aria-selected="false">Recaptcha Configuration</a>
                            <a class="nav-link mb-2" id="sso-tab" data-bs-toggle="pill" href="#sso" role="tab" aria-controls="sso" aria-selected="false">SSO Configuration</a>
                        </div>
                    </div><!-- end col -->
                    <div class="col-md-9">
                        <div class="tab-content text-muted ms-2 mt-4 mt-md-0" id="v-pills-tabContent">
                            <div class="tab-pane fade show active" id="web-app" role="tabpanel" aria-labelledby="web-app-tab">
                                <?php echo view('system/configuration/web_app_form'); ?>
                            </div>
                            <div class="tab-pane fade" id="recaptcha" role="tabpanel" aria-labelledby="recaptcha-tab">
                                <?php echo view('system/configuration/recaptcha_form'); ?>
                            </div>
                            <div class="tab-pane fade" id="sso" role="tabpanel" aria-labelledby="sso-tab">
                                <?php echo view('system/configuration/sso_form'); ?>
                            </div>
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
<?php echo $this->include('layouts/partials/datatables-scripts') ?>

<script type="text/javascript">

</script>
<?php echo $this->endSection() ?>