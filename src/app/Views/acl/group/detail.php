<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'ACL', 'title' => $title]); ?>

    <div class="row" data-aos="fade-right">
        <div class="col-sm-12 col-lg-6">
            <div class="card mb-4">
                <h5 class="card-header"><?php echo $title; ?></h5>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Nama Grup</div>
                        <div class="col-sm-8"><?php echo $result['group_name']; ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Deskripsi</div>
                        <div class="col-sm-8"><?php echo nullable_value($result['description']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Publikasi/ Eksternal Akses</div>
                        <div class="col-sm-8"><?php echo boolean_integer($result['is_publish']); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Sub Publikasi/ Eksternal Akses</div>
                        <div class="col-sm-8"><?php echo boolean_integer($result['is_sub_publish']); ?></div>
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

        <!-- Render Access Control -->
        <?php echo view('acl/group/form_group_access', ['actions' => $actions, 'permission_list' => $permission_list, 'group_permissions' => $group_permissions ?? array(), 'read_only' => TRUE]); ?>
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