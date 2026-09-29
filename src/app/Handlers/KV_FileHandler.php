<?php

/**
 * Override File Logging Codeigniter 4
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-02
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-05-14
 */

namespace App\Handlers;

use CodeIgniter\Log\Handlers\BaseHandler;
use DateTime;
use Exception;

class KV_FileHandler extends BaseHandler //  __TOP_CLASS__
{
    protected $path;
    protected $fileExtension;
    protected $filePermissions;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->path = empty($config['path']) ? WRITEPATH . 'logs/' : $config['path'];

        $this->fileExtension = empty($config['fileExtension']) ? 'log' : $config['fileExtension'];
        $this->fileExtension = ltrim($this->fileExtension, '.');

        $this->filePermissions = $config['filePermissions'] ?? 0644;
    }
    public function handle($level, $message): bool
{
    $filepath = $this->path . 'log-' . date('Y-m-d') . '.' . $this->fileExtension;

    $msg = '';

    if (! is_file($filepath)) {
        $newfile = true;

        if ($this->fileExtension === 'php') {
            $msg .= "<?php defined('SYSTEMPATH') || exit('No direct script access allowed'); ?>\n\n";
        }
    }

    if (! $fp = @fopen($filepath, 'ab')) {
        return false;
    }

    if (strpos($this->dateFormat, 'u') !== false) {
        $microtimeFull  = microtime(true);
        $microtimeShort = sprintf('%06d', ($microtimeFull - floor($microtimeFull)) * 1_000_000);
        $date           = new DateTime(date('Y-m-d H:i:s.' . $microtimeShort, (int) $microtimeFull));
        $date           = $date->format($this->dateFormat);
    } else {
        $date = date($this->dateFormat);
    }

    // Get User Data From Session — DIBUAT DEFENSIF
    $username = 'guest';
    $user_id  = '0';

    try {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $login_username = session()->get('login_username');
            $username       = !empty($login_username) ? kv_decrypt($login_username, getenv('APP_KEY')) : 'guest';
            $user_id        = session()->get('login_user_id') ?? '0';
        }
    } catch (\Throwable $e) {
        // Jangan biarin kegagalan session bikin logging ikutan gagal
    }

    $ip_address = \Config\Services::request()->getIPAddress();

    $msg .= strtoupper($level) . ' - ' . $date . ' --> [' . $user_id . ':' . $username . ':' . $ip_address . '] ' . $message . "\n";

    flock($fp, LOCK_EX);

    $result = null;
    for ($written = 0, $length = strlen($msg); $written < $length; $written += $result) {
        if (($result = fwrite($fp, substr($msg, $written))) === false) {
            break;
        }
    }

    flock($fp, LOCK_UN);
    fclose($fp);

    if (isset($newfile) && $newfile === true) {
        chmod($filepath, $this->filePermissions);
    }

    return is_int($result);
}
}
