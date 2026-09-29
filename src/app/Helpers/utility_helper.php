<?php

/**
 * Create Utilty Method for additional functionality
 *
 * @author 		  : Kevin Kurnia Wikarta
 * @copyright   : Copyright (c) 2025
 * @link 		    : https://kevkw.id
 * @version 	  : 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	  : 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	  : 2025-04-04
 */

use App\Libraries\Acl\AclLib;

if (!function_exists('uuid_v4')) {
  function uuid_v4()
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

if (! function_exists('greeting')) {
  function greeting()
  {
    $hour = date('H');
    $hour = intval($hour);

    if ($hour >= 10 && $hour < 15)
      $greeting = "Selamat Siang";
    else if ($hour >= 15 && $hour < 18)
      $greeting = "Selamat Sore";
    else if ($hour >= 18 && $hour <= 23)
      $greeting = "Selamat Malam";
    else
      $greeting = "Selamat Pagi";

    return $greeting;
  }
}

if (! function_exists('boolean_integer')) {
  function boolean_integer($value, $true = 'Ya', $false = 'Tidak')
  {
    return $value === "1" ? $true : $false;
  }
}

if (! function_exists('nullable_value')) {
  function nullable_value($value, $placeholder = '-')
  {
    return !is_null($value) ? $value : $placeholder;
  }
}

if (! function_exists('icon_value')) {
  function icon_value($icon_name, $size_class = 'fs-5')
  {
    return '<i class="' . $icon_name . ' ' . $size_class . '"></i>';
  }
}

if (! function_exists('is_required')) {
  function is_required($text = '*')
  {
    return '<span class="text-danger">' . $text . '</span>';
  }
}

if (! function_exists('verified_label')) {
  function verified_label($status)
  {
    if ($status === "0") {
      return '<span class="badge bg-warning"><i class="ri-refresh-line fs-17 align-middle"></i> Menunggu Verifikasi</span>';
    } else if ($status === "1") {
      return '<span class="badge bg-success"><i class="ri-checkbox-circle-line fs-17 align-middle"></i> Diterima</span>';
    } else {
      return '<span class="badge bg-danger"><i class="ri-close-circle-line fs-17 align-middle"></i> Ditolak</span>';
    }
  }
}

if (!function_exists('pass_hash')) {
  function pass_hash($password, $algo = 'argon2id')
  {
    $hashAlgos = [
      'argon2id' => PASSWORD_ARGON2ID,
      'argon2i'  => PASSWORD_ARGON2I,
      'bcrypt'   => PASSWORD_BCRYPT,
    ];

    $selected_algo = $hashAlgos[$algo] ?? PASSWORD_DEFAULT;

    $result = password_hash($password, $selected_algo);

    return $result;
  }
}

if (!function_exists('kv_encrypt')) {
  function kv_encrypt($plain_text, $key)
  {
    if (empty($key)) {
      show_error('Encryption Key Not Found!');
    }

    $iv = substr(hash('sha256', $plain_text), 0, 16);
    $ciphertext = openssl_encrypt($plain_text, 'aes-256-cbc', hash('sha256', $key, true), OPENSSL_RAW_DATA, $iv);

    return base64_encode($iv . $ciphertext);
  }
}

if (!function_exists('kv_decrypt')) {
  function kv_decrypt($encrypted_text, $key)
  {
    if (empty($key)) {
      show_error('Encryption Key Not Found !');
    }

    if (empty($encrypted_text)) {
      return '';
    }

    $data = base64_decode($encrypted_text, true);
    if ($data === false || strlen($data) < 16) {
      return $encrypted_text;
    }

    $iv = substr($data, 0, 16);
    $ciphertext = substr($data, 16);
    $result = @openssl_decrypt($ciphertext, 'aes-256-cbc', hash('sha256', $key, true), OPENSSL_RAW_DATA, $iv);

    return ($result === false) ? $encrypted_text : $result;
  }
}

if (!function_exists('user_hash')) {
  function user_hash(array $data)
  {
    $key = getenv('APP_KEY');

    $data['data']['username'] =  kv_encrypt($data['data']['username'], $key);
    $data['data']['password'] =  pass_hash($data['data']['password']);

    return $data;
  }
}

if (!function_exists('format_currency')) {
  function format_currency($amount, $currency = 'IDR', $locale = 'id_ID')
  {
    $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

    return $formatter->formatCurrency($amount, $currency);
  }
}

if (!function_exists('is_authorized')) {
  function is_authorized($menu_code, $action_code)
  {
    $result = FALSE;

    if (session()->has('access_controls')) {
      $access_controls = session()->get('access_controls');
      if (isset($access_controls[$menu_code][$action_code]) && !empty($access_controls[$menu_code][$action_code])) {
        $result = TRUE;
      }
    } else if (session()->has('login_user_id')) {
      $acl_lib = new AclLib();

      $result = $acl_lib->get_access(session()->get('login_user_id'), $menu_code, $action_code);
    }

    return $result;
  }
}

if (!function_exists('access_denied')) {
  function access_denied()
  {
    return show_error("Maaf, akses Anda untuk fasilitas tersebut ditolak!", 403);
  }
}

if (!function_exists('format_date')) {
  function format_date($tgl, $tampil_hari = true)
  {
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
      1 => 'Januari',
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

    // Pastikan format tanggal sesuai dengan 'Y-m-d'
    if (strlen($tgl) != 10) {
      return "Format tanggal salah";
    }

    // Mengambil bagian-bagian dari tanggal
    $tanggal = (int) substr($tgl, 8, 2);
    $bulan   = (int) substr($tgl, 5, 2);
    $tahun   = (int) substr($tgl, 0, 4);

    // Validasi tanggal
    if (!checkdate($bulan, $tanggal, $tahun)) {
      return "Tanggal tidak valid";
    }

    $text = '';

    if ($tampil_hari) {
      $urutan_hari = date('w', strtotime($tgl));
      $hari        = $nama_hari[$urutan_hari];
      $text       .= "$hari, $tanggal " . $nama_bulan[$bulan] . " $tahun";
    } else {
      $text       .= "$tanggal " . $nama_bulan[$bulan] . " $tahun";
    }

    return $text;
  }
}

if (!function_exists('get_sub_type')) {
  function get_sub_type($is_instance_required = false)
  {
    $data = ['' => ''];

    if ($is_instance_required) {
      $data['0'] = 'Instansi';
    }

    $data += [
      '1' => 'Unit Organisasi',
      '2' => 'Unit Kerja',
      '3' => 'Unit Wilayah',
      '4' => 'Unit Pusat',
      '5' => 'Instansi Lainnya',
    ];

    return $data;
  }
}


if (!function_exists('get_sub_type_value')) {
  function get_sub_type_value($sub_type)
  {
    switch ($sub_type) {
      case "0":
        return "Instansi";
        break;
      case "1":
        return "Unit Organisasi";
        break;
      case "2":
        return "Unit Kerja";
        break;
      case "3":
        return "Unit Wilayah";
        break;
      case "4":
        return "Unit Pusat";
        break;
      case "5":
        return "Instansi Lainnya";
        break;
      case "0":
        return "Undefined Type";
        break;
    }
  }
}

if (!function_exists('render_select2_tree_options')) {
  function render_select2_tree_options($tree, $label, $value = 'id', $level = 0)
  {
    $options = [];
    $options[''] = '';

    foreach ($tree as $node) {
      $indent = str_repeat('-- ', $level);
      $options[$node[$value]] = $indent . $node[$label];

      if (!empty($node['children'])) {
        $options += render_select2_tree_options($node['children'], $label, $value, $level + 1);
      }
    }

    return $options;
  }
}

if (!function_exists('format_options')) {
  function format_options($query, $label, $value = 'id')
  {
    $options = [];
    $options[''] = '';

    foreach ($query as $item) {
      $options += [
        $item[$value] => $item[$label]
      ];
    }

    return $options;
  }
}

if (!function_exists('format_multiple_options')) {
  function format_multiple_options($query, $label, $value = 'id')
  {
    $options = [];

    foreach ($query as $item) {
      $options += [
        $item[$value] => $item[$label]
      ];
    }

    return $options;
  }
}

if (!function_exists('get_month_options')) {
  function get_month_options()
  {
    $month_options = [
      ''   => '',
      '01' => 'Januari',
      '02' => 'Februari',
      '03' => 'Maret',
      '04' => 'April',
      '05' => 'Mei',
      '06' => 'Juni',
      '07' => 'Juli',
      '08' => 'Agustus',
      '09' => 'September',
      '10' => 'Oktober',
      '11' => 'November',
      '12' => 'Desember',
    ];

    return $month_options;
  }
}

if (!function_exists('get_month_value')) {
  function get_month_value($month)
  {
    switch ($month) {
      case '01':
        return 'Januari';
        break;
      case '02':
        return 'Februari';
        break;
      case '03':
        return 'Maret';
        break;
      case '04':
        return 'April';
        break;
      case '05':
        return 'Mei';
        break;
      case '06':
        return 'Juni';
        break;
      case '07':
        return 'Juli';
        break;
      case '08':
        return 'Agustus';
        break;
      case '09':
        return 'September';
        break;
      case '10':
        return 'Oktober';
        break;
      case '11':
        return 'November';
        break;
      case '12':
        return 'Desember';
        break;
      default:
        return 'Tidak Terdefinisikan';
        break;
    }
  }
}

if (!function_exists('get_gender_options')) {
  function get_gender_options()
  {
    $gender_options = [
      ''  => '',
      '0' => 'Perempuan',
      '1' => 'Laki-Laki',
    ];

    return $gender_options;
  }
}

if (!function_exists('get_gender_value')) {
  function get_gender_value($gender)
  {
    switch ($gender) {
      case '0':
        return 'Perempuan';
        break;
      case '1':
        return 'Laki-Laki';
        break;
      default:
        return 'Tidak Terdefinisikan';
        break;
    }
  }
}

if (!function_exists('get_category_type_options')) {
  function get_category_type_options()
  {
    $gender_options = [
      ''  => '',
      '0' => 'Pelatihan',
      '1' => 'Assessment',
    ];

    return $gender_options;
  }
}

if (!function_exists('get_category_type_value')) {
  function get_category_type_value($gender)
  {
    switch ($gender) {
      case '0':
        return 'Pelatihan';
        break;
      case '1':
        return 'Assessment';
        break;
      default:
        return 'Tidak Terdefinisikan';
        break;
    }
  }
}

if (!function_exists('get_assignment_type_options')) {
  function get_assignment_type_options()
  {
    $gender_options = [
      ''  => '',
      '0' => 'Dalam Negeri',
      '1' => 'Luar Negeri',
    ];

    return $gender_options;
  }
}

if (!function_exists('get_assignment_type_value')) {
  function get_assignment_type_value($gender)
  {
    switch ($gender) {
      case '0':
        return 'Dalam Negeri';
        break;
      case '1':
        return 'Luar Negeri';
        break;
      default:
        return 'Tidak Terdefinisikan';
        break;
    }
  }
}

if (!function_exists('get_placement_type_options')) {
  function get_placement_type_options()
  {
    $gender_options = [
      ''  => '',
      '0' => 'Dalam Negeri',
      '1' => 'Luar Negeri',
    ];

    return $gender_options;
  }
}

if (!function_exists('get_placement_type_value')) {
  function get_placement_type_value($gender)
  {
    switch ($gender) {
      case '0':
        return 'Dalam Negeri';
        break;
      case '1':
        return 'Luar Negeri';
        break;
      default:
        return 'Tidak Terdefinisikan';
        break;
    }
  }
}

if (!function_exists('get_presence_type_options')) {
  function get_presence_type_options()
  {
    $gender_options = [
      ''  => '',
      '0' => 'Masuk',
      '1' => 'Pulang',
    ];

    return $gender_options;
  }
}

if (!function_exists('get_presence_type_value')) {
  function get_presence_type_value($gender)
  {
    switch ($gender) {
      case '0':
        return 'Masuk';
        break;
      case '1':
        return 'Pulang';
        break;
      default:
        return 'Tidak Terdefinisikan';
        break;
    }
  }
}

if (!function_exists('set_default_value')) {
  function set_default_value($value, $default_value = '0')
  {
    return ($value === null || $value === '') ? $default_value : $value;
  }
}


// vald nip format
if (! function_exists('is_not_valid_nip_message')) {
  function is_not_valid_nip_message($str)
  {
    // when null is success
    $message = NULL;

    // NIP BKN
    if (mb_strlen($str) == 18 && is_numeric($str)) {
      $appointment_year = intval(substr($str, 8, 4));
      $appointment_month = substr($str, 12, 2);
      $appointment_day = 1;
      $gender = substr($str, 14, 1);
      $gender = intval($gender); // 1: male, 2 female

      $random = substr($str, -3);

      $birt_date = birth_date_nip($str);

      $age = ($birt_date !== FALSE ? convert_age('Y-m-d', $birt_date) : 0);

      if ($birt_date == FALSE or $age <= 6) {
        $message = "Data '" . $str . "' in %s field does not match the format.";
      }
      $appointment_date = checkdate(intval($appointment_month), $appointment_day, $appointment_year);
      if ($appointment_date == FALSE or $appointment_year > date('Y')) {
        $message = "Data '" . $str . "' in %s field does not match the format.";
      }

      if ($gender < 1 or $gender > 2) {
        $message = "Data '" . $str . "' in %s field does not match the format.";
      }
    }
    // NRP - Polri
    else if (mb_strlen($str) == 8 && is_numeric($str)) {
      $curent_year = date('Yn');
      $curent_year = intval($curent_year);
      $age = 0;

      $born_year_ext = intval(substr($str, 0, 2));
      $born_year_prev = 19;
      if ($born_year_ext < 10) {
        if ($born_year_ext == 0) {
          $born_year_ext = '00';
        }
        $born_year_prev = 20;
      }
      $born_year = $born_year_prev . $born_year_ext;

      $born_month = intval(substr($str, 2, 2));
      if ($born_month > 0 && $born_month <= 12) {
        $born_year .= $born_month;
        $age = convert_age('Yn', $born_year, 'Yn');

        if ($age < 18 or $age > 60) {
          $message = "Data '" . $str . "' in %s field does not match the format.";
        }
      } else {
        $message = "Data '" . $str . "' in %s field does not match the format.";
      }
    }
    // NRP - TNI
    else if (mb_strlen($str) == 6 && is_numeric($str)) {
    } else {
      $message = "Data '" . $str . "' in %s field does not match the format.";
    }

    return $message;
  }
}

// get birhtdate from nip
if (! function_exists('birth_date_nip')) {
  function birth_date_nip($nip)
  {
    $birt_year = intval(substr($nip, 0, 4));
    $birt_month = substr($nip, 4, 2);
    $birt_day = substr($nip, 6, 2);

    $birt_date = checkdate(intval($birt_month), intval($birt_day), $birt_year);
    if ($birt_date !== FALSE) {
      $birtdate = $birt_year . '-' . $birt_month . '-' . $birt_day;

      return $birtdate;
    }

    return FALSE;
  }
}

/* Convert Date from 31/12/2013 to 2013-12-31 or in reverse 2013-12-31 to 31/12/2013
- use for save to database or display
*/
if (! function_exists('convert_date')) {
  function convert_date($date_format = 'd/m/Y', $date = '', $date_format_result = 'Y-m-d')
  {
    $result = '';
    if (!empty($date)) {
      if (floatval(phpversion()) >= 5.3) {
        $date_new = DateTime::createFromFormat($date_format, $date);
        $result = $date_new->format($date_format_result);
      } else {
        if (strpos($date, '/') !== FALSE) {
          $temp_date = explode('/', $date);
          $date = $temp_date[2] . '-' . $temp_date[1] . '-' . $temp_date[0];
        } else {
          $temp_date = explode('-', $date);
          $date = $temp_date[0] . '-' . $temp_date[1] . '-' . $temp_date[2];
        }

        $result = date($date_format_result, strtotime($date));
      }
    }

    return $result;
  }
}

/* Convert Date Time from 31/12/2013 22:55:10 to 2013-12-31 22:55:10 or in reverse 2013-12-31 22:55:10 to 31/12/2013 22:55:10
- use for save to database or display
*/
if (! function_exists('convert_date_time')) {
  function convert_date_time($date_time_format = 'd/m/Y H:i:s', $date_time = NULL, $date_time_format_result = 'Y-m-d H:i:s')
  {
    // if($date_time == '0000-00-00 00:00:00')
    // 	$date_time = '';
    if (substr($date_time, 0, 10) === '0000-00-00')
      $date_time = '';

    $result = $date_time;
    if (!empty($date_time)) {
      $date_new = DateTime::createFromFormat($date_time_format, $date_time);
      if ($date_new) {
        $result = $date_new->format($date_time_format_result);
      } else
        $result = '';
    }

    return $result;
  }
}

/* Valid Date (ISO format)
 *
 * @access	public
 * @param	string
 * @return	bool
 */
if (! function_exists('valid_date')) {
  function valid_date($str)
  {
    if (preg_match("/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{4})/", $str)) {
      $arr = explode("/", $str);  // splitting the array
      $dd = $arr[0];        // third element is days
      $mm = $arr[1];        // second element is month
      $yyyy = $arr[2];      // first element of the array is year
      return (checkdate($mm, $dd, $yyyy));
    } else {
      return FALSE;
    }
  }
}

if (! function_exists('convert_age')) {
  function convert_age($date_format = 'd/m/Y', $date = '', $convert_format = 'Y-m-d', $to_date = '')
  {
    $result = 0;
    if (!empty($date)) {
      $birthday = convert_date($date_format, $date, $convert_format);
      if (!empty($birthday)) {
        if (!empty($to_date)) {
          $today = DateTime::createFromFormat('Y-m-d', $to_date);
        } else {
          $today = new DateTime();
        }

        // $biday = new DateTime($birthday);
        $biday = DateTime::createFromFormat($convert_format, $birthday);


        $diff = $today->diff($biday);
        $result = $diff->y;
      }
    }

    return $result;
  }
}

if (!function_exists('render_menu_row')) {
  function render_menu_row($menu, $menu_code, $group_permissions, $read_only, $level = 1)
  {
    $indent = str_repeat('&nbsp;', $level * 5);

    // Sanitasi agar class/id tidak error di jQuery selector
    $safe_menu_code = preg_replace('/[^a-zA-Z0-9_]/', '_', $menu_code);

    echo '<tr>';
    echo '<td class="fw-bold">';
    echo $indent;

    if ($read_only) {
      echo $menu['menu_name'];
    } else {
      $options = [
        'id'    => 'parent_' . $safe_menu_code,
        'class' => 'form-check-input me-1',
      ];
      echo form_checkbox('menu_code[parent_' . $safe_menu_code . ']', $menu_code, false, $options);
      echo form_label($menu['menu_name'], 'parent_' . $safe_menu_code, ['class' => 'form-check-label']);
    }

    echo '</td>';
    echo '<td class="d-flex flex-wrap gap-3">';

    $existing_permissions = array_column($group_permissions, 'permission_id');

    foreach ($menu['permissions'] as $permission) {
      $is_checked = in_array($permission['id'], $existing_permissions);
      $options = [
        'id'    => 'permission_' . $permission['id'],
        'class' => 'form-check-input child_' . $safe_menu_code,
        'value' => $permission['id'],
      ];

      if ($read_only) {
        echo '<div>';
        echo $is_checked
          ? '<span class="badge bg-' . $permission['indicator'] . '">' . $permission['action_name'] . '</span>'
          : '<span>&nbsp;</span>';
        echo '</div>';
      } else {
        echo '<div class="form-check form-switch">';
        echo form_checkbox('permissions[]', $permission['id'], $is_checked, $options);
        echo form_label($permission['action_name'], 'permission_' . $permission['id'], ['class' => 'form-check-label']);
        echo '</div>';
      }
    }

    echo '</td>';
    echo '</tr>';

    if (!empty($menu['menus'])) {
      foreach ($menu['menus'] as $sub_code => $sub_menu) {
        render_menu_row($sub_menu, $sub_code, $group_permissions, $read_only, $level + 1);
      }
    }
  }
}
