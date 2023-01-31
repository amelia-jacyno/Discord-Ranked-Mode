<?php

require_once __DIR__ . '/../bootstrap.php';

$backupManager = new App\Service\S3BackupManager();
if ($backupManager->createBackup()) {
	echo 'Backup created successfully.' . PHP_EOL;
} else {
	echo 'Backup failed.' . PHP_EOL;
}