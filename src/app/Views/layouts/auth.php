<!doctype html>
<html lang="en" data-layout="vertical" data-layout-style="detached" data-sidebar="light" data-topbar="dark" data-sidebar-size="lg" data-sidebar-image="none" data-preloader="disable">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title><?php echo $title ?> | UJI KOMPETENSI MELALUI WAWANCARA</title>

    <meta name="description" content="" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo base_url('vlz/images/favicon.ico') ?>" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

    <!-- Icons Css -->
    <link href="<?php echo base_url('vlz/css/icons.min.css') ?>" rel="stylesheet" type="text/css" />
    <!-- Bootstrap Css -->
    <link href="<?php echo base_url('vlz/css/bootstrap.min.css') ?>" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="<?php echo base_url('vlz/css/app.min.css') ?>" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="<?php echo base_url('vlz/css/custom.min.css') ?>" rel="stylesheet" type="text/css" />

    <!-- BVAmbient Css -->
    <link rel="stylesheet" href="<?php echo base_url('snt/assets/vendor/bvambient/css/bvambient.css'); ?>">

    <script src="<?php echo base_url('vlz/js/layout.js') ?>"></script>
</head>

<body class="auth-body-bg">
    <div id="ambient" style="position: fixed; top: 0; left: 0; overflow: hidden; width: 100%; height: 100vh; filter: blur(10px); z-index: -1;"></div>
    <?php echo $this->renderSection('content') ?>

    <script src="<?php echo base_url('vlz/libs/jquery/jquery-3.6.0.min.js') ?>"></script>
    <script src="<?php echo base_url('vlz/libs/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?php echo base_url('snt/assets/vendor/bvambient/js/bvambient.js'); ?>"></script>

    <!-- SweetAlert2 -->
    <script src="<?php echo base_url('vlz/libs/sweetalert2/sweetalert2.all.min.js'); ?>"></script>

    <script type="text/javascript">
        // Minimal helpers for auth pages (utils.js is only loaded on admin pages)
        function redirect(url, type) {
            if (typeof type === "undefined") type = "_self";
            window.open(url, type);
        }

        function showAlert(title, message, status = "success") {
            Swal.fire({
                position: "center",
                icon: status,
                title: title,
                text: message,
                showConfirmButton: false,
                timer: 3000,
            });
        }
    </script>

    <?php echo $this->renderSection('javascript') ?>
</body>

</html>
