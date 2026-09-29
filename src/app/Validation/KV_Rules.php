<?php

/**
 * Additional Validation Rules Codeigniter 4
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-03-14
 */

namespace App\Validation;

use DateTime;

class KV_Rules
{
    /*
    * Custom Validation For NIK
    */
    public function is_valid_nik(string $str): bool
    {
        $valid = TRUE;

        /*
		Nomor Induk Kependudukan atau NIK adalah nomor identitas Penduduk yang bersifat unik atau khas, tunggal dan melekat pada seseorang yang terdaftar sebagai Penduduk Indonesia. NIK berlaku seumur hidup dan selamanya, yang diberikan oleh Pemerintah dan diterbitkan oleh Instansi Pelaksana kepada setiap Penduduk setelah dilakukan pencatatan biodata. NIK pertama kali diperkenalkan oleh Direktorat Jenderal Administrasi Kependudukan ketika Institusi Pemerintah ini menerapkan sistem KTP nasional yang terkomputerisasi.

		NIK terdiri dari 16 digit. Kode penyusun NIK terdiri dari 2 digit awal merupakan kode provinsi, 2 digit setelahnya merupakan kode kota/kabupaten, 2 digit sesudahnya kode kecamatan, 6 digit selanjutnya merupakan tanggal lahir dalam format hhbbtt (untuk wanita tanggal ditambah 40), lalu 4 digit terakhir merupakan nomor urut yang dimulai dari 0001. Sebagai contoh, misalkan seorang perempuan lahir di Kota Bandung tanggal 17 Agustus 1990 maka NIK-nya adalah: 10 50 24 570890 0001. Apabila ada orang lain (perempuan) dengan domisili dan tanggal lahir yang sama mendaftar, maka NIK-nya adalah 10 50 24 570890 0002. Apabila ada orang lain (laki-laki) dengan domisili dan tanggal lahir yang sama mendaftar, maka NIK-nya adalah 10 50 24 170890 0001.

		NIK dicantumkan dalam setiap Dokumen Kependudukan dan dijadikan dasar penerbitan KTP, paspor, surat izin mengemudi, nomor pokok wajib pajak, polis asuransi, sertifikat hak atas tanah, dan penerbitan dokumen identitas lainnya.


			35-15-02-49-07-85-0003
			32-02-01-05-01-91-0312
			- Dua angka pertama dari depan adalah kode provinsi
			- Dua angka kedua adalah kode kota/kabupaten
			- Dua angka ketiga adalah kode kecamatan
			- Dua angka keempat adalah kode tanggal lahir (Khusus Perempuan ditambah Nilai 40)
			- Dua angka kelima adalah bulan lahir
			- Dua angka keenam adalah tahun lahir
			- Empat angka di buntut adalah nomor komputerisasi

			// Saat ini yang dapat di check adalah dari tgl lahir sampe komputerisasi (karena kode wilayah belum memiliki databasenya)
		*/
        if (mb_strlen($str) == 16 && is_numeric($str)) {
            $province_code = substr($str, 0, 2);
            $city_code = substr($str, 2, 2);
            $district_code = substr($str, 4, 2);

            $birth_date = substr($str, 6, 2);
            $birth_date_numb = intval($birth_date);
            if ($birth_date_numb > 40) {
                // apabila wanita maka ditambahkan 40
                $birth_date_numb = ($birth_date_numb - 40);
            }

            $birth_month = substr($str, 8, 2);
            $birth_month_numb = intval($birth_month);
            $birth_year = substr($str, 10, 2);
            $birth_year_numb = intval($birth_year);
            $sequence_number = substr($str, 12, 4);

            if ($birth_date_numb < 1 or $birth_date_numb > 31) {
                $valid = FALSE;
            }

            if ($birth_month_numb < 1 or $birth_month_numb > 12) {
                $valid = FALSE;
            }

            $birth_year_full = '19' . $birth_year . $birth_month . $birth_date;
            if ($birth_year_numb >= 0) {
                $birth_year_full = '20' . $birth_year . $birth_month . $birth_date;
                $age = convert_age('Ymd', $birth_year_full);

                if ($age < 0 or $age >= 120) {
                    $valid = FALSE;
                }
            }
        } else {
            $valid = FALSE;
        }

        return $valid;
    }

    /*
    * Custom Validation For KK
    */
    public function is_valid_kk(string $str): bool
    {
        // Pastikan nomor KK memiliki panjang 16 digit dan hanya berisi angka
        if (strlen($str) !== 16 || !ctype_digit($str)) {
            return false;
        }

        // Ambil bagian-bagian dari Nomor KK
        $province_code = substr($str, 0, 2);
        $city_code = substr($str, 2, 2);
        $district_code = substr($str, 4, 2);

        $record_date = substr($str, 6, 6); // Format: hhbbtt (hari, bulan, tahun)
        $sequence_number = substr($str, 12, 4); // 4 digit terakhir

        // Validasi tanggal pencatatan (harus angka dan dalam rentang yang masuk akal)
        $record_day = intval(substr($record_date, 0, 2));
        $record_month = intval(substr($record_date, 2, 2));
        $record_year = intval(substr($record_date, 4, 2));

        // Tentukan tahun pencatatan lengkap (asumsi antara tahun 1900-2099)
        $current_year = date('Y');
        $current_century = substr($current_year, 0, 2);
        $full_record_year = ($record_year >= intval(substr($current_year, 2, 2)))
            ? ($current_century - 1) . str_pad($record_year, 2, '0', STR_PAD_LEFT)
            : $current_century . str_pad($record_year, 2, '0', STR_PAD_LEFT);

        // Validasi tanggal pencatatan
        if (!checkdate($record_month, $record_day, intval($full_record_year))) {
            return false;
        }

        return true;
    }

