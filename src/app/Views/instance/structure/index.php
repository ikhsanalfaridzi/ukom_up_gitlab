<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('css') ?>
<?php echo $this->include('layouts/partials/datatables-css') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'Instansi', 'title' => $title]); ?>

    <div class="row">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?php echo $title ?></h5>
            </div>
            <div class="card-body">
                <div class="table-height-over-flow" style="max-height: 550px; overflow-y: auto;">
                    <table class="table table-bordered" id="datalist_<?php echo $uri_name_js_id ?>">
                        <thead>
                            <tr>
                                <th class="text-center">Kode Struktur</th>
                                <th class="text-center">Jabatan</th>
                                <th class="text-center">Unit Kerja</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">
                                    <a href="<?php echo site_url($uri_name . '/form'); ?>" class="btn btn-sm btn-icon btn-primary rounded-pill">
                                        <span class="tf-icons bx bx-plus-circle"></span>
                                    </a>
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-2">
                    <select id="dt-entries-select" class="form-select form-control-sm" style="width: auto;">
                        <option value="20">10</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="500">500</option>
                        <option value="1000">1000</option>
                    </select>
                    <div class="d-flex flex-column">
                        <div id="dt-pagination" class="d-flex justify-content-end"></div>
                        <span id="dt-showing-entries" class="d-flex justify-content-end"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>

<?php echo $this->section('javascript') ?>
<?php echo $this->include('layouts/partials/datatables-scripts') ?>

<script type="text/javascript">
    $(document).ready(function() {
        const tableId = 'datalist_<?php echo $uri_name_js_id ?>';
        const url = site_url + uri_name + '/data';
        var toast = getToast();

        if (toast) {
            showToast(toast.status, toast.message);
            removeToast();
        }

        const options = {
            buttons: [{
                    extend: "copy",
                    exportOptions: {
                        columns: ":not(.no-export)",
                    },
                },
                {
                    extend: "print",
                    exportOptions: {
                        columns: ":not(.no-export)",
                    },
                },
                {
                    extend: "excel",
                    exportOptions: {
                        columns: ":not(.no-export)",
                    },
                },
            ],
            columns: [{
                    data: "structure_code",
                    name: "structure_code",
                    render: function(data, type, row, meta) {
                        return detailButton(row.id, data);
                    }
                },
                {
                    data: "position_name",
                    name: "position_name",
                },
                {
                    data: "organization_name",
                    name: "organization_name",
                },
                {
                    data: "is_active",
                    name: "is_active",
                    render: function(data, type, row, meta) {
                        return booleanInteger(row.is_active);
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        data = `<td class="text-center">
                                    <div class="dropdown d-inline-block">
                                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-more-fill align-middle"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                            `;
                        data += `<li>
                                    <a href="${site_url + uri_name + '/form/' + row.id}" class="dropdown-item edit-item-btn">
                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit
                                    </a>
                                </li>`;
                        data += `<li>
                                    <button class="dropdown-item remove-item-btn" data-name="${row.structure_code + ' ' +row.position_name}" data-url="${site_url + uri_name + '/delete/' + row.id}">
                                        <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Delete
                                    </button>
                                </li>`;
                        data += `</ul>
                            </div>
                        </td>`;

                        return data;
                    },
                },
            ],
        };

        generateDataTable(tableId, url, options);

        $(document).on('click', '.remove-item-btn', function() {
            var itemName = $(this).data('name');
            var itemUrl = $(this).data('url');
            var token = "<?php echo csrf_hash() ?>";

            delete_data(itemName, itemUrl, token, tableId);
        });
    });
</script>
<?php echo $this->endSection() ?>