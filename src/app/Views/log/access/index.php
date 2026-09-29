<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('css') ?>
<?php echo $this->include('layouts/partials/datatables-css') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'Log', 'title' => $title]); ?>

    <div class="row" data-aos="fade-up">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><?php echo $title ?></h5>
            </div>
            <div class="card-body">
                <div class="table-height-over-flow" style="max-height: 550px; overflow-y: auto;">
                    <table class="table table-bordered" id="datalist_<?php echo $uri_name_js_id ?>">
                        <thead>
                            <tr>
                                <th class="text-center">Username</th>
                                <th class="text-center">URL</th>
                                <th class="text-center">IP Address</th>
                                <th class="text-center">User Data</th>
                                <th class="text-center">Timestamp</th>
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
                    data: "username",
                    name: "username",
                    render: function(data, type, row, meta) {
                        return detailButton(row.id, data);
                    }
                },
                {
                    data: "url",
                    name: "url",
                },
                {
                    data: "ip_address",
                    name: "ip_address",
                },
                {
                    data: "user_data",
                    name: "user_data",
                },
                {
                    data: "created_at",
                    name: "created_at",
                },
            ],
        };

        generateDataTable(tableId, url, options);
    });
</script>
<?php echo $this->endSection() ?>