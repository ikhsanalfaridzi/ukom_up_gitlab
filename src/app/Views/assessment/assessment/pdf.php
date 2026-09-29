<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Export Interview Result | UJI KOMPETENSI MELALUI WAWANCARA</title>

    <style type="text/css">
        .profile-image {
            width: 120px;
            /* Adjust to maintain 4x6 aspect ratio */
            height: 180px;
            /* Height for 4x6 aspect ratio */
            object-fit: cover;
            /* Ensures the image fits the box without distortion */
        }

        .info-table {
            border-collapse: collapse;
            width: 100%;
        }

        /* .info-table td {
            padding: 10px;
        } */

        /* table {
            page-break-before: avoid;
        } */
    </style>
</head>

<body>
    <header>
        <h3 style="text-align: center; padding:0px; margin:0px;">BERITA ACARA HASIL UJI KOMPETENSI MELALUI WAWANCARA</h3>
        <h3 style="text-align: center; padding:0px; margin:3px 0px 0px 0px;">KEMENTERIAN IMIGRASI DAN PEMASYARAKATAN</h3>
        <p style="text-align: center; padding:0px; margin:4px 0px 0px 0px;">Tanggal Wawancara: <?php
                                                                                                if (empty($data['created_at'])) {
                                                                                                    echo "-";
                                                                                                } else {
                                                                                                    $date_new = DateTime::createFromFormat('Y-m-d H:i:s', $data['created_at']);
                                                                                                    $tgl_wawancara = $date_new->format('d/m/Y');
                                                                                                    echo $tgl_wawancara;
                                                                                                }
                                                                                                ?>
        </p>
        <hr />
    </header>
    <main>
        <div class="container">
            <div style="display: flex; flex-direction: column; gap: 25px;">
                <table class="info-table">
                    <tr>
                        <td rowspan="7" style="width: 20%;">
                            <img src="<?php echo htmlspecialchars($profile, ENT_QUOTES, 'UTF-8'); ?>" alt="Profile" class="profile-image" />
                        </td>
                        <td style="width: 20%;"><b>NIP/NRP</b></td>
                        <td style="width: 60%;"><?php echo $data['employee_number'] ? htmlspecialchars($data['employee_number'], ENT_QUOTES, 'UTF-8') : "-"; ?></td>
                    </tr>
                    <tr>
                        <td><b>Nama Lengkap</b></td>
                        <td><?php echo strtoupper(htmlspecialchars($data['full_name'], ENT_QUOTES, 'UTF-8')); ?></td>
                    </tr>
                    <tr>
                        <td><b>Tanggal Lahir</b></td>
                        <td><?php echo $data['birth_date'] ? htmlspecialchars(format_date($data['birth_date'], false), ENT_QUOTES, 'UTF-8') : "-"; ?></td>
                    </tr>
                    <tr>
                        <td><b>Umur</b></td>
                        <td><?php echo $data['birth_date'] ? htmlspecialchars(calculate_age($data['birth_date']), ENT_QUOTES, 'UTF-8') : "-"; ?></td>
                    </tr>
                    <tr>
                        <td><b>Golongan</b></td>
                        <td>
                            <?php echo isset($data['grade_name']) ? htmlspecialchars($data['grade_name'], ENT_QUOTES, 'UTF-8') : "-"; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Jabatan</b></td>
                        <td>
                            <?php echo isset($data['position_name']) ? htmlspecialchars($data['position_name'], ENT_QUOTES, 'UTF-8') : "-"; ?>
                        </td>
                    </tr>
                    <!-- <tr>
                        <td><b>Wawancara Untuk</b></td>
                        <td>
                            <?php //echo !empty($data['type']) ? get_eselon_type($data['type']) : "Belum melakukan wawancara"; 
                            ?>
                        </td>
                    </tr> -->
                    <!-- <tr>
                        <td><b>Asal Instansi</b></td>
                        <td>
                            <?php //echo isset($data['instance_name']) ? htmlspecialchars($data['instance_name'], ENT_QUOTES, 'UTF-8') : (isset($data['instance_text']) ? htmlspecialchars($data['instance_text'], ENT_QUOTES, 'UTF-8') : "-"); 
                            ?>
                        </td>
                    </tr> -->
                </table>
            </div>
        </div>

        <hr class="border">

        <?php if (!empty($final_result) || !empty($raw_result)) : ?>
            <?php foreach ($raw_result as $component => $aspects) : ?>
                <table style="border: 1px solid black; width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                    <thead style="background-color: #508D4E;">
                        <tr style="border: 1px solid black; color: white; text-align: center;">
                            <th colspan="2"><?php echo htmlspecialchars($component, ENT_QUOTES, 'UTF-8'); ?></th>
                        </tr>
                        <tr style="border: 1px solid black; color: white;">
                            <th style="border: 1px solid black; text-align: left;" width="30%">Aspek</th>
                            <th style="border: 1px solid black; text-align: center;" width="10%">Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aspects as $aspect => $data) : ?>
                            <?php foreach ($data['questions'] as $index => $item) : ?>
                                <tr>
                                    <td style="border: 1px solid black;"><?php echo htmlspecialchars($aspect, ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td class="text-center" style="border: 1px solid black; text-align: center;"><?php echo htmlspecialchars($item['value'], ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>

            <table style="border: 1px solid black; width: 100%; border-collapse: collapse; margin-bottom: 15px;">
                <thead style="background-color: #1D5B79;">
                    <tr class="text-center">
                        <th style="border: 1px solid black; color: white;">Summary Penilaian</th>
                        <th style="border: 1px solid black; color: white;">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($final_result as $data) : ?>
                        <tr>
                            <td style="border: 1px solid black;"><?php echo htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-center" style="border: 1px solid black; text-align: center;"><?php echo htmlspecialchars($data['value'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="text-center">
                        <th style="border: 1px solid black;">Total Skor</th>
                        <td class="fw-bold" style="border: 1px solid black; text-align: center;"><b><?php echo htmlspecialchars($total_value, ENT_QUOTES, 'UTF-8'); ?></b></td>
                    </tr>
                    <tr class="text-center">
                        <th style="border: 1px solid black;">Hasil Akhir</th>
                        <td class="fw-bold" style="border: 1px solid black; text-align: center;"><b><?php echo htmlspecialchars($final_score, ENT_QUOTES, 'UTF-8'); ?></b></td>
                    </tr>
                    <tr class="text-center">
                        <th style="border: 1px solid black;">Kategori</th>
                        <td style="border: 1px solid black; text-align: center;"><b><?php echo $category; ?></b></td>
                    </tr>
                </tbody>
            </table>

            <div style="display: flex; flex-direction: column; gap: 25px;">
                <table class="info-table" style="border-collapse: collapse; width: 100%;">
                    <?php
                    $index = 1;
                    foreach ($evaluator as $eval) :
                        // Start a new row every 3 evaluators
                        if ($index % 3 == 1) {
                            echo '<tr>';
                        }
                    ?>
                        <td style="border: none; text-align: center;">
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
            <h4 style="text-align: center;">Belum Melakukan Penilaian</h4>
        <?php endif; ?>
    </main>
</body>

</html>