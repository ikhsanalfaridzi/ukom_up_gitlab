<?php

/**
 * Minio Config for Minio Integration
 *
 * @author 		: Kevin Kurnia Wikarta
 * @copyright 	: Copyright (c) 2025
 * @link 		: https://kevkw.id
 * @version 	: 1.0
 * @created by 	: Kevin Kurnia Wikarta
 * @created 	: 2025-02-26
 * @updated by 	: Kevin Kurnia Wikarta
 * @updated 	: 2025-02-26
 */

namespace App\Libraries\Storage;

use Aws\S3\S3Client;
use Config\Minio;
use Exception;

class MinioLib
{
    protected $s3;
    protected $bucket;

    public function __construct()
    {
        $config = new Minio();

        $this->s3 = new S3Client([
            'version'                 => 'latest',
            'region'                  => 'us-east-1',
            'endpoint'                => $config->endpoint,
            'use_path_style_endpoint' => true,
            'signature_version'       => 'v4',
            // 'debug'       => true,
            'credentials'             => [
                'key'    => $config->access_key,
                'secret' => $config->secret_key,
            ],
        ]);

        $this->bucket = $config->bucket;
    }

    /**
     * Upload file to MinIO
     */
    public function upload_file($file, $filename)
    {
        try {
            $result = $this->s3->putObject([
                'Bucket'     => $this->bucket,
                'Key'        => $filename,
                'Body'       => fopen($file->getTempName(), 'r'),
                // 'SourceFile' => $file->getTempName(),
                'ACL'        => 'private',
                'Metadata'   => [
                    'x-amz-content-sha256' => hash('sha256', $file->getTempName()), // Tambahkan signature hash
                    'x-amz-date' => gmdate('Ymd\THis\Z'), // Timestamp sesuai AWS format
                ],
            ]);

            return $result['ObjectURL'];
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die();
            // log_message('error', 'Error uploading file to Minio: ' . $e->getMessage());
            return $e->getMessage();
        }
    }

    /**
     * Generate URL file from MinIO
     */
    // public function get_file_url($filename)
    // {
    //     return $this->s3->getObjectUrl($this->bucket, $filename);
    // }
    public function get_signed_url($filename, $expiration = '+60 minutes')
    {
        try {
            $cmd = $this->s3->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key'    => $filename,
            ]);

            $request = $this->s3->createPresignedRequest($cmd, $expiration);
            return (string) $request->getUri();
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Delete file from MinIO
     */
    public function delete_file($filename)
    {
        try {
            $this->s3->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $filename,
            ]);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
