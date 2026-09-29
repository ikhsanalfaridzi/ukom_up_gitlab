<?php echo form_open($uri_name . '/', ['id' => 'form_input_' . $uri_name_js_id, 'autocomplete' => 'off']) ?>
<?php echo csrf_field() ?>
<div>
    <div class="row d-flex justify-content-center align-item-center">
        <div class="col-sm-12 col-lg-2 mb-3">
            <?php
            $active = [
                'type'      => 'text',
                'class'     => 'form-check-input ',
                'name'      => 'is_publish',
                'id'        => 'active_value',
                'value'     => 1,
                'checked'   => isset($result) && $result['is_publish'] === "1" ? true : false
            ];
            $non_active = [
                'type'      => 'text',
                'class'     => 'form-check-input ',
                'name'      => 'is_publish',
                'id'        => 'non_active_value',
                'value'     => 0,
                'checked'   => isset($result) && $result['is_publish'] !== "1" ? true : false
            ];
            echo form_label('Aktif', 'is_publish', ['class' => 'form-label']);
            ?>
        </div>
        <div class="col-sm-12 col-lg-10 mb-3 d-flex gap-3">
            <div class="d-flex flex-row gap-2">
                <?php
                echo form_radio($active);
                echo form_label('Ya', 'active_value');
                ?>
            </div>
            <div class="d-flex flex-row gap-2">
                <?php
                echo form_radio($non_active);
                echo form_label('Tidak', 'non_active_value');
                ?>
            </div>
        </div>
    </div>
    <div class="row d-flex justify-content-center align-item-center">
        <div class="col-sm-12 col-lg-2 mb-3">
            <?php echo form_label('Realms', 'sso_realms', ['class' => 'form-label']); ?>
        </div>
        <div class="col-sm-12 col-lg-10 mb-3">
            <?php
            $item = [
                'type'          => 'text',
                'class'         => 'form-control',
                'name'          => 'sso_realms',
                'id'            => 'sso_realms',
                'placeholder'   => 'Masukkan Realms',
                'value'         => isset($result['sso_realms']) ? $result['sso_realms'] : set_value('sso_realms')
            ];
            echo form_input($item); ?>
        </div>
    </div>
    <div class="row d-flex justify-content-center align-item-center">
        <div class="col-sm-12 col-lg-2 mb-3">
            <?php echo form_label('Client ID', 'sso_client_id', ['class' => 'form-label']); ?>
        </div>
        <div class="col-sm-12 col-lg-10 mb-3">
            <?php
            $item = [
                'type'          => 'text',
                'class'         => 'form-control',
                'name'          => 'sso_client_id',
                'id'            => 'sso_client_id',
                'placeholder'   => 'Masukkan Client ID',
                'value'         => isset($result['sso_client_id']) ? $result['sso_client_id'] : set_value('sso_client_id')
            ];
            echo form_input($item); ?>
        </div>
    </div>
    <div class="row d-flex justify-content-center align-item-center">
        <div class="col-sm-12 col-lg-2 mb-3">
            <?php echo form_label('Client Secret', 'sso_client_secret', ['class' => 'form-label']); ?>
        </div>
        <div class="col-sm-12 col-lg-10 mb-3">
            <?php
            $item = [
                'type'          => 'text',
                'class'         => 'form-control',
                'name'          => 'sso_client_secret',
                'id'            => 'sso_client_secret',
                'placeholder'   => 'Masukkan Client Secret',
                'value'         => isset($result['sso_client_secret']) ? $result['sso_client_secret'] : set_value('sso_client_secret')
            ];
            echo form_input($item); ?>
        </div>
    </div>
    <div class="row d-flex justify-content-center align-item-center">
        <div class="col-sm-12 col-lg-2 mb-3">
            <?php echo form_label('Base URL', 'sso_base_url', ['class' => 'form-label']); ?>
        </div>
        <div class="col-sm-12 col-lg-10 mb-3">
            <?php
            $item = [
                'type'          => 'text',
                'class'         => 'form-control',
                'name'          => 'sso_base_url',
                'id'            => 'sso_base_url',
                'placeholder'   => 'Masukkan Base URL',
                'value'         => isset($result['sso_base_url']) ? $result['sso_base_url'] : set_value('sso_base_url')
            ];
            echo form_input($item); ?>
        </div>
    </div>
    <div class="row d-flex justify-content-center align-item-center">
        <div class="col-sm-12 col-lg-2 mb-3">
            <?php echo form_label('Redirect URL', 'sso_redirect_url', ['class' => 'form-label']); ?>
        </div>
        <div class="col-sm-12 col-lg-10 mb-3">
            <?php
            $item = [
                'type'          => 'text',
                'class'         => 'form-control',
                'name'          => 'sso_redirect_url',
                'id'            => 'sso_redirect_url',
                'placeholder'   => 'Masukkan Redirect URL',
                'value'         => isset($result['sso_redirect_url']) ? $result['sso_redirect_url'] : set_value('sso_redirect_url')
            ];
            echo form_input($item); ?>
        </div>
    </div>
</div>
<div class="d-flex justify-content-end gap-3">
    <?php echo form_button(['type' => 'submit', 'id' => 'btn_save_' . $uri_name_js_id, 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
</div>
<?php echo form_close() ?>