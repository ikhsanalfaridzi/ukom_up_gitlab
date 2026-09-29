<!doctype html>
<html lang="en" data-layout="vertical" data-layout-style="detached" data-sidebar="light" data-topbar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>

    <meta charset="utf-8" />
    <title><?php echo lang('Errors.pageNotFound') ?> | Kev Codebase</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Aplikasi Kev Codebase PT Baru Transformasi Indonesia" name="description" />
    <meta content="PT Baru Transformasi Indonesia" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?php echo base_url('vlz/images/favicon.ico') ?>">

    <!-- Layout config Js -->
    <script src="<?php echo base_url('vlz/js/layout.js') ?>"></script>
    <!-- Bootstrap Css -->
    <link href="<?php echo base_url('vlz/css/bootstrap.min.css') ?>" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="<?php echo base_url('vlz/css/icons.min.css') ?>" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?php echo base_url('vlz/css/app.min.css') ?>" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="<?php echo base_url('vlz/css/custom.min.css') ?>" rel="stylesheet" type="text/css" />

</head>

<body>

    <!-- auth-page wrapper -->
    <div class="auth-page-wrapper auth-bg-cover py-5 d-flex justify-content-center align-items-center min-vh-100">
        <div class="bg-overlay"></div>
        <!-- auth-page content -->
        <div class="auth-page-content overflow-hidden pt-lg-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-5">
                        <div class="card overflow-hidden">
                            <div class="card-body p-4">
                                <div class="text-center">
                                    <img src="<?php echo base_url('vlz/images/errors/not_found.webp') ?>" alt="'Error 404" type="image/webp">
                                    <h1 class="text-primary mb-4">Oops !</h1>
                                    <h4 class="text-uppercase">Sorry, Page not Found 😭</h4>
                                    <p class="text-muted mb-4">
                                        <?php if (ENVIRONMENT !== 'production') : ?>
                                            <?= nl2br(esc($message)) ?>
                                        <?php else : ?>
                                            <?= lang('Errors.sorryCannotFind') ?>
                                        <?php endif ?>
                                    </p>
                                    <a href="<?php echo site_url('home/dashboard') ?>" class="btn btn-primary"><i class="mdi mdi-home me-1"></i>Homepage</a>
                                </div>
                            </div>
                        </div>
                        <!-- end card -->
                    </div>
                    <!-- end col -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
        </div>
        <!-- end auth page content -->
    </div>
    <!-- end auth-page-wrapper -->

    <!-- JAVASCRIPT -->
    <script src="<?php echo base_url('vlz/libs/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?php echo base_url('vlz/libs/simplebar/simplebar.min.js') ?>"></script>
    <script src="<?php echo base_url('vlz/libs/node-waves/waves.min.js') ?>"></script>
    <script src="<?php echo base_url('vlz/libs/feather-icons/feather.min.js') ?>"></script>
    <script src="<?php echo base_url('vlz/js/pages/plugins/lord-icon-2.1.0.js') ?>"></script>
    <script src="<?php echo base_url('vlz/js/plugins.js') ?>"></script>

</body>

</html>