<?php

  if (!function_exists('generate_uuid')) {
    function generate_uuid()
    {
      if (class_exists('Ramsey\\Uuid\\Uuid')) {
        return \Ramsey\Uuid\Uuid::uuid4()->toString();
      }

      $data = random_bytes(16);
      $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
      $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

      return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
  }

  if (!function_exists('format_phone_number')) {
    function format_phone_number($number)
    {
      // Check if the number starts with "0"
      if (strpos($number, '0') === 0) {
        // Replace the "0" with "62"
        $formatted_number = '62' . substr($number, 1);
        return $formatted_number;
      } else {
        // If the number doesn't start with "0", return the original number
        return $number;
      }
    }
  }

  if (!function_exists('format_date')) {
    function format_date($date, $show_day = false)
    {
      // Array of days and months in Bahasa Indonesia
      $nama_hari = [
        'Minggu',
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jum\'at',
        'Sabtu'
      ];

      $nama_bulan = [
        'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
      ];

      // Check if the date contains time or is just a date
      if (strpos($date, ' ') !== false) {
        // If the date includes time (e.g., 2024-11-06 10:00:52)
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $date);
      } else {
        // If it's just a date (e.g., 2024-11-06)
        $date = DateTime::createFromFormat('Y-m-d', $date);
      }

      if (!$date) {
        return "Format tanggal salah";
      }

      // Extract parts of the date
      $tanggal = (int) $date->format('d');
      $bulan   = (int) $date->format('m');
      $tahun   = (int) $date->format('Y');

      // Validate the date
      if (!checkdate($bulan, $tanggal, $tahun)) {
        return "Tanggal tidak valid";
      }

      // Format the date
      $text = '';

      if ($show_day) {
        // Get the day of the week
        $urutan_hari = $date->format('w');
        $hari = $nama_hari[$urutan_hari];
        $text .= "$hari, $tanggal " . $nama_bulan[$bulan - 1] . " $tahun";
      } else {
        $text .= "$tanggal " . $nama_bulan[$bulan - 1] . " $tahun";
      }

      return $text;
    }
  }

  if (!function_exists('format_name')) {
    function format_name($text)
    {
      // Use str_replace to replace spaces with "%"
      $formatted_text = str_replace(' ', '%20', $text);
      return $formatted_text;
    }
  }

  if (!function_exists('get_initial')) {
    function get_initial($word)
    {
      $kataKomp = explode(' ', $word); // Memisahkan kata
      $hurufPertama = '';
      foreach ($kataKomp as $kata) {
        $hurufPertama .= strtoupper(substr($kata, 0, 1));
      }

      return $hurufPertama;
    }
  }

  if (!function_exists('format_decimal')) {
    function format_decimal($number)
    {
      $formattedNumber = rtrim(number_format($number, 1), '0');
      if (substr($formattedNumber, -1) === '.') {
        $formattedNumber = substr($formattedNumber, 0, -1);
      }
      echo $formattedNumber;
    }
  }

  if (!function_exists('get_random_color')) {
    function get_random_color()
    {
      $colors = ['#23C6C8', '#F8AC59', '#1C84C6', '#1D5B79', '#ED5565', '#272829', '#461959', '#4A55A2', '#088395', '#2B3467', '#645CBB', '#7B2869', '#3C6255', '#404258'];
      $randomIndex = rand(0, count($colors) - 1);
      return $colors[$randomIndex];
    }
  }

  if (!function_exists('generate_talent_pool')) {
    function generate_talent_pool($data, $query, $chart_id, $chart_data)
    {
      $kotak1 =  0;
      $kotak2 =  0;
      $kotak3 =  0;
      $kotak4 =  0;
      $kotak5 =  0;
      $kotak8 =  0;
      $kotak6 =  0;
      $kotak7 =  0;
      $kotak9 =  0;

      foreach ($data as $value) {
        $kotak1 = $value['kotak'] == 1 ? $value['jumlah'] : $kotak1;
        $kotak2 = $value['kotak'] == 2 ? $value['jumlah'] : $kotak2;
        $kotak3 = $value['kotak'] == 3 ? $value['jumlah'] : $kotak3;
        $kotak4 = $value['kotak'] == 4 ? $value['jumlah'] : $kotak4;
        $kotak5 = $value['kotak'] == 5 ? $value['jumlah'] : $kotak5;
        $kotak6 = $value['kotak'] == 6 ? $value['jumlah'] : $kotak6;
        $kotak7 = $value['kotak'] == 7 ? $value['jumlah'] : $kotak7;
        $kotak8 = $value['kotak'] == 8 ? $value['jumlah'] : $kotak8;
        $kotak9 = $value['kotak'] == 9 ? $value['jumlah'] : $kotak9;
      }

      $html = '
              <div class="card-body">
                <div class="d-flex justify-content-around">
                  <div class="col-md-6 d-flex flex-column">
                    <div class="row">
                      <div class="col-md-1 bg-secondary d-flex justify-content-center align-items-center text-light rotate">
                        <div class="text text-white">
                          Kinerja
                        </div>
                      </div>
                      <div class="col-md-9 text-light">
                        <div class="row">
                          <div class="col-md-3 d-flex align-items-center rotate">
                            <div class="text text-center text-dark">
                              Diatas
                              Ekspektasi
                            </div>
                          </div>
                          <div class="col-md-3 text-dark p-4 border" style="background-color: #FCE22A;">
                            <div class="row fs-6">
                              4
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer d-flex text-dark justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(4, \'' . $query . '\')">
                                ' . $kotak4 . '
                              </a>
                            </div>
                          </div>
                          <div class="col-md-3 text-white p-4 border" style="background-color: #54B435;">
                            <div class="row fs-6">
                              7
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-white d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(7, \'' . $query . '\')">
                              ' . $kotak7 . '  
                              </a>
                            </div>
                          </div>
                          <div class="col-md-3 bg-secondary text-white p-4 border">
                            <div class="row fs-6">
                              9
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-white d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(9, \'' . $query . '\')">
                              ' . $kotak9 . '    
                              </a>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-3 d-flex align-items-center rotate">
                            <div class="text text-center text-dark">
                              Sesuai
                              Ekspektasi
                            </div>
                          </div>
                          <div class="col-md-3 text-white p-4 border" style="background-color: #526D82;">
                            <div class="row fs-6">
                              2
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-white d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(2, \'' . $query . '\')">
                              ' . $kotak2 . '   
                              </a>
                            </div>
                          </div>
                          <div class="col-md-3 text-dark p-4 border" style="background-color: #FCE22A;">
                            <div class="row fs-6">
                              5
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-dark d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(5, \'' . $query . '\')">
                              ' . $kotak5 . '   
                              </a>
                            </div>
                          </div>
                          <div class="col-md-3 text-white p-4 border" style="background-color: #54B435;">
                            <div class="row fs-6">
                              8
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-white d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(8, \'' . $query . '\')">
                              ' . $kotak8 . '   
                              </a>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-3 d-flex align-items-center rotate">
                            <div class="text text-center text-dark">
                              Dibawah
                              Ekspektasi
                            </div>
                          </div>
                          <div class="col-md-3 bg-danger text-white p-4 border">
                            <div class="row fs-6">
                              1
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-white d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(1, \'' . $query . '\')">
                              ' . $kotak1 . '   
                              </a>
                            </div>
                          </div>
                          <div class="col-md-3 text-white p-4 border" style="background-color: #526D82;">
                            <div class="row fs-6">
                              3
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-white d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(3, \'' . $query . '\')">
                              ' . $kotak3 . '   
                              </a>
                            </div>
                          </div>
                          <div class="col-md-3 text-dark p-4 border" style="background-color: #FCE22A;">
                            <div class="row fs-6">
                              6
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-dark d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(6, \'' . $query . '\')">
                              ' . $kotak6 . '   
                              </a>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-3">
                          </div>
                          <div class="col-md-3 text-dark p-2 d-flex justify-content-center align-items-center">
                            Rendah
                          </div>
                          <div class="col-md-3 text-dark p-2 d-flex justify-content-center align-items-center">
                            Sedang
                          </div>
                          <div class="col-md-3 text-dark p-2 d-flex justify-content-center align-items-center">
                            Tinggi
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-3">
                          </div>
                          <div class="col-md-9 p-2 bg-danger text-white d-flex justify-content-center align-items-center">
                            Potensi
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
          
                  <div class="col-md-4">
                    <div class="row">
                      <canvas id="' . $chart_id . '" width="600" height="500"></canvas>
                    </div>
                  </div>
                </div>
              </div>
    
            <script type="text/javascript">
              document.addEventListener("DOMContentLoaded", function() {
                generate_chart("' . $chart_id . '", ' . $chart_data . ', "' . $query . '");
              });
            </script>';

      return $html;
    }
  }

  if (!function_exists('generate_talent_pool_2')) {
    function generate_talent_pool_2($data, $query, $chart_data)
    {
      $kotak1 =  0;
      $kotak2 =  0;
      $kotak3 =  0;
      $kotak4 =  0;
      $kotak5 =  0;
      $kotak8 =  0;
      $kotak6 =  0;
      $kotak7 =  0;
      $kotak9 =  0;

      foreach ($data as $value) {
        $kotak1 = $value['kotak'] == 1 ? $value['jumlah'] : $kotak1;
        $kotak2 = $value['kotak'] == 2 ? $value['jumlah'] : $kotak2;
        $kotak3 = $value['kotak'] == 3 ? $value['jumlah'] : $kotak3;
        $kotak4 = $value['kotak'] == 4 ? $value['jumlah'] : $kotak4;
        $kotak5 = $value['kotak'] == 5 ? $value['jumlah'] : $kotak5;
        $kotak6 = $value['kotak'] == 6 ? $value['jumlah'] : $kotak6;
        $kotak7 = $value['kotak'] == 7 ? $value['jumlah'] : $kotak7;
        $kotak8 = $value['kotak'] == 8 ? $value['jumlah'] : $kotak8;
        $kotak9 = $value['kotak'] == 9 ? $value['jumlah'] : $kotak9;
      }

      $html = '
              <div class="card-body">
                <div class="d-flex justify-content-around">
                  <div class="col-md-6 d-flex flex-column">
                    <div class="row">
                      <div class="col-md-1 bg-primary d-flex justify-content-center align-items-center text-light rotate">
                        <div class="text text-white">
                          Kinerja
                        </div>
                      </div>
                      <div class="col-md-9 text-light">
                        <div class="row">
                          <div class="col-md-3 d-flex align-items-center rotate">
                            <div class="text text-center text-dark">
                              Diatas
                              Ekspektasi
                            </div>
                          </div>
                          <div class="col-md-3 text-dark p-4 border" style="background-color: #FCE22A;">
                            <div class="row fs-6">
                              4
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer d-flex text-dark justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(4, \'' . $query . '\')">
                                ' . $kotak4 . '
                              </a>
                            </div>
                          </div>
                          <div class="col-md-3 text-white p-4 border" style="background-color: #54B435;">
                            <div class="row fs-6">
                              7
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-white d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(7, \'' . $query . '\')">
                              ' . $kotak7 . '  
                              </a>
                            </div>
                          </div>
                          <div class="col-md-3 bg-secondary text-white p-4 border">
                            <div class="row fs-6">
                              9
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-white d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(9, \'' . $query . '\')">
                              ' . $kotak9 . '    
                              </a>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-3 d-flex align-items-center rotate">
                            <div class="text text-center text-dark">
                              Sesuai
                              Ekspektasi
                            </div>
                          </div>
                          <div class="col-md-3 text-white p-4 border" style="background-color: #526D82;">
                            <div class="row fs-6">
                              2
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-white d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(2, \'' . $query . '\')">
                              ' . $kotak2 . '   
                              </a>
                            </div>
                          </div>
                          <div class="col-md-3 text-dark p-4 border" style="background-color: #FCE22A;">
                            <div class="row fs-6">
                              5
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-dark d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(5, \'' . $query . '\')">
                              ' . $kotak5 . '   
                              </a>
                            </div>
                          </div>
                          <div class="col-md-3 text-white p-4 border" style="background-color: #54B435;">
                            <div class="row fs-6">
                              8
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-white d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(8, \'' . $query . '\')">
                              ' . $kotak8 . '   
                              </a>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-3 d-flex align-items-center rotate">
                            <div class="text text-center text-dark">
                              Dibawah
                              Ekspektasi
                            </div>
                          </div>
                          <div class="col-md-3 bg-danger text-white p-4 border">
                            <div class="row fs-6">
                              1
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-white d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(1, \'' . $query . '\')">
                              ' . $kotak1 . '   
                              </a>
                            </div>
                          </div>
                          <div class="col-md-3 text-white p-4 border" style="background-color: #526D82;">
                            <div class="row fs-6">
                              3
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-white d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(3, \'' . $query . '\')">
                              ' . $kotak3 . '   
                              </a>
                            </div>
                          </div>
                          <div class="col-md-3 text-dark p-4 border" style="background-color: #FCE22A;">
                            <div class="row fs-6">
                              6
                            </div>
                            <div class="row fs-3">
                              <a href="javascript:void(0)" class="pointer text-dark d-flex justify-content-center" data-bs-toggle="modal" data-bs-target="#modalTalent" onclick="get_talent_pool(6, \'' . $query . '\')">
                              ' . $kotak6 . '   
                              </a>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-3">
                          </div>
                          <div class="col-md-3 text-dark p-2 d-flex justify-content-center align-items-center">
                            Rendah
                          </div>
                          <div class="col-md-3 text-dark p-2 d-flex justify-content-center align-items-center">
                            Sedang
                          </div>
                          <div class="col-md-3 text-dark p-2 d-flex justify-content-center align-items-center">
                            Tinggi
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-3">
                          </div>
                          <div class="col-md-9 p-2 text-white d-flex justify-content-center align-items-center" style="background-color: #8E8FFA;">
                            Potensi
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
          
                  <div class="col-md-4">
                    <div class="row">
                      <canvas id="talent_pool_chart" width="600" height="500"></canvas>
                    </div>
                  </div>
                </div>
              </div>
    
            <script type="text/javascript">
              document.addEventListener("DOMContentLoaded", function() {
                generate_chart("talent_pool_chart", ' . $chart_data . ', "' . $query . '");
              });
            </script>';

      return $html;
    }
  }

  if (!function_exists('get_performance')) {
    function get_performance($nilai_kinerja, $nilai_potensial)
    {
      $kinerjaKategori = '';
      $potensialKategori = '';

      if ($nilai_kinerja >= 84 && $nilai_kinerja <= 100) {
        $kinerjaKategori = 'di atas ekspektasi';
      } elseif ($nilai_kinerja >= 67 && $nilai_kinerja <= 83) {
        $kinerjaKategori = 'sesuai ekspektasi';
      } elseif ($nilai_kinerja < 67) {
        $kinerjaKategori = 'di bawah ekspektasi';
      }

      if ($nilai_potensial >= 84 && $nilai_potensial <= 100) {
        $potensialKategori = 'tinggi';
      } elseif ($nilai_potensial >= 67 && $nilai_potensial <= 83) {
        $potensialKategori = 'menengah';
      } elseif ($nilai_potensial < 67) {
        $potensialKategori = 'rendah';
      }

      $output = "Kinerja $kinerjaKategori dan potensial $potensialKategori";

      switch ($output) {
        case "Kinerja di atas ekspektasi dan potensial tinggi":
          return 9;
        case "Kinerja sesuai ekspektasi dan potensial tinggi":
          return 8;
        case "Kinerja di atas ekspektasi dan potensial menengah":
          return 7;
        case "Kinerja di bawah ekspektasi dan potensial tinggi":
          return 6;
        case "Kinerja sesuai ekspektasi dan potensial menengah":
          return 5;
        case "Kinerja di atas ekspektasi dan potensial rendah":
          return 4;
        case "Kinerja di bawah ekspektasi dan potensial menengah":
          return 3;
        case "Kinerja sesuai ekspektasi dan potensial rendah":
          return 2;
        case "Kinerja di bawah ekspektasi dan potensial rendah":
          return 1;
        default:
          return 0;
      };
    }
  }

  if (!function_exists('count_instrument_weight')) {
    function count_instrument_weight($percentage, $standard_value)
    {
      $data = ($percentage * $standard_value) / 100;

      return $data;
    }
  }

  if (!function_exists('convert_formula')) {
    function convert_formula($formula)
    {
      switch ($formula) {
        case 'Equals':
          return '=';
        case 'Addition':
          return '+';
        case 'Substraction':
          return '-';
        case 'Multiplication':
          return '*';
        case 'Division':
          return '/';
        default:
          return '';
      }
    }
  }

  if (!function_exists('calculate_value')) {
    function calculate_value($operator, $bilangan, $nilai_unsur)
    {
      switch ($operator) {
        case '=':
          return $nilai_unsur;
        case '+':
          return $nilai_unsur + $bilangan;
        case '-':
          return $nilai_unsur - $bilangan;
        case '*':
          return $nilai_unsur * $bilangan;
        case '/':
          if ($bilangan != 0) {
            return $nilai_unsur / $bilangan;
          } else {
            return 0;
          }
        default:
          return 0;
      }
    }
  }

  if (!function_exists('format_array')) {
    function format_array($query, $field1, $field2 = NULL)
    {
      $data = [];
      $data += ['' => '',];
      foreach ($query as $item) {
        if ($field2 !== NULL) {
          $data += [
            $item['id'] => $item[$field1] . ' - ' . $item[$field2]
          ];
        } else {
          $data += [
            $item['id'] => $item[$field1]
          ];
        }
      }

      return $data;
    }
  }

  if (!function_exists('calculate_age')) {
    function calculate_age($birth_date)
    {
      $birth_date = new DateTime($birth_date);
      $today = new DateTime();
      $diff = $today->diff($birth_date);

      $age = "{$diff->y} Tahun, {$diff->m} Bulan, {$diff->d} Hari";

      return $age;
    }
  }


  // if (!function_exists('calculate_age')) {
  //   function calculate_age($birth_date)
  //   {
  //       // Konversi tanggal dengan `strtotime`
  //       $parsed_date = date('Y-m-d', strtotime($birth_date));

  //       if (!$parsed_date) {
  //           return "Format tanggal tidak valid";
  //       }

  //       // Buat objek DateTime
  //       $birth_date = new DateTime($parsed_date);
  //       $today = new DateTime();

  //       // Hitung selisih
  //       $diff = $today->diff($birth_date);

  //       // Format umur
  //       $age = "{$diff->y} Tahun, {$diff->m} Bulan, {$diff->d} Hari";

  //       return $age;
  //   }
  // }

  if (!function_exists('format_list')) {
    function format_list($query, $string)
    {
      $data = [];
      $data += ['' => '',];
      foreach ($query as $item) {
        $data += [
          $item[$string] => $item[$string]
        ];
      }

      return $data;
    }
  }

  if (!function_exists('format_raw')) {
    function format_raw($query, $type, $user_id, $employee_id, $evaluator, $interview_participant_id = NULL)
    {
      $result = [];
      foreach ($query as $key => $item) {
        // Set default value
        if (!$item) {
          $item = NULL;
        }

        $result[] = [
          'id'                      => generate_uuid(),
          'user_id'                 => $user_id,
          'type'                    => $type,
          'employee_id'             => $employee_id,
          'evaluator'               => $evaluator,
          'aspect_question_id'      => $key,
          'question_instrument_id'  => $item,
          'interview_participant_id' => $interview_participant_id,
        ];
      }

      return $result;
    }
  }

  if (!function_exists('get_category')) {
    function get_category($final_score, $use_badge = true)
    {
      // Define category labels and classes
      $categories = [
        'MS' => ['label' => 'Memenuhi Syarat', 'min_score' => 80, 'badge' => 'bg-success'],
        'MMS' => ['label' => 'Masih Memenuhi Syarat', 'min_score' => 68, 'badge' => 'bg-info'],
        'KMS' => ['label' => 'Kurang Memenuhi Syarat', 'min_score' => 0, 'badge' => 'bg-warning'],
      ];

      foreach ($categories as $key => $category) {
        if ($final_score >= $category['min_score']) {
          // If using badge, return with badge HTML
          return $use_badge
            ? '<span class="badge ' . $category['badge'] . '">' . $key . ' (' . $category['label'] . ')</span>'
            : $key . ' (' . $category['label'] . ')';
        }
      }

      // Return default category if none matched (optional)
      return '<span class="badge bg-secondary">Undefined</span>';
    }
  }

  if (!function_exists('convert_to_base64')) {
    function convert_to_base64($img_path)
    {
      // Try to access the image locally from the file system
      $path = parse_url($img_path, PHP_URL_PATH);
      $local_path = FCPATH . ltrim($path, '/');

      if (file_exists($local_path)) {
        // If the file exists locally, read it directly
        $data = file_get_contents($local_path);
      } else {
        // Otherwise, fall back to HTTP request with SSL options
        $opciones_ssl = array(
          "ssl" => array(
            "verify_peer"      => false,
            "verify_peer_name" => false,
          )
        );
        $data = file_get_contents($img_path, false, stream_context_create($opciones_ssl));
      }

      $extencion = pathinfo($img_path, PATHINFO_EXTENSION);
      $img_base_64 = base64_encode($data);
      return 'data:image/' . $extencion . ';base64,' . $img_base_64;
    }
  }

  if (!function_exists('get_eselon_type')) {
    function get_eselon_type($type)
    {
      switch ($type) {
        case "ES1":
          return "Eselon I";

          break;
        case "ES2":
          return "Eselon II";

          break;
        case "ES3":
          return "Eselon III";

          break;
        case "ES4":
          return "Eselon IV";

          break;
        case "ES5":
          return "Eselon V";

          break;
        default:
          return "Undefined Type";
          break;
      }
    }
  }

  if (!function_exists('get_eselon_level')) {
    function get_eselon_level($value, $is_es = true)
    {
      if ($is_es) {
        switch ($value) {
          case "ES1":
            return "5";

            break;
          case "ES2":
            return "5";

            break;
          case "ES3":
            return "3";

            break;
          case "ES4":
            return "2";

            break;
          case "ES5":
            return "1";

            break;
          case "FUNG":
            return "3";

            break;
          default:
            return "Undefined Type";
            break;
        }
      }

      if (!$is_es) {
        switch ($value) {
          case "5":
            return "ES1";

            break;
          case "4":
            return "ES2";

            break;
          case "3":
            return "ES3";

            break;
          case "2":
            return "ES4";

            break;
          case "1":
            return "ES5";

            break;
          default:
            return "Undefined Type";
            break;
        }
      }
    }
  }

  if (!function_exists('convert_scale')) {
    function convert_scale($value, $baseline, $target)
    {
      return (($value / $baseline) * $target);
    }
  }
