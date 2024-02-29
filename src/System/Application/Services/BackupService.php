<?php

namespace System\Application\Services;

use Carbon\Carbon;
use Doctrine\DBAL\Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use PDO;
use RuntimeException;
use System\Domain\Backup\Models\Attributes\BackupCreated;
use System\Domain\Backup\Models\Attributes\BackupPassword;
use System\Domain\Backup\Models\Attributes\FileName;
use System\Domain\Backup\Models\Attributes\Size;
use System\Domain\Backup\Models\Backup;
use System\Domain\Settings\Models\BackupSetting;
use ZipArchive;

class BackupService
{
    public const string BACKUP_TEMPORARY_LOCATION = 'backup-temp';

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
        $tempDirForBackupRecovery = $this->getTempDirForBackup() . DIRECTORY_SEPARATOR .  $recoveryDirRelativeName;

        $extractTo = $zipArchive->extractTo($tempDirForBackupRecovery);
        $zipArchive->close();

        if (!$extractTo) {
            Storage::disk('local')->deleteDirectory($recoveryDirRelativePath);
            throw new RuntimeException("Unable to extract files from ZIP archive");
        }
        $this->recoveryStorageFromBackup($recoveryDirRelativePath, 'identity_keys');
        $this->recoveryStorageFromBackup($recoveryDirRelativePath, 'cbc_salt');
        Storage::disk('local')->deleteDirectory($recoveryDirRelativePath);
    }

    private function recoveryStorageFromBackup(string $recoveryDirRelativePath, string $storage_name): void
    {
        $targetStorage = Storage::disk($storage_name);
        $path = $recoveryDirRelativePath . DIRECTORY_SEPARATOR . $storage_name;
        $localStorage = Storage::disk('local');
        foreach ($localStorage->allFiles($path . DIRECTORY_SEPARATOR) as $file) {
//            dump( $file);
            $resource = $localStorage->readStream($file);
//            $targetStorage->writeStream($file, $resource);
            fclose($resource);
        }
    }

    /**
     * @param Backup|null $backup
     * @param string|null $password
     * @return Backup
     */
    public function makeBackup(?Backup $backup = null, ?string $password = null): Backup
    {
        if (is_null($backup)) {
            $backup = new Backup();
        }
        /**
         * @var BackupSetting $backupSetting
         */
        $backupSetting = BackupSetting::firstOrCreate([
            'key' => BackupSetting::TYPE,
            'type' => BackupSetting::TYPE,
        ]);
        if (is_null($password)) {
            $password = $backupSetting->getArchivePassword()->getValue();
        }

        $zipArchiveFilePath = $this->getTempDirForBackup() . DIRECTORY_SEPARATOR . 'pb_backup_' . time() . '.zip';
        $zipArchive = new ZipArchive();
        $zipArchive->open($zipArchiveFilePath, ZipArchive::CREATE);

        $fileName = new FileName('pb_backup_' . (Carbon::now())->format('Y_m_d__H_i_s') . '.zip');
        $backup->file_name = $fileName;

        $backup->password = BackupPassword::fromNative($password);

        $fileDatabasePath = $this->addDatabaseToZipArchive(zipArchive: $zipArchive, password: $password);
        $this->addFilesFromStoreToZipArchive(
            zipArchive: $zipArchive,
            filesystem: Storage::disk('cbc_salt'),
            pathInArchive: 'cbc_salt/',
            password: $password,
        );
        $this->addFilesFromStoreToZipArchive(
            zipArchive: $zipArchive,
            filesystem: Storage::disk('identity_keys'),
            pathInArchive: 'identity_keys/',
            password: $password,
        );

        $this->addVersionToZipArchive(zipArchive: $zipArchive, password: $password);
        $this->addEnvToZipArchive(zipArchive: $zipArchive, password: $password);

        $zipArchive->setPassword($password);
        $zipArchive->close();

        unlink($fileDatabasePath);

        $backup->size = new Size(filesize($zipArchiveFilePath));
        Storage::disk('system_backup')->put($fileName, fopen($zipArchiveFilePath, "rb"));
        unlink($zipArchiveFilePath);

        return $backup;
    }

    /**
     * @param ZipArchive $zipArchive
     * @param string|null $password
     * @return void
     */
    private function addVersionToZipArchive(ZipArchive $zipArchive, ?string $password): void
    {
        $version = config('app_version.version', '0.0.1');
        $file = 'app_version.txt';
        $zipArchive->addFromString($file, $version);
        if ($password) {
            $zipArchive->setEncryptionName($file, ZipArchive::EM_AES_256, $password);
        }
    }

    /**
     * @param ZipArchive $zipArchive
     * @param string|null $password
     * @return void
     */
    private function addEnvToZipArchive(ZipArchive $zipArchive, ?string $password): void
    {
        base_path();
        foreach (scandir(base_path()) as $file) {
            if (preg_match('/^\.env/', $file)) {
                $zipArchive->addFile(base_path() . '/' . $file, $file);
                if ($password) {
                    $zipArchive->setEncryptionName($file, ZipArchive::EM_AES_256, $password);
                }
            }
        }
    }

    /**
     * @param ZipArchive $zipArchive
     * @return string database file path
     */
    private function addDatabaseToZipArchive(ZipArchive $zipArchive, ?string $password): string
    {
        $fileDatabasePath = $this->getTempDirForBackup() . DIRECTORY_SEPARATOR . 'pb_backup_database_' . time() . '.sql';

        $fileDatabaseResource = fopen($fileDatabasePath, 'wb+');
        $databaseName = $this->makeDatabaseBackup($fileDatabaseResource);
        $databaseNameInArchive = 'database_' . $databaseName . '.sql';
        $zipArchive->addFile($fileDatabasePath, $databaseNameInArchive);
        if ($password) {
            $zipArchive->setEncryptionName($databaseNameInArchive, ZipArchive::EM_AES_256, $password);
        }
        fclose($fileDatabaseResource);
        unset($fileDatabaseResource);
        return $fileDatabasePath;
    }

    /**
     * @param ZipArchive $zipArchive
     * @param Filesystem $filesystem
     * @param string $pathInArchive
     * @param string|null $password
     * @return void
     */
    private function addFilesFromStoreToZipArchive(
        ZipArchive $zipArchive,
        Filesystem $filesystem,
        string $pathInArchive = '',
        ?string $password = null,
    ): void
    {
        foreach ($filesystem->allFiles('/') as $file){
            $full_path = $pathInArchive . $file;
            $zipArchive->addFromString($full_path, $filesystem->get($file));
            if ($password) {
                $zipArchive->setEncryptionName($full_path, ZipArchive::EM_AES_256, $password);
            }
        }
    }

    /**
     * @param resource $fileResource
     * @return string database name
     * @throws Exception
     */
    public function makeDatabaseBackup($fileResource): string
    {
        $defaultConnection = DB::getDefaultConnection();
        $abstractSchemaManager = DB::connection(DB::getDefaultConnection())->getDoctrineSchemaManager();
        $tables = $abstractSchemaManager->listTableNames(); //tables

        switch ($defaultConnection) {
            case 'mysql':
                $this->makeDatabaseBackupMySql($fileResource, $tables);
                return $defaultConnection;
            case 'sqlite':
                $this->makeDatabaseBackupSQLite($fileResource, $tables);
                return $defaultConnection;
            default:
                throw new RuntimeException("Backup for " . $defaultConnection . " is not supported");
        }
    }

    private function makeDatabaseBackupSQLite($fileResource, array $tables): void
    {
        foreach ($tables as $table) {
            $PDO = DB::getPdo();
            $tableInfo = $PDO->query(sprintf("PRAGMA table_info(%s)", $table));
            fwrite($fileResource, sprintf("\n\n-- Table structure for table `%s`\n\n", $table));
            fwrite($fileResource, sprintf("DROP TABLE IF EXISTS `%s`;\n", $table));
            fwrite($fileResource, sprintf("CREATE TABLE %s (\r\n", $table));
            $tableSignature = [];
            while ($column = $tableInfo ->fetch(PDO::FETCH_ASSOC)) {
                $columnName = $column['name'];
                $dataType = $column['type'];
                $notNull = $column['notnull'] ? ' NOT NULL' : '';
                $defaultValue = isset($column['dflt_value']) ?
                    sprintf(" DEFAULT '%s'", trim($column['dflt_value'], "'\""))
                    : '';
                $tableSignature[] = sprintf("%s %s%s%s", $columnName, $dataType, $notNull, $defaultValue);
            }

            $tableStructure = implode(", \n", $tableSignature) . "\n);\n";
            fwrite($fileResource, $tableStructure);
            fwrite($fileResource, sprintf("-- Inserting data for table `%s`\n", $table));
            $dataQuery = $PDO->query(sprintf("SELECT * FROM `%s`", $table));
            $blobFields = $this->getBlobFields($tableStructure);
            while ($rowArr = $dataQuery->fetch(PDO::FETCH_ASSOC)) {
                $row = [];
                foreach ($rowArr as $column => $value) {
                    $row[] = $this->sqlValueHelper($column, $value, $blobFields);
                }
                fwrite($fileResource,
                    sprintf("INSERT INTO `%s` VALUES (%s);\n", $table, implode(', ', $row))
                );
            }
        }
    }
    private function makeDatabaseBackupMySql($fileResource, array $tables): void
    {
        fwrite($fileResource,"SET FOREIGN_KEY_CHECKS=0;\n\n");
        foreach ($tables as $table) {
            $tableStructure = DB::select(sprintf("SHOW CREATE TABLE %s", $table))[0]->{'Create Table'};

            fwrite($fileResource, sprintf("\n\n-- Table structure for table `%s`\n\n", $table));
            fwrite($fileResource, sprintf("DROP TABLE IF EXISTS `%s`;\n", $table));
            fwrite($fileResource, $tableStructure . ";\n\n");

            $tableData = DB::table($table)->get();
            fwrite($fileResource, sprintf("-- Inserting data for table `%s`\n", $table));

            $blobFields = $this->getBlobFields($tableStructure);

            foreach ($tableData->all() as $rowObj) {
                $row = [];
                foreach ($rowObj as $column => $value) {
                    $row[] = $this->sqlValueHelper($column, $value, $blobFields);
                }
//                if ($table === 'password_broker_entry_fields') {
//                    dd([
//                        $row,
//                        $rowObj,
//                        $blobFields
//                    ]);
//                }
                fwrite($fileResource,
                    sprintf("INSERT INTO `%s` VALUES (%s);\n", $table, implode(', ', $row))
                );
            }
        }
        fwrite($fileResource,"\n\nSET FOREIGN_KEY_CHECKS=1;");
    }

    /**
     * @param $tableStructure
     * @return mixed
     */
    public function getBlobFields($tableStructure): array
    {
        $blobFields = [];
        if (preg_match_all('/`(.*)`\s[a-z]*blob/', $tableStructure, $matches)) {
            $blobFields = $matches[1];
        }
        return $blobFields;
    }

    private function sqlValueHelper($field, $value, array $blobFields): mixed
    {
        if (is_numeric($value)) {
            return $value;
        }
        if (is_null($value)) {
            return 'NULL';
        }
        if (in_array($field, $blobFields, true)) {
            return "UNHEX('" . bin2hex($value) . "')";
        }
        return "'" . addslashes($value) . "'";
    }

    /**
     * @return string
     */
    public function getTempDirForBackup(): string
    {
        return config('filesystems.disks.local.root') . DIRECTORY_SEPARATOR . self::BACKUP_TEMPORARY_LOCATION;
    }

}
