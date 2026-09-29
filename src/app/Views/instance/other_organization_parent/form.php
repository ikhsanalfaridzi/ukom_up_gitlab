<?php echo $this->extend('layouts/app'); ?>

<?php echo $this->section('css') ?>
<link href="<?php echo base_url('vlz/libs/select2/select2.min.css') ?>" rel="stylesheet" type="text/css" />
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'Instansi', 'title' => $title]); ?>

    <?php echo form_open($uri_name . '/' . $form_action, ['id' => 'form_input_' . $uri_name_js_id, 'autocomplete' => 'off']) ?>
    <?php echo csrf_field() ?>
    <div class="row" data-aos="fade-right">
        <div class="col">
            <div class="card mb-4">
                <h5 class="card-header"><?php echo $title; ?></h5>
                <div class="card-body">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control',
                                    'name'          => 'organization_code',
                                    'id'            => 'organization_code',
                                    'placeholder'   => 'Masukkan Kode Instansi',
                                    'value'         => isset($result['organization_code']) ? $result['organization_code'] : set_value('organization_code')
                                ];
                                echo form_label('Kode Instansi' . is_required(), 'organization_code', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $item = [
                                    'type'          => 'text',
                                    'class'         => 'form-control',
                                    'name'          => 'organization',
                                    'id'            => 'organization',
                                    'placeholder'   => 'Masukkan Instansi',
                                    'value'         => isset($result['organization']) ? $result['organization'] : set_value('organization')
                                ];
                                echo form_label('Instansi' . is_required(), 'organization', ['class' => 'form-label']);
                                echo form_input($item); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col mb-3">
                                <?php
                                $option = 'id="group_id" class="form-select select2"';
                                $value = isset($result['group_id']) ? $result['group_id'] : "";
                                echo form_label('Jenis Instansi' . is_required(), 'group_id', ['class' => 'form-label']);
                                echo form_dropdown('group_id', $group_options, $value, $option);
                                ?>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <?php
                            $active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_active',
                                'id'        => 'active_value',
                                'value'     => 1,
                                'checked'   => $result && $result['is_active'] === "1" ? true : false
                            ];
                            $non_active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_active',
                                'id'        => 'non_active_value',
                                'value'     => 0,
                                'checked'   => $result && $result['is_active'] !== "1" ? true : false
                            ];
                            echo form_label('Status' . is_required(), 'is_active', ['class' => 'form-label']);
                            ?>
                            <div class="d-flex gap-3">
                                <div class="d-flex flex-row gap-2">
                                    <?php
                                    echo form_radio($active);
                                    echo form_label('Aktif', 'active_value');
                                    ?>
                                </div>
                                <div class="d-flex flex-row gap-2">
                                    <?php
                                    echo form_radio($non_active);
                                    echo form_label('Tidak Aktif', 'non_active_value');
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <?php
                            $active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_demo',
                                'id'        => 'demo_value',
                                'value'     => 1,
                                'checked'   => $result && $result['is_demo'] === "1" ? true : false
                            ];
                            $non_active = [
                                'type'      => 'text',
                                'class'     => 'form-check-input ',
                                'name'      => 'is_demo',
                                'id'        => 'non_demo_value',
                                'value'     => 0,
                                'checked'   => $result && $result['is_demo'] !== "1" ? true : false
                            ];
                            echo form_label('Instansi Demo' . is_required(), 'is_demo', ['class' => 'form-label']);
                            ?>
                            <div class="d-flex gap-3">
                                <div class="d-flex flex-row gap-2 mb-1">
                                    <?php
                                    echo form_radio($active);
                                    echo form_label('Ya', 'demo_value');
                                    ?>
                                </div>
                                <div class="d-flex flex-row gap-2">
                                    <div class="mb-2">
                                        <?php
                                        echo form_radio($non_active);
                                        echo form_label('Tidak', 'non_demo_value');
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" data-aos="fade-left">
        <div class="card mb-4">
            <h5 class="card-header">Lokasi & Kontak Instansi</h5>
            <div class="card-body">
                <div class="row">
                    <div class="col mb-3">
                        <?php
                        $option = 'id="province_id" class="form-select select2"';
                        $value = isset($result['province_id']) ? $result['province_id'] : "";
                        echo form_label('Provinsi', 'province_id', ['class' => 'form-label']);
                        echo form_dropdown('province_id', $province_options, $value, $option);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <?php
                        $option = 'id="city_id" class="form-select select2"';
                        $value = isset($result['city_id']) ? $result['city_id'] : "";
                        echo form_label('Kabupaten/Kota', 'city_id', ['class' => 'form-label']);
                        echo form_dropdown('city_id', [], $value, $option);
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <?php
                        $item = [
                            'class'         => 'form-control',
                            'name'          => 'address',
                            'id'            => 'address',
                            'rows'          => 3,
                            'value'         => isset($contact['address']) ? $contact['address'] : set_value('address')
                        ];
                        echo form_label('Alamat', 'address', ['class' => 'form-label']);
                        echo form_textarea($item); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <?php
                        $item = [
                            'type'          => 'email',
                            'class'         => 'form-control',
                            'name'          => 'email',
                            'id'            => 'email',
                            'placeholder'   => 'Masukkan Email',
                            'value'         => isset($contact['email']) ? $contact['email'] : set_value('email')
                        ];
                        echo form_label('Email', 'email', ['class' => 'form-label']);
                        echo form_input($item); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?php
                        $item = [
                            'type'          => 'number',
                            'class'         => 'form-control',
                            'name'          => 'phone_number',
                            'id'            => 'phone_number',
                            'placeholder'   => 'Masukkan Nomor Telepon',
                            'value'         => isset($contact['phone_number']) ? $contact['phone_number'] : set_value('phone_number')
                        ];
                        echo form_label('Nomor Telepon', 'phone_number', ['class' => 'form-label']);
                        echo form_input($item); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?php
                        $item = [
                            'type'          => 'number',
                            'class'         => 'form-control',
                            'name'          => 'fax_number',
                            'id'            => 'fax_number',
                            'placeholder'   => 'Masukkan Faksimile',
                            'value'         => isset($contact['fax_number']) ? $contact['fax_number'] : set_value('fax_number')
                        ];
                        echo form_label('Faksimile', 'fax_number', ['class' => 'form-label']);
                        echo form_input($item); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <?php
                        $item = [
                            'type'          => 'text',
                            'class'         => 'form-control',
                            'name'          => 'latitude',
                            'id'            => 'latitude',
                            'placeholder'   => 'Masukkan Latitude',
                            'value'         => isset($contact['latitude']) ? $contact['latitude'] : set_value('latitude')
                        ];
                        echo form_label('Latitude', 'latitude', ['class' => 'form-label']);
                        echo form_input($item); ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <?php
                        $item = [
                            'type'          => 'text',
                            'class'         => 'form-control',
                            'name'          => 'longitude',
                            'id'            => 'longitude',
                            'placeholder'   => 'Masukkan Longitude',
                            'value'         => isset($contact['longitude']) ? $contact['longitude'] : set_value('longitude')
                        ];
                        echo form_label('Longitude', 'longitude', ['class' => 'form-label']);
                        echo form_input($item); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col mb-3">
                        <?php
                        $item = [
                            'class'         => 'form-control',
                            'name'          => 'vector',
                            'id'            => 'vector',
                            'rows'          => 3,
                            'value'         => isset($contact['vector']) ? $contact['vector'] : set_value('vector')
                        ];
                        echo form_label('Vector', 'vector', ['class' => 'form-label']);
                        echo form_textarea($item); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-lg-12 d-flex flex-row justify-content-center gap-3">
            <?php echo form_button(['type' => 'button', 'id' => 'btn_cancel_' . $uri_name_js_id, 'content' => 'Kembali', 'class' => 'btn btn-outline-secondary btn-cancel']) ?>
            <?php echo form_button(['type' => 'submit', 'id' => 'btn_save_' . $uri_name_js_id, 'content' => 'Simpan', 'class' => 'btn btn-primary']) ?>
        </div>
    </div>

    <?php echo form_close() ?>
</div>
<?php echo $this->endSection(); ?>

<?php echo $this->section('javascript'); ?>
<script src="<?php echo base_url('vlz/libs/select2/select2.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo base_url('vlz/js/form-handler.js'); ?>" type="text/javascript"></script>

<script type="text/javascript">
    $(document).ready(function() {
        let selectedCity = '<?php echo $result !== NULL ? $result["city_id"] : set_value("city_id") ?>';

        $('.select2').select2({
            placeholder: '- Silahkan Pilih -',
            allowClear: true,
            width: '100%'
        });

        handleDropdownChange('#province_id', '#city_id', '<?php echo site_url("region/cities/get_list/") ?>', selectedCity);

        $("#btn_cancel_<?php echo $uri_name_js_id; ?>").click(function() {
            redirect(site_url + uri_name);
        });

        handleFormSubmit(`#form_input_<?php echo $uri_name_js_id; ?>`)
            .onSuccess(function(res) {
                setToast(res.status, res.message);
                redirect(res.redirect_url);
            })
            .onError(function(err) {
                showAlert('Error!', err.responseJSON?.message, 'error');
            })
            .init();
    });
</script>
<?php echo $this->endSection(); ?>