    /*
    * Custom Validation For NPWP
    */
    public function is_valid_npwp(string $str): bool
    {
        // Hapus karakter selain angka
        $cleaned_str = preg_replace('/[^0-9]/', '', $str);

        // Pastikan NPWP memiliki 15 digit angka
        if (strlen($cleaned_str) !== 15 || !ctype_digit($cleaned_str)) {
            return false;
        }

        // Pecah bagian NPWP berdasarkan struktur format
        $wp_type = substr($cleaned_str, 0, 2);
        $sequence_number = substr($cleaned_str, 2, 6);
        $security_code = substr($cleaned_str, 8, 1);
        $kpp_code = substr($cleaned_str, 9, 3);
        $wp_status = substr($cleaned_str, 12, 3);

        // Validasi jenis wajib pajak (seharusnya berkisar antara 01-99)
        if (intval($wp_type) < 1 || intval($wp_type) > 99) {
            return false;
        }

        // Validasi nomor urut (tidak boleh nol semua)
        if (intval($sequence_number) === 0) {
            return false;
        }

        // Validasi kode pengaman (seharusnya 0-9)
        if (intval($security_code) < 0 || intval($security_code) > 9) {
            return false;
        }

        // Validasi kode KPP (seharusnya berkisar antara 001-999)
        if (intval($kpp_code) < 1 || intval($kpp_code) > 999) {
            return false;
        }

        // Validasi status wajib pajak (seharusnya berkisar antara 000-999)
        if (intval($wp_status) < 0 || intval($wp_status) > 999) {
            return false;
        }

        return true;
    }

    /*
    * Custom Validation For NIP
    */
    public function is_valid_nip_nrp(string $str): bool
    {
        // **Validasi NIP PNS/PPPK (18 Digit)**
        if (mb_strlen($str) == 18 && is_numeric($str)) {
            $birth_date = $this->get_birth_date($str);
            $appointment_year = intval(substr($str, 8, 4));
            $gender = intval(substr($str, 14, 1)); // 1: male, 2: female

            // Format NIP tidak valid: tanggal lahir tidak sesuai.
            if (!$birth_date || $this->calculate_age($birth_date) < 18) {
                return false;
            }

            // Format NIP tidak valid: tahun pengangkatan tidak sesuai.
            if (!checkdate(1, 1, $appointment_year) || $appointment_year > date('Y')) {
                return false;
            }

            // Format NIP tidak valid: kode gender tidak sesuai.
            if ($gender < 1 || $gender > 2) {
                return false;
            }
        }

        // **Validasi NRP POLRI (8 Digit)**
        elseif (mb_strlen($str) == 8 && is_numeric($str)) {
            $year_prefix = intval(substr($str, 0, 2));
            $year_full = ($year_prefix < 10) ? "20" . $year_prefix : "19" . $year_prefix;
            $month = intval(substr($str, 2, 2));

            // Format NRP POLRI tidak valid: bulan lahir tidak sesuai.
            if ($month < 1 || $month > 12) {
                return false;
            }

            $birth_year_month = $year_full . str_pad($month, 2, '0', STR_PAD_LEFT);
            $age = $this->calculate_age($birth_year_month, 'Yn');

            // Format NRP POLRI tidak valid: rentang usia tidak sesuai.
            if ($age < 18 || $age > 60) {
                return false;
            }
        }

        // **Validasi NRP TNI (6 Digit)**
        elseif (mb_strlen($str) == 6 && is_numeric($str)) {
            // Struktur NRP TNI tidak memiliki format tanggal lahir baku
            // Biasanya digunakan dengan registrasi internal
            return false; // Tidak ada validasi khusus untuk NRP TNI saat ini
        } else {
            return false;
        }

        return true;
    }

    /*
    * Get Birth Date From NIP
    */
    protected function get_birth_date($nip)
    {
        $year = intval(substr($nip, 0, 4));
        $month = intval(substr($nip, 4, 2));
        $day = intval(substr($nip, 6, 2));

        return checkdate($month, $day, $year) ? sprintf('%04d-%02d-%02d', $year, $month, $day) : false;
    }

    /*
    * Count Age By Format
    */
    protected function calculate_age($birth_date, $format = 'Y-m-d')
    {
        $birthDate = DateTime::createFromFormat($format, $birth_date);
        $today = new DateTime();

        return $birthDate ? $today->diff($birthDate)->y : 0;
    }
}
