<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'ACL', 'title' => $title]); ?>

    <?php echo form_open($uri_name . '/' . $form_action, ['id' => 'form_input_' . $uri_name_js_id, 'autocomplete' => 'off']) ?>
    <?php echo csrf_field() ?>
    <div class="row" data-aos="fade-right">
        <div class="col-sm-12 col-lg-5">
            <div class="card mb-4">
                <h5 class="card-header"><?php echo $title; ?></h5>
                <div class="card-body">
                    <div class="row">
                        <div class="col mb-3">
                            <?php
                            $item = [
                                'type'          => 'text',
                                'class'         => 'form-control',
                                'name'          => 'group_name',
                                'id'            => 'group_name',
                                'placeholder'   => 'Masukkan Nama Grup',
                                'value'         => isset($result['group_name']) ? $result['group_name'] : set_value('group_name')
                            ];
                            echo form_label('Nama Grup' . is_required(), 'group_name', ['class' => 'form-label']);
                            echo form_input($item); ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col mb-3">
                            <?php
                            $item = [
                                'class'         => 'form-control',
                                'name'          => 'description',
                                'id'            => 'description',
                                'rows'          => 3,
                                'value'         => isset($result['description']) ? $result['description'] : set_value('description')
                            ];
                            echo form_label('Deskripsi', 'description', ['class' => 'form-label']);
                            echo form_textarea($item); ?>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <?php
                        $active = [
                            'type'      => 'text',
                            'class'     => 'form-check-input ',
                            'name'      => 'is_publish',
                            'id'        => 'publish_value',
                            'value'     => 1,
                            'checked'   => $result && $result['is_publish'] === "1" ? true : false
                        ];
                        $non_active = [
                            'type'      => 'text',
                            'class'     => 'form-check-input ',
                            'name'      => 'is_publish',
                            'id'        => 'non_publish_value',
                            'value'     => 0,
                            'checked'   => $result && $result['is_publish'] !== "1" ? true : false
                        ];
                        echo form_label('Publikasi/ Eksternal Akses' . is_required(), 'is_publish', ['class' => 'form-label']);
                        ?>
                        <div class="d-flex gap-3">
                            <div class="d-flex flex-row gap-2 mb-1">
                                <?php
                                echo form_radio($active);
                                echo form_label('Ya', 'publish_value');
                                ?>
                            </div>
                            <div class="d-flex flex-row gap-2">
                                <div class="mb-2">
                                    <?php
                                    echo form_radio($non_active);
                                    echo form_label('Tidak', 'non_publish_value');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <?php
                        $active = [
                            'type'      => 'text',
                            'class'     => 'form-check-input ',
                            'name'      => 'is_sub_publish',
                            'id'        => 'sub_publish_value',
                            'value'     => 1,
                            'checked'   => $result && $result['is_sub_publish'] === "1" ? true : false
                        ];
                        $non_active = [
                            'type'      => 'text',
                            'class'     => 'form-check-input ',
                            'name'      => 'is_sub_publish',
                            'id'        => 'non_sub_publish_value',
                            'value'     => 0,
                            'checked'   => $result && $result['is_sub_publish'] !== "1" ? true : false
                        ];
                        echo form_label('Sub Publikasi/ Eksternal Akses' . is_required(), 'is_sub_publish', ['class' => 'form-label']);
                        ?>
                        <div class="d-flex gap-3">
                            <div class="d-flex flex-row gap-2 mb-1">
                                <?php
                                echo form_radio($active);
                                echo form_label('Ya', 'sub_publish_value');
                                ?>
                            </div>
                            <div class="d-flex flex-row gap-2">
                                <div class="mb-2">
                                    <?php
                                    echo form_radio($non_active);
                                    echo form_label('Tidak', 'non_sub_publish_value');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-3">
                        <?php echo form_button(['type' => 'button', 'id' => 'btn_cancel_' . $uri_name_js_id, 'content' => 'Kembali', 'class' => 'btn btn-outline-secondary btn-cancel']) ?>
                        <?php echo form_button(['type' => 'submit', 'id' => 'btn_save_' . $uri_name_js_id, 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Render Access Control -->
        <?php echo view('acl/group/form_group_access', ['actions' => $actions, 'permission_list' => $permission_list, 'group_permissions' => $group_permissions ?? array(), 'read_only' => FALSE]); ?>
    </div>
    <?php echo form_close() ?>

</div>
<?php echo $this->endSection(); ?>

<?php echo $this->section('javascript'); ?>
<script src="<?php echo base_url('vlz/js/form-handler.js'); ?>" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function() {
        // Check parent onload
        $("input[id^='parent_']").each(function() {
            let menuCode = this.id.replace("parent_", "");
            let allChildCheckboxes = $(".child_" + menuCode);
            let checkedChild = $(".child_" + menuCode + ":checked");

            // If all child checked, set parent to checked
            $(this).prop("checked", allChildCheckboxes.length > 0 && allChildCheckboxes.length === checkedChild.length);
        });

        // Parent checkbox event listener
        $("input[id^='parent_']").on("change", function() {
            let menuCode = this.id.replace("parent_", "");
            let childCheckboxes = $(".child_" + menuCode);

            childCheckboxes.prop("checked", this.checked);
        });

        // Child checkbox event listener
        $("input[class^='form-check-input child_']").on("change", function() {
            let menuCode = $(this).attr("class").split(" ")[1].replace("child_", "");
            let parentCheckbox = $("#parent_" + menuCode);
            let allChildCheckboxes = $(".child_" + menuCode);
            let checkedChild = $(".child_" + menuCode + ":checked");

            // If all child checked, set parent to checked; If not, uncheck parent
            parentCheckbox.prop("checked", allChildCheckboxes.length === checkedChild.length);
        });

        $("#btn_cancel_<?php echo $uri_name_js_id; ?>").click(function() {
            redirect(site_url + uri_name);
        });

        handleFormSubmit(`#form_input_<?php echo $uri_name_js_id; ?>`)
            .onSuccess(function(res) {
                setToast(res.status, res.message);

                redirect(res.redirect_url);
            })
            .onError(function(err) {
                if (err.responseJSON?.message !== 'Validation failed') {
                    showAlert('Error!', err.responseJSON?.message, err.responseJSON?.status);
                }
            })
            .init();
    });
</script>
<?php echo $this->endSection(); ?>