<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <a href="<?php echo site_url('recap/recap') ?>" class="btn btn-primary mb-3">Kembali</a>

    <div class="row mb-4">
        <div class="col-md mb-4 mb-md-0">
            <div class="card">
                <h5 class="card-header">Detail</h5>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Kelompok Wawancara</div>
                        <div class="col-md-8"><?php echo $group['name'] ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 fw-bold">Level Kompetensi</div>
                        <div class="col-md-8"><?php echo $group['level'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md mb-4 mb-md-0">
            <div class="card">
                <h5 class="card-header"><?php echo $title ?></h5>
                <div class="card-body">
                    <div class="alert alert-secondary" role="alert">
                        <h6 class="text-primary font-weight-bold mb-2"><i class='bx bx-info-circle me-2'></i></i>Ketentuan Pemeringkatan</h6>
                        <p class="mb-1"><i class="bi bi-sort-numeric-down me-2"></i>Diurutkan berdasarkan nilai tertinggi</p>
                        <p class="mb-1"><i class="bi bi-chevron-bar-up me-2"></i>Jika nilai sama, urutkan berdasarkan pangkat tertinggi</p>
                        <p class="mb-0"><i class="bi bi-calendar3 me-2"></i>Jika nilai dan pangkat sama, urutkan berdasarkan umur tertua</p>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th rowspan="2">Ranking</th>
                                    <th rowspan="2">Nama Lengkap</th>
                                    <th rowspan="2">Pangkat</th>
                                    <th rowspan="2">Umur</th>
                                    <th rowspan="2">Penilai</th>
                                    <th colspan="<?php echo count($data['header']); ?>" class="text-center">Pertanyaan</th>
                                    <th rowspan="2">Total Skor</th>
                                    <th rowspan="2">Hasil Akhir</th>
                                    <th rowspan="2">Kategori</th>
                                    <th rowspan="2">Detail</th>
                                    <th rowspan="2">Waktu Penilaian</th>
                                    <th rowspan="2">Operator</th>
                                </tr>
                                <tr>
                                    <?php for ($i = 1; $i <= count($data['header']); $i++) : ?>
                                        <th>P<?php echo $i; ?></th>
                                    <?php endfor; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($data['final_result'])) : ?>
                                    <?php
                                    $rank = 1;
                                    foreach ($data['final_result'] as $employee_id => $results) : ?>
                                        <tr>
                                            <td rowspan="<?php echo count($results); ?>"><?php echo $rank++; ?></td>
                                            <td rowspan="<?php echo count($results); ?>" style="min-width: 250px;"><?php echo htmlspecialchars($results[0]['full_name']); ?></td>
                                            <td rowspan="<?php echo count($results); ?>"><?php echo htmlspecialchars($results[0]['grade_name'] ?? "-"); ?></td>
                                            <td rowspan="<?php echo count($results); ?>" style="min-width: 250px;"><?php echo calculate_age($results[0]['birth_date']); ?></td>
                                            <?php foreach ($results as $result) : ?>
                                                <td style="min-width: 200px;"><?php echo htmlspecialchars($result['evaluator']); ?></td>
                                                <?php foreach ($result['details'] as $question) : ?>
                                                    <td><?php echo number_format($question['value'], 2); ?></td>
                                                <?php endforeach; ?>
                                                <td><?php echo htmlspecialchars($result['score']); ?></td>
                                                <td><?php echo htmlspecialchars($result['final_score']); ?></td>
                                                <td><?php echo $result['category']; ?></td>
                                                <td>
                                                    <a href="<?php echo site_url('assessment/assessment/result/' . $result['employee_id'] . '/' . get_eselon_level($group['level'], false)); ?>" class="badge bg-success" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Hasil Wawancara">
                                                        <i class="bx bx-book-open"></i>
                                                    </a>
                                                </td>
                                                <td style="min-width: 200px;"><?php echo htmlspecialchars($result['created_at']); ?></td>
                                                <td><?php echo htmlspecialchars($result['operator']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <th colspan="<?php echo 22 + count($data['header']); ?>" class="text-center">No data</th>
                            <?php endif; ?>
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<?php echo $this->endSection() ?>