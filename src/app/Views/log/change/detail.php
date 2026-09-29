<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'Log', 'title' => $title]); ?>

    <div class="row" data-aos="fade-right">
        <div class="col">
            <div class="card mb-4">
                <h5 class="card-header"><?php echo $title; ?></h5>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-2 fw-bold">Username</div>
                        <div class="col-sm-10"><?php echo kv_decrypt($result['username'], getenv('APP_KEY')); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 fw-bold">IP Address</div>
                        <div class="col-sm-10"><?php echo $result['ip_address']; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 fw-bold">Jenis</div>
                        <div class="col-sm-10"><?php echo $result['types']; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 fw-bold">Query</div>
                        <div class="col-sm-10"><?php echo $result['query']; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 fw-bold">Data</div>
                        <div class="col-sm-10"><?php echo $result['notes']; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 fw-bold">User Data</div>
                        <div class="col-sm-10"><?php echo $result['user_data']; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 fw-bold">Timestamp</div>
                        <div class="col-sm-10"><?php echo $result['created_at']; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="d-flex justify-content-end gap-3">
                            <?php echo form_button(['type' => 'button', 'id' => 'btn_cancel_' . $uri_name_js_id, 'content' => 'Kembali', 'class' => 'btn btn-outline-secondary btn-cancel']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php echo $this->endSection(); ?>

    <?php echo $this->section('javascript'); ?>
    <script type="text/javascript">
        $(document).ready(function() {
            $("#btn_cancel_<?php echo $uri_name_js_id; ?>").click(function() {
                redirect(site_url + uri_name);
            });
        });
    </script>
    <?php echo $this->endSection(); ?>