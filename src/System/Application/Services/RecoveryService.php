<?php

namespace System\Application\Services;

use Dotenv\Dotenv;
use Dotenv\Repository\RepositoryBuilder;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

class RecoveryService
{
    use BackupTemporaryDir;

    private array $protectedEnv = [
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
    ];

    private readonly Filesystem $localStorage;

    public function __construct()
    {
        $this->localStorage = Storage::disk('local');
    }

    /**
     * @param string $filePath
     * @param string|null $password
     * @return void
     */
    public function recoveryFromBackupFile(string $filePath, ?string $password = null): void
    {
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException('File does not exist filePath [' . $filePath . ']');
        }
        $zipArchive = new ZipArchive();
        $open = $zipArchive->open($filePath);
        if ($open !== true) {
            throw new InvalidArgumentException('Unable tp open ZIP archive from a file [' . $filePath . '] status: ' . $open);
        }

        if (!is_null($password)) {
            $zipArchive->setPassword($password);
        }

        $recoveryDirRelativeName = 'pb_recovery_' . time();
        $recoveryDirRelativePath = self::BACKUP_TEMPORARY_LOCATION . DIRECTORY_SEPARATOR . $recoveryDirRelativeName;
        $tempDirForBackupRecovery = $this->getTempDirForBackup() . DIRECTORY_SEPARATOR . $recoveryDirRelativeName;

        $extractTo = $zipArchive->extractTo($tempDirForBackupRecovery);
        $zipArchive->close();

        if (!$extractTo) {
            $this->localStorage->deleteDirectory($recoveryDirRelativePath);
            throw new RuntimeException("Unable to extract files from ZIP archive");
        }
        $this->validateBackupBeforeRecovery($recoveryDirRelativePath);


        $this->recoveryStorageFromBackup($recoveryDirRelativePath, 'identity_keys');
        $this->recoveryStorageFromBackup($recoveryDirRelativePath, 'cbc_salt');
        $this->recoveryDatabaseFromBackup($recoveryDirRelativePath);
        $this->recoveryEnvFromBackup($recoveryDirRelativePath);
        $this->localStorage->deleteDirectory($recoveryDirRelativePath);
        Artisan::call('migrate');
    }

    private function validateBackupBeforeRecovery(string $recoveryDirRelativePath): void
    {
        $this->recoveryStorageFromBackup($recoveryDirRelativePath, 'identity_keys', true);
        $this->recoveryStorageFromBackup($recoveryDirRelativePath, 'cbc_salt', true);
        $this->recoveryDatabaseFromBackup($recoveryDirRelativePath, true);
        $this->recoveryEnvFromBackup($recoveryDirRelativePath, true);
    }

    private function recoveryStorageFromBackup(string $recoveryDirRelativePath, string $storage_name, bool $no_data_persist = false): void
    {
        $path = $recoveryDirRelativePath . DIRECTORY_SEPARATOR . $storage_name;
        if (!$this->localStorage->exists($path)) {
            throw new RuntimeException($storage_name . " does not exists in Backup FIle");
        }
        if ($no_data_persist) {
            return;
        }
        $targetStorage = Storage::disk($storage_name);
        foreach ($this->localStorage->allFiles($path) as $file) {
            $resource = $this->localStorage->readStream($file);
            $targetStorage->writeStream($file, $resource);
            fclose($resource);
        }
    }

    private function recoveryDatabaseFromBackup(string $recoveryDirRelativePath, bool $no_data_persist = false): void
    {
        $name = DB::getName();
        if ($name === 'mysql') {
            $dumpFileName = "database_" . $name . ".sql";
            $dumpString = $this->localStorage->get($recoveryDirRelativePath . DIRECTORY_SEPARATOR . $dumpFileName);
            if (!$dumpString) {
                throw new RuntimeException("Database dump file does not exists in backup: " . $dumpFileName);
            }
            if ($no_data_persist) {
                return;
            }
            DB::unprepared($dumpString);
            return;
        }
        throw new RuntimeException("Unsupported database type: " . $name);
    }

    private function recoveryEnvFromBackup(string $recoveryDirRelativePath, bool $no_data_persist = false): void
    {
        $path = $recoveryDirRelativePath . DIRECTORY_SEPARATOR . '.env';
        $envLocalFileFullPath = app()->environmentFilePath();
        if (empty($envLocalFileFullPath)) {
            $envLocalFileFullPath = base_path('.env');
        }
        if (!$this->localStorage->exists($path)) {
            throw new RuntimeException(".env file does not exists in the Backup");
        }
        if ($no_data_persist) {
            return;
        }
        $localEnv = Dotenv::create(
            RepositoryBuilder::createWithDefaultAdapters()->make(),
            base_path(),
            ['.env']
        );

        $parsedFromLocal = $localEnv->load();
        $stringFromBackup = $this->localStorage->get($path);
        foreach ($this->protectedEnv as $key) {
            if (empty($parsedFromLocal[$key])) {
                continue;
            }
            $stringFromBackup = preg_replace('/' . $key . '=(?>.)*$/m', $key . '=' . $parsedFromLocal[$key], $stringFromBackup);
        }
        copy($envLocalFileFullPath, $envLocalFileFullPath . '.backup');

        file_put_contents($envLocalFileFullPath, $stringFromBackup);
    }

}
