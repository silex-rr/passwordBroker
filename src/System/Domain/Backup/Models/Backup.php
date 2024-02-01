<?php

namespace System\Domain\Backup\Models;

use App\Common\Domain\Traits\HasFactoryDomain;
use App\Common\Domain\Traits\ModelDomainConstructor;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use System\Domain\Backup\Models\Casts\BackupCreated;
use System\Domain\Backup\Models\Casts\BackupDeleted;
use System\Domain\Backup\Models\Casts\BackupId;
use System\Domain\Backup\Models\Casts\BackupPassword;
use System\Domain\Backup\Models\Casts\BackupState;
use System\Domain\Backup\Models\Casts\ErrorMessage;
use System\Domain\Backup\Models\Casts\FileName;
use System\Domain\Backup\Models\Casts\Size;
use System\Infrastructure\Factories\Backup\BackupFactory;

/**
 * @property Attributes\BackupId $backup_id
 * @property Attributes\FileName $file_name
 * @property Attributes\Size $size
 * @property Attributes\BackupState $state
 * @property Attributes\BackupCreated $backup_created
 * @property Attributes\BackupDeleted $backup_deleted
 * @property Attributes\ErrorMessage $error_message
 * @property Attributes\BackupPassword $password
 *
 * @method static BackupFactory factory
 */
class Backup extends Model
{
    use ModelDomainConstructor;
    use HasUuids;
    use HasFactoryDomain;

    public $keyType = 'string';
    public $primaryKey = 'backup_id';

    public $incrementing = false;

    public $fillable = [
        'file_name',
        'size',
    ];

    protected $casts = [
        'backup_id' => BackupId::class,
        'file_name' => FileName::class,
        'state' => BackupState::class,
        'size' => Size::class,
        'backup_created' => BackupCreated::class,
        'backup_deleted' => BackupDeleted::class,
        'error_message' => ErrorMessage::class,
        'password' => BackupPassword::class,
    ];

}
