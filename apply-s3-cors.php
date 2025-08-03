<?php

require_once 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// S3 Client configuration
$s3Client = new S3Client([
    'version' => 'latest',
    'region'  => $_ENV['AWS_DEFAULT_REGION'],
    'endpoint' => $_ENV['AWS_ENDPOINT'],
    'use_path_style_endpoint' => $_ENV['AWS_USE_PATH_STYLE_ENDPOINT'] ?? false,
    'credentials' => [
        'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
        'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
    ],
]);

// CORS configuration
$corsConfig = [
    'CORSRules' => [
        [
            'AllowedHeaders' => ['*'],
            'AllowedMethods' => ['GET', 'HEAD', 'POST', 'PUT', 'DELETE'],
            'AllowedOrigins' => [
                'http://127.0.0.1:8001',
                'http://localhost:8001',
                'http://127.0.0.1:8000',
                'http://localhost:8000',
                'http://127.0.0.1:3000',
                'http://localhost:3000'
            ],
            'ExposeHeaders' => ['ETag', 'Content-Length', 'Content-Type'],
            'MaxAgeSeconds' => 3000
        ]
    ]
];

try {
    $result = $s3Client->putBucketCors([
        'Bucket' => $_ENV['AWS_BUCKET'],
        'CORSConfiguration' => $corsConfig
    ]);

    echo "CORS configuration applied successfully!\n";
    echo "Bucket: " . $_ENV['AWS_BUCKET'] . "\n";

} catch (AwsException $e) {
    echo "Error applying CORS configuration: " . $e->getMessage() . "\n";
}
