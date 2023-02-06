<?php

use App\Service\S3BackupManager;

require_once __DIR__ . '/../bootstrap.php';

$backupManager = new S3BackupManager();
echo match ($backupManager->createBackup()) {
	S3BackupManager::BACKUP_EXISTS => 'Backup already exists.' . PHP_EOL,
	S3BackupManager::BACKUP_CREATED => 'Backup created successfully.' . PHP_EOL,
	default => 'Backup failed.' . PHP_EOL
};
