<?php echo form_open($uri_name . '/', ['id' => 'form_input_' . $uri_name_js_id, 'autocomplete' => 'off']) ?>
<?php echo csrf_field() ?>
<div>
    <div class="row d-flex justify-content-center align-item-center">
        <div class="col-sm-12 col-lg-2 mb-3">
            <?php echo form_label('Nama Aplikasi', 'web_app_name', ['class' => 'form-label']); ?>
        </div>
        <div class="col-sm-12 col-lg-10 mb-3">
            <?php
            $item = [
                'type'          => 'text',
                'class'         => 'form-control',
                'name'          => 'web_app_name',
                'id'            => 'web_app_name',
                'placeholder'   => 'Masukkan Nama Aplikasi',
                'value'         => isset($result['web_app_name']) ? $result['web_app_name'] : set_value('web_app_name')
            ];
            echo form_input($item); ?>
        </div>
    </div>
    <div class="row d-flex justify-content-center align-item-center">
        <div class="col-sm-12 col-lg-2 mb-3">
            <?php echo form_label('App Logo (Light)', 'web_app_logo_light', ['class' => 'form-label']); ?>
        </div>
        <div class="col-sm-12 col-lg-10 mb-3">
            <?php
            $item = [
                'type'   => 'file',
                'class'  => 'form-control',
                'name'   => 'web_app_logo_light',
                'id'     => 'web_app_logo_light',
                'accept' => 'image/jpg,image/jpeg,image/png,application/pdf'
            ];

            echo form_input($item); ?>
            <div class="text-muted text-x-small mb-0 full-width small">
                Ukuran File Maksimal 2 MB. File yang diperbolehkan hanya ekstensi JPG, JPEG, atau PNG.
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-lg-2 mb-3">
            <?php echo form_label('App Logo (Dark)', 'web_app_logo_dark', ['class' => 'form-label']); ?>
        </div>
        <div class="col-sm-12 col-lg-10 mb-3">
            <?php
            $item = [
                'type'   => 'file',
                'class'  => 'form-control',
                'name'   => 'web_app_logo_dark',
                'id'     => 'web_app_logo_dark',
                'accept' => 'image/jpg,image/jpeg,image/png,application/pdf'
            ];
            echo form_input($item); ?>
            <div class="text-muted text-x-small mb-0 full-width small">
                Ukuran File Maksimal 2 MB. File yang diperbolehkan hanya ekstensi JPG, JPEG, atau PNG.
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 col-lg-2 mb-3">
        <?php echo form_label('Web Author', 'web_app_author', ['class' => 'form-label']); ?>
    </div>
    <div class="col-sm-12 col-lg-10 mb-3">
        <?php
        $item = [
            'type'          => 'text',
            'class'         => 'form-control',
            'name'          => 'web_app_author',
            'id'            => 'web_app_author',
            'placeholder'   => 'Masukkan Web Author',
            'value'         => isset($result['web_app_author']) ? $result['web_app_author'] : set_value('web_app_author')
        ];

        echo form_input($item); ?>
    </div>
</div>
<div class="row">
    <div class="col-sm-12 col-lg-2 mb-3">
        <?php echo form_label('Web Meta Description', 'web_app_meta_description', ['class' => 'form-label']); ?>
    </div>
    <div class="col-sm-12 col-lg-10 mb-3">
        <?php
        $item = [
            'class'         => 'form-control',
            'name'          => 'web_app_meta_description',
            'id'            => 'web_app_meta_description',
            'rows'          => 3,
            'value'         => isset($result['web_app_meta_description']) ? $result['web_app_meta_description'] : set_value('web_app_meta_description')
        ];

        echo form_textarea($item); ?>
    </div>
</div>
<div class="d-flex justify-content-end gap-3">
    <?php echo form_button(['type' => 'submit', 'id' => 'btn_save_' . $uri_name_js_id, 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
</div>
<?php echo form_close() ?>