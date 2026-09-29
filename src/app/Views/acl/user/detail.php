<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'ACL', 'title' => $title]); ?>

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
                        <div class="col-sm-2 fw-bold">Grup Akses</div>
                        <div class="col-sm-10"><?php echo nullable_value($result['description']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 fw-bold">Aktif</div>
                        <div class="col-sm-10"><?php echo boolean_integer($result['is_active']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 fw-bold">Wajib Ganti Password</div>
                        <div class="col-sm-10"><?php echo boolean_integer($result['is_change_password']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-2 fw-bold">Catatan</div>
                        <div class="col-sm-10"><?php echo nullable_value($result['description']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="d-flex justify-content-end gap-3">
                            <?php echo form_button(['type' => 'button', 'id' => 'btn_cancel_' . $uri_name_js_id, 'content' => 'Kembali', 'class' => 'btn btn-outline-secondary btn-cancel']) ?>
                            <?php echo form_button(['type' => 'button', 'id' => 'btn_edit_' . $uri_name_js_id, 'content' => '<i class="ri-pencil-fill align-bottom me-2"></i>Edit', 'class' => 'btn btn-warning']) ?>
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

            $("#btn_edit_<?php echo $uri_name_js_id; ?>").click(function() {
                redirect(site_url + uri_name + '/form/' + '<?php echo $result['id'] ?>');
            });
        });
    </script>
    <?php echo $this->endSection(); ?>