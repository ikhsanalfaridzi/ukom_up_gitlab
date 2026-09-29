<?php echo $this->include('layouts/partials/main') ?>

<head>

  <?php echo view('layouts/partials/title-meta', array('title' => $title)); ?>

  <!-- Page CSS -->
  <?php echo $this->renderSection('css') ?>

  <?php echo $this->include('layouts/partials/head-css') ?>

</head>

<body onload="hideLoading();">
  <script src="<?php echo base_url('vlz/libs/jquery/jquery-3.6.0.min.js'); ?>"></script>
  <script type="text/javascript">
    let base_url = '<?php echo base_url(); ?>';
    let site_url = '<?php echo site_url(); ?>';
    let uri_name = '<?php echo $uri_name ?? ""; ?>';
  </script>

  <!-- Begin page -->
  <div id="layout-wrapper">

    <?php echo $this->include('layouts/partials/menu') ?>

    <!-- Start right Content here -->
    <div class="main-content">

      <div class="page-content">
        <?php echo $this->renderSection('content') ?>
      </div>
      <!-- End Page-content -->

      <?php echo $this->include('layouts/partials/footer') ?>
    </div>
    <!-- end main content-->

  </div>
  <!-- END layout-wrapper -->

  <!-- Loader -->
  <div class="loading overlay">
    <div class="lds-ripple">
      <div></div>
      <div></div>
    </div>
  </div>

  <?php echo $this->include('layouts/partials/vendor-scripts') ?>

  <!-- Chart JS -->
  <script src="<?php echo base_url('vlz/libs/chart.js/chart.umd.js') ?>"></script>

  <!-- Page JS -->
  <?php echo $this->renderSection('javascript') ?>

  <!-- App js -->
  <script src="<?php echo base_url('vlz/js/app.js') ?>"></script>

</body>

</html>
