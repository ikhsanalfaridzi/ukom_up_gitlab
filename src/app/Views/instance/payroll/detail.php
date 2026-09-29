<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('content') ?>
<div class="container-fluid">
    <?php echo view('layouts/partials/page-title', ['pagetitle' => 'Instansi', 'title' => 'Rincian Gaji Pegawai']); ?>

    <div class="row" data-aos="fade-up">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-white mb-0">Rincian Gaji & Payroll</h5>
                    <a href="<?php echo site_url($uri_name) ?>" class="btn btn-sm btn-light">
                        <i class="ri-arrow-left-line"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <th style="width: 30%;">Nama Pegawai</th>
                                <td><?php echo esc($detail['full_name']); ?></td>
                            </tr>
                            <tr>
                                <th>Unit Kerja</th>
                                <td><?php echo esc($detail['organization']); ?></td>
                            </tr>
                            <tr>
                                <th>Nominal Gaji</th>
                                <td class="fw-bold text-success">
                                    Rp <?php echo number_format($detail['salary'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Tunjangan / Benefits</th>
                                <td>
                                    <?php
                                    $benefits = !empty($detail['benefits']) ? json_decode($detail['benefits'], true) : [];
                                    if (!empty($benefits) && is_array($benefits)) {
                                        foreach ($benefits as $b) {
                                            echo '<span class="badge bg-primary me-1">' . esc($b) . '</span>';
                                        }
                                    } else {
                                        echo '<span class="text-muted">-</span>';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Tanggal Dibuat</th>
                                <td><?php echo $detail['created_at'] ? date('d-m-Y H:i:s', strtotime($detail['created_at'])) : '-'; ?></td>
                            </tr>
                            <tr>
                                <th>Dokumen Slip / Bukti Gaji</th>
                                <td>
                                    <?php if (!empty($detail['document_path']) && file_exists(FCPATH . $detail['document_path'])): ?>
                                        <a href="<?php echo base_url($detail['document_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="ri-file-download-line me-1"></i> Unduh Dokumen
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada dokumen terlampir</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>
