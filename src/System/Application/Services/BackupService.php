<?php

namespace System\Application\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PDO;
use RuntimeException;
use System\Domain\Backup\Models\Attributes\FileName;
use System\Domain\Backup\Models\Attributes\Size;
use System\Domain\Backup\Models\Backup;
use ZipArchive;

class BackupService
{
    public function makeBackup(?Backup $backup = null): Backup
    {
        if (is_null($backup)) {
            $backup = new Backup();
        }
        $zipArchiveFilePath = $this->getTempDirForBackup() . 'pb_backup_' . time() . '.zip';
        $zipArchive = new ZipArchive();
        $zipArchive->open($zipArchiveFilePath, ZipArchive::CREATE);

        $fileName = new FileName('pb_backup_' . (Carbon::now())->format('Y_m_d__H_i_s') . '.zip');
        $backup->file_name = $fileName;

        $fileDatabasePath = $this->addDatabaseToZipArchive($zipArchive);
        $this->addFilesFromStoreToZipArchive($zipArchive, Storage::disk('cbc_salt'), 'cbc_salt/');
        $this->addFilesFromStoreToZipArchive($zipArchive, Storage::disk('identity_keys'), 'identity_keys/');
        $this->addEnv($zipArchive);

        $zipArchive->close();

        unlink($fileDatabasePath);

        $backup->size = new Size(filesize($zipArchiveFilePath));
        Storage::disk('system_backup')->put($fileName, fopen($zipArchiveFilePath, "rb"));
        unlink($zipArchiveFilePath);

        return $backup;
    }

    private function addEnv(ZipArchive $zipArchive): void
    {
        base_path();
        foreach (scandir(base_path()) as $file) {
            if (preg_match('/^\.env/', $file)) {
                $zipArchive->addFile(base_path() . '/' . $file, $file);
            }
        }
    }

    /**
     * @param ZipArchive $zipArchive
     * @return string database file path
     */
    private function addDatabaseToZipArchive(ZipArchive $zipArchive): string
    {
        $fileDatabasePath = $this->getTempDirForBackup() . 'pb_backup_database_' . time() . '.sql';

        $fileDatabaseResource = fopen($fileDatabasePath, 'wb+');
        $databaseName = $this->makeDatabaseBackup($fileDatabaseResource);
        $zipArchive->addFile($fileDatabasePath, 'database_' . $databaseName . '.sql');
        fclose($fileDatabaseResource);
        return $fileDatabasePath;
    }

    private function addFilesFromStoreToZipArchive(ZipArchive $zipArchive, Filesystem $filesystem, string $pathInArchive = ''): void
    {
        foreach ($filesystem->allFiles('/') as $file){
            $full_path = $pathInArchive . $file;
            $zipArchive->addFromString($full_path, $filesystem->get($file));
        }
    }

    /**
     * @param resource $fileResource
     * @return string database name
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

            fwrite($fileResource, implode(", \n", $tableSignature) . "\n);\n");
            fwrite($fileResource, sprintf("-- Inserting data for table `%s`\n", $table));
            $dataQuery = $PDO->query(sprintf("SELECT * FROM `%s`", $table));
            while ($row = $dataQuery->fetch(PDO::FETCH_ASSOC)) {
                $row = array_map(static fn ($value) => is_numeric($value) ? $value : "'" . addslashes($value) . "'", $row);
                fwrite($fileResource,
                    sprintf("INSERT INTO `%s` VALUES (%s);\n", $table, implode(', ', $row))
                );
            }
        }
    }
    private function makeDatabaseBackupMySql($fileResource, array $tables): void
    {
        foreach ($tables as $table) {
            $tableStructure = DB::select(sprintf("SHOW CREATE TABLE %s", $table))[0]->{'Create Table'};

            fwrite($fileResource, sprintf("\n\n-- Table structure for table `%s`\n\n", $table));
            fwrite($fileResource, $tableStructure . ";\n\n");

            $tableData = DB::table($table)->get()->toArray();
            fwrite($fileResource, sprintf("-- Inserting data for table `%s`\n", $table));
            foreach ($tableData as $row) {
                $row = array_map(static fn ($value) => is_numeric($value) ? $value : "'" . addslashes($value) . "'", $row);
                fwrite($fileResource,
                    sprintf("INSERT INTO `%s` VALUES (%s);\n", $table, implode(', ', $row))
                );
            }
        }
    }

    /**
     * @return string
     */
    public function getTempDirForBackup(): string
    {
        return storage_path() . DIRECTORY_SEPARATOR;
    }

}
