<?php

namespace App\Service;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Carbon\Carbon;

final class S3BackupManager
{
    private S3Client $client;

    private string $bucket;

    public function __construct()
    {
        $this->client = new S3Client([
            'region' => $_ENV['DB_BACKUP_AWS_S3_REGION'],
            'version' => $_ENV['DB_BACKUP_AWS_S3_VERSION'],
			'credentials' => [
				'key'    => $_ENV['AWS_ACCESS_KEY_ID'],
				'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
			],
        ]);
        $this->bucket = $_ENV['DB_BACKUP_AWS_S3_BUCKET'];
    }

    public function createBackup(): bool
    {
        $objectKey = sprintf('db-%s.sqlite', Carbon::now()->format('Y-m-d'));
        $objectBody = file_get_contents(__ROOT__ . '/' . $_ENV['DB_PATH']);
        try {
            if ($this->client->doesObjectExist($this->bucket, $objectKey)) {
                return true;
            }

            $this->client->upload($this->bucket, $objectKey, $objectBody);
        } catch (S3Exception) {
            return false;
        }

        return true;
    }
}
