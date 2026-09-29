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
            <?php echo form_label('Recaptcha Site Key', 'recaptcha_site_key', ['class' => 'form-label']); ?>
        </div>
        <div class="col-sm-12 col-lg-10 mb-3">
            <?php
            $item = [
                'type'          => 'text',
                'class'         => 'form-control',
                'name'          => 'recaptcha_site_key',
                'id'            => 'recaptcha_site_key',
                'placeholder'   => 'Masukkan Recaptcha Site Key',
                'value'         => isset($result['recaptcha_site_key']) ? $result['recaptcha_site_key'] : set_value('recaptcha_site_key')
            ];
            echo form_input($item); ?>
        </div>
    </div>
    <div class="row d-flex justify-content-center align-item-center">
        <div class="col-sm-12 col-lg-2 mb-3">
            <?php echo form_label('Recaptcha Secret Key', 'recaptcha_secret_key', ['class' => 'form-label']); ?>
        </div>
        <div class="col-sm-12 col-lg-10 mb-3">
            <?php
            $item = [
                'type'          => 'text',
                'class'         => 'form-control',
                'name'          => 'recaptcha_secret_key',
                'id'            => 'recaptcha_secret_key',
                'placeholder'   => 'Masukkan Recaptcha Secret Key',
                'value'         => isset($result['recaptcha_secret_key']) ? $result['recaptcha_secret_key'] : set_value('recaptcha_secret_key')
            ];
            echo form_input($item); ?>
        </div>
    </div>
    <div class="row d-flex justify-content-center align-item-center">
        <div class="col-sm-12 col-lg-2 mb-3">
            <?php echo form_label('Recaptcha Language', 'recaptcha_lang', ['class' => 'form-label']); ?>
        </div>
        <div class="col-sm-12 col-lg-10 mb-3">
            <?php
            $item = [
                'type'          => 'text',
                'class'         => 'form-control',
                'name'          => 'recaptcha_lang',
                'id'            => 'recaptcha_lang',
                'placeholder'   => 'Masukkan Recaptcha Language',
                'value'         => isset($result['recaptcha_lang']) ? $result['recaptcha_lang'] : set_value('recaptcha_lang')
            ];
            echo form_input($item); ?>
        </div>
    </div>
</div>
<div class="d-flex justify-content-end gap-3">
    <?php echo form_button(['type' => 'submit', 'id' => 'btn_save_' . $uri_name_js_id, 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
</div>
<?php echo form_close() ?>