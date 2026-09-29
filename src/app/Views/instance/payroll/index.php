<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('css') ?>
<?php echo $this->include('layouts/partials/datatables-css') ?>
<?php echo $this->endSection() ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'Instansi', 'title' => $title]); ?>

    <!-- Main Datatable Card: All Personnel list with salary configuration button per row -->
    <div class="row" data-aos="fade-up">
        <div class="col-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-white">
                    <h5 class="card-title mb-0 fw-semibold">Daftar Pegawai & Status Gaji</h5>
                    <a href="<?php echo site_url($uri_name . '/form'); ?>" class="btn btn-primary btn-sm rounded-pill d-flex align-items-center gap-1">
                        <i class="ri-add-line fs-5"></i> Tambah Gaji Manual
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" id="datalist_main_<?php echo $uri_name_js_id ?>">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Nama Pegawai</th>
                                    <th class="text-center">Unit Kerja</th>
                                    <th class="text-center">Nominal Gaji</th>
                                    <th class="text-center">Tanggal Dibuat</th>
                                    <th class="text-center" style="width: 120px;">Aksi / Atur Gaji</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Second Datatable Card: Preview 4 Data Terbaru dari Tabel Gaji -->
    <div class="row" data-aos="fade-up" data-aos-delay="100">
        <div class="col-12">
            <div class="card border-info shadow-sm">
                <div class="card-header bg-soft-info d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-info">
                        <i class="ri-bar-chart-box-line me-1"></i> Ringkasan Gaji (4 Data Terbaru)
                    </h5>
                    <span class="badge bg-info">Maksimal 4 Data</span>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Tabel di bawah ini menampilkan 4 data transaksi gaji pegawai paling baru yang tercatat dalam sistem.</p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle" id="datalist_top4_<?php echo $uri_name_js_id ?>">
                            <thead class="table-secondary">
                                <tr>
                                    <th class="text-center">Nama Pegawai</th>
                                    <th class="text-center">Unit Kerja</th>
                                    <th class="text-center">Nominal Gaji</th>
                                    <th class="text-center">Tanggal Dibuat</th>
                                </tr>
                            </thead>
                        </table>
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
        const tableMainId = 'datalist_main_<?php echo $uri_name_js_id ?>';
        const tableTop4Id = 'datalist_top4_<?php echo $uri_name_js_id ?>';
        const urlMain = site_url + uri_name + '/data';
        const urlTop4 = site_url + uri_name + '/data-top4';
        
        var toast = getToast();
        if (toast) {
            showToast(toast.status, toast.message);
            removeToast();
        }

        // Configuration for Main Datatable (All Employees)
        const mainOptions = {
            columns: [
                {
                    data: "full_name",
                    name: "full_name",
                },
                {
                    data: "organization",
                    name: "organization",
                },
                {
                    data: null,
                    name: "salary",
                    className: 'text-end',
                    render: function(data, type, row) {
                        if (row.has_salary) {
                            return `<span class="fw-semibold text-success">${row.salary}</span>`;
                        } else {
                            return `<span class="badge bg-soft-secondary text-muted px-2 py-1">Belum Diatur</span>`;
                        }
                    }
                },
                {
                    data: "created_at",
                    name: "created_at",
                    className: 'text-center'
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function(data, type, row, meta) {
                        if (row.has_salary) {
                            var docBtn = '';
                            if (row.document_path) {
                                docBtn = `<li>
                                            <a href="${base_url + row.document_path}" target="_blank" class="dropdown-item">
                                                <i class="ri-file-download-line align-bottom me-2 text-primary"></i> Unduh Berkas
                                            </a>
                                        </li>`;
                            }

                            return `<div class="dropdown d-inline-block">
                                        <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="ri-more-fill align-middle"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            ${docBtn}
                                            <li>
                                                <a href="${site_url + uri_name + '/form/' + row.salary_id}" class="dropdown-item edit-item-btn">
                                                    <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Edit Gaji
                                                </a>
                                            </li>
                                            <li>
                                                <button class="dropdown-item remove-item-btn" data-name="${row.full_name}" data-url="${site_url + uri_name + '/delete/' + row.salary_id}">
                                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted"></i> Hapus Gaji
                                                </button>
                                            </li>
                                        </ul>
                                    </div>`;
                        } else {
                            // Sleek "Set Gaji" button
                            return `<a href="${site_url + uri_name + '/form?employee_personnel_id=' + row.employee_personnel_id}" class="btn btn-soft-primary btn-sm rounded-pill px-3 d-inline-flex align-items-center gap-1">
                                        <i class="ri-add-line"></i> Set Gaji
                                    </a>`;
                        }
                    },
                },
            ],
        };

        generateDataTable(tableMainId, urlMain, mainOptions);

        // Configuration for Top 4 Datatable (Preview)
        $('#' + tableTop4Id).DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            paging: false,
            info: false,
            lengthChange: false,
            ajax: {
                url: urlTop4,
                type: 'POST'
            },
            columns: [
                { data: "full_name" },
                { data: "organization" },
                { data: "salary", className: 'text-end fw-semibold text-success' },
                { data: "created_at", className: 'text-center' }
            ]
        });

        // Delete Handler
        $(document).on('click', '.remove-item-btn', function() {
            var itemName = $(this).data('name');
            var itemUrl = $(this).data('url');
            var token = "<?php echo csrf_hash() ?>";

            delete_data(itemName, itemUrl, token, tableMainId);
        });
    });
</script>
<?php echo $this->endSection() ?>
