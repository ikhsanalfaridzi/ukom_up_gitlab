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

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Minio extends BaseConfig
{
    /**
     * MinIO Endpoint
     * The URL or address of the MinIO server where files are stored.
     */
    public string $endpoint;

    /**
     * MinIO Access Key
     * Credentials used for authentication with MinIO.
     */
    public string $access_key;

    /**
     * MinIO Secret Key
     * The secret key for authentication with MinIO.
     */
    public string $secret_key;

    /**
     * MinIO Region
     * The storage region for MinIO (default: us-east-1).
     */
    public string $region;

    /**
     * MinIO API Version
     * The API version of MinIO to use (default: latest).
     */
    public string $version;

    /**
     * MinIO Bucket Name
     * The name of the MinIO bucket where files will be stored.
     */
    public string $bucket;

    /**
     * Use Path-Style Endpoint
     * Determines whether MinIO uses a path-style endpoint.
     * - true  → Uses path-style (e.g., http://localhost:9000/mybucket)
     * - false → Uses virtual-host-style (e.g., http://mybucket.localhost:9000)
     */
    public bool $use_path_style_endpoint;

    /**
     * Constructor to load configurations from the .env file.
     */
    public function __construct()
    {
        $this->endpoint = getenv('MINIO_ENDPOINT') ?: 'http://localhost:9000';
        $this->access_key = getenv('MINIO_ACCESS_KEY') ?: 'your-access-key';
        $this->secret_key = getenv('MINIO_SECRET_KEY') ?: 'your-secret-key';
        $this->region = getenv('MINIO_REGION') ?: 'us-east-1';
        $this->version = getenv('MINIO_VERSION') ?: 'latest';
        $this->bucket = getenv('MINIO_BUCKET') ?: 'your-bucket-name';
        $this->use_path_style_endpoint = getenv('USE_PATH_STYLE_ENDPOINT') !== false;
    }
}
