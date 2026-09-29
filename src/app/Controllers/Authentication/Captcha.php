<?php

/**
 * Captcha Feature from Authentication Module
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-04-14
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-04-14
 */

namespace App\Controllers\Authentication;

use App\Controllers\KV_Controller;
use CodeIgniter\HTTP\ResponseInterface;

class Captcha extends KV_Controller
{
    public function getIndex(): ResponseInterface
    {
        $phrase = $this->createCaptchaPhrase();

        session()->remove('kv-code');
        session()->set('kv-code', $phrase);

        $imageData = $this->createCaptchaImage($phrase);

        return $this->response
            ->setHeader('Content-Type', 'image/jpeg')
            ->setBody($imageData);
    }

    protected function createCaptchaPhrase(): string
    {
        if (class_exists('Gregwar\\Captcha\\PhraseBuilder')) {
            $builder = new \Gregwar\Captcha\PhraseBuilder(6, '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $phrase = $builder->build(6, '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ');

            if (is_string($phrase) && $phrase !== '') {
                return $phrase;
            }
        }

        $chars = '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $phrase = '';

        for ($i = 0; $i < 6; $i++) {
            $phrase .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $phrase;
    }

    protected function createCaptchaImage(string $phrase): string
    {
        if (class_exists('Gregwar\\Captcha\\CaptchaBuilder')) {
            $builder = new \Gregwar\Captcha\CaptchaBuilder($phrase, new \Gregwar\Captcha\PhraseBuilder(6, '123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'));
            $builder
                ->setBackgroundColor(255, 255, 255)
                ->setTextColor(0, 128, 0)
                ->setMaxBehindLines(1)
                ->setMaxFrontLines(2)
                ->build(200, 50, null);

            $image = $builder->getGd();
            ob_start();
            imagejpeg($image);
            $imageData = ob_get_clean();
            imagedestroy($image);

            return $imageData;
        }

        $width = 200;
        $height = 50;
        $image = imagecreatetruecolor($width, $height);
        $background = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 128, 0);

        imagefill($image, 0, 0, $background);

        for ($i = 0; $i < 4; $i++) {
            $lineColor = imagecolorallocate($image, 200, 200, 200);
            imageline($image, 0, rand(0, $height), $width, rand(0, $height), $lineColor);
        }

        imagestring($image, 5, 20, 15, $phrase, $textColor);

        ob_start();
        imagejpeg($image);
        $imageData = ob_get_clean();
        imagedestroy($image);

        return $imageData;
    }
}
