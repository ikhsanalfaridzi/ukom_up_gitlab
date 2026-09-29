<?php echo $this->extend('layouts/app') ?>

<?php echo $this->section('content') ?>
<div class="container-xxl flex-grow-1 container-p-y">
    <h3 class="pb-1 mb-4"><?php echo $title ?></h3>
    <div class="d-flex justify-content-between">
        <?php if (empty(session()->get('login_personnel_id'))) : ?>
            <a href="<?php echo site_url('interview/interview_group/list') ?>" class="btn btn-primary mb-3">Kembali</a>
            <a href="<?php echo site_url('assessment/assessment/pdf/' . $employee_id . (!empty($interview_participant_id) ? '/' . $interview_participant_id : '')) ?>" class="btn btn-danger mb-3"><i class='bx bxs-file-pdf'></i> Export PDF</a>
        <?php endif; ?>
    </div>
    <div class="row mb-5">
        <div class="col-md">
            <div class="card mb-3">
                <div class="row g-0 px-4">
                    <div class="col-md-2 d-flex">
                        <img class="rounded-circle header-profile-user" src="<?php echo !empty($data['profile_image']) ? base_url('uploads/employee/profile_image/' . $data['profile_image']) : base_url('vlz/images/users/no-photo.jpg') ?>" style="width: 150px; height: 150px; object-fit: cover;" alt="Header Avatar">
                    </div>
                    <div class="col-md-10">
                        <div class="card-body">
                            <h5 class="card-title mb-4">Data Pegawai</h5>
                            <div class="row">
                                <div class="col">
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">NIP/NRP</div>
                                        <div class="col-md-8"><?php echo $data['employee_number'] ? $data['employee_number'] : "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Nama Lengkap</div>
                                        <div class="col-md-8"><?php echo strtoupper($data['full_name']) ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Tanggal Lahir</div>
                                        <div class="col-md-8"><?php echo $data['birth_date'] ? format_date($data['birth_date'], false) : "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Umur</div>
                                        <div class="col-md-8"><?php echo $data['birth_date'] ? calculate_age($data['birth_date']) : "-"; ?></div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Golongan</div>
                                        <div class="col-md-8"><?php echo $data['grade_name'] ?? "-"; ?></div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Jabatan</div>
                                        <div class="col-md-8">
                                            <?php
                                            echo isset($data['position_name'])
                                                ? strtoupper($data['position_name'])
                                                : "-";
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Tanggal Wawancara</div>
                                        <div class="col-md-8">
                                            <?php
                                            echo !empty($data['created_at']) ? format_date($data['created_at']) : "-";
                                            ?>
                                        </div>
                                    </div>
                                    <!-- <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Wawancara Untuk</div>
                                        <div class="col-md-8">
                                            <?php
                                            //echo !empty($data['type']) ? get_eselon_type($data['type']) : "Belum melakukan wawancara";
                                            ?>
                                        </div>
                                    </div> -->
                                    <!-- <div class="row mb-3">
                                        <div class="col-md-4 fw-bold">Asal Instansi</div>
                                        <div class="col-md-8">
                                            <?php
                                            // echo isset($data['instance_name'])
                                            //     ? strtoupper($data['instance_name'])
                                            //     : (isset($data['instance_text'])
                                            //         ? strtoupper($data['instance_text'])
                                            //         : "-");
                                            ?>
                                        </div>
                                    </div> -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="border">

                    <?php if (!empty($final_result) || !empty($raw_result)) :  ?>
                        <!-- General -->
                        <div class="row d-flex flex-wrap p-4">
                            <?php
                            $tableCount = count($raw_result);
                            $count = 1;
                            foreach ($raw_result as $component => $aspects) :
                                $colSize = $tableCount === 1 ? 'col-12' : 'col-md-6';
                            ?>
                                <div class="<?php echo $colSize; ?> mb-4 d-flex">
                                    <div class="table-responsive w-100">
                                        <table class="table table-bordered mb-4">
                                            <thead style="background-color: #508D4E;">
                                                <tr class="text-center">
                                                    <th colspan="2" style="color: white"><?php echo htmlspecialchars($component, ENT_QUOTES, 'UTF-8'); ?></th>
                                                </tr>
                                                <tr class="text-center">
                                                    <th style="color: white">Aspek</th>
                                                    <th style="color: white">Nilai</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($aspects as $aspect => $data) : ?>
                                                    <?php foreach ($data['questions'] as $index => $item) : ?>
                                                        <tr>
                                                            <?php //if ($index === 0) : 
                                                            ?>
                                                            <td rowspan="<?php echo count($data['questions']); ?>">
                                                                <?php echo htmlspecialchars($data['aspect'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </td>
                                                            <?php //endif; 
                                                            ?>
                                                            <td class="text-center"><?php echo htmlspecialchars($item['value'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <?php if ($count === 3): ?>
                                    <div class="col-6">
                                        <div class="table-responsive text-nowrap mb-2">
                                            <table class="table table-bordered">
                                                <thead style="background-color: #1D5B79;">
                                                    <tr class="text-center">
                                                        <th style="color: white">Summary Penilaian</th>
                                                        <th style="color: white">Nilai</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($final_result as $data) : ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                            <td class="text-center"><?php echo htmlspecialchars($data['value'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                    <tr class="text-center">
                                                        <th>Total Skor</th>
                                                        <td class="text-center fw-bold"><?php echo htmlspecialchars($final_value, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                    <tr class="text-center">
                                                        <th>Hasil Akhir</th>
                                                        <td class="text-center fw-bold"><?php echo htmlspecialchars($final_score, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                    <tr class="text-center">
                                                        <th>Kategori</th>
                                                        <td class="text-center"><?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <table class="table table-bordered">
                                            <thead style="background-color: #1D5B79;">
                                                <tr class="text-center">
                                                    <th style="color: white">Kategori</th>
                                                    <th style="color: white">Skor</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>MS : Memenuhi Syarat</td>
                                                    <td>≥ 80</td>
                                                </tr>
                                                <tr>
                                                    <td>MMS: Masih Memenuhi Syarat</td>
                                                    <td>≥ 68 - < 80</td>
                                                </tr>
                                                <tr>
                                                    <td>KMS Kurang Memenuhi Syarat</td>
                                                    <td>
                                                        < 68</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            <?php
                                $count++;
                            endforeach;
                            ?>
                        </div>

                        <div class="d-flex justify-content-center">
                            <table class="table w-100 border-0 mb-4">
                                <?php
                                $index = 1;
                                foreach ($evaluator as $eval) :
                                    // Open a new row every 3 evaluators
                                    if ($index % 3 == 1) {
                                        echo '<tr class="d-flex justify-content-around w-100">';
                                    }
                                ?>
                                    <td class="text-center border-0">
                                        Penilai <?php echo $index; ?>,
                                        <br><br><br><br><br>
                                        <?php echo $eval['evaluator']; ?>
                                    </td>
                                <?php
                                    // Close the row after 3 evaluators
                                    if ($index % 3 == 0) {
                                        echo '</tr>';
                                    }
                                    $index++;
                                endforeach;

                                // If the last row has less than 3 evaluators, close the row
                                if (($index - 1) % 3 != 0) {
                                    echo '</tr>';
                                }
                                ?>
                            </table>
                        </div>

                    <?php else : ?>
                        <h4 class="text-center">Belum Melakukan Penilaian</h4>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php echo $this->endSection() ?>

    <?php echo $this->section('javascript') ?>
    <?php if (!empty(session()->getFlashdata('message'))) : ?>
        <script type="text/javascript">
            Swal.fire({
                position: 'center',
                icon: 'warning',
                title: '<?php echo session()->getFlashdata('message'); ?>',
                showConfirmButton: false,
                timer: 3000
            });
        </script>
    <?php
        unset($_SESSION['message']);
    endif;
    ?>
    <?php echo $this->endSection() ?>