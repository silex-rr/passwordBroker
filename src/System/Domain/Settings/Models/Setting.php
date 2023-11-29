<?php

namespace System\Domain\Settings\Models;

use App\Common\Domain\Contracts\EnumDefaultValue;
use App\Common\Domain\Traits\ModelDomainConstructor;
use App\Models\Abstracts\AbstractValue;
use BackedEnum;
use Identity\Domain\User\Models\Attributes\UserId;
use Identity\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JsonException;
use PasswordBroker\Domain\Entry\Models\Fields\Casts\UpdatedBy;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use System\Domain\Settings\Models\Casts\Data;
use System\Domain\Settings\Models\Casts\SettingId;

/**
 * @property Attributes\SettingId $setting_id
 * @property Attributes\Data $data
 * @property UserId $updated_by
 */
abstract class Setting extends Model
{
    use ModelDomainConstructor;
    use HasUuids;

    public const TYPE = '';
    protected static array $related = [
        BooleanSetting::TYPE => BooleanSetting::class,
        StringSetting::TYPE => StringSetting::class,
        IntSetting::TYPE => IntSetting::class,

        BackupSetting::TYPE => BackupSetting::class,
    ];

    public $table = 'settings';
    public $incrementing = false;
    public $keyType = 'string';
    protected $primaryKey = 'setting_id';

    public $fillable = [
        'setting_id',
        'key',
        'data',
        'updated_by'
    ];

    public $guarded = [
        'type'
    ];

    public $casts = [
        'setting_id' => SettingId::class,
        'data' => Data::class,
        'updated_by' => UpdatedBy::class,
    ];

    public $dispatchesEvents = [];

    public function getType(): string
    {
        return static::TYPE;
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    /**
     * Call in SettingObserver
     * @return void
     */
    public function unpackData(): void
    {
        $data = $this->data->getData();
        $refProps = $this->getRefProps();
        foreach ($refProps as $prop) {
            if (!array_key_exists($prop->getName(), $data)) {
                continue;
            }
            $value = $data[$prop->getName()];
            $prpType = $prop->getType()?->getName();
            switch ($prpType) {
                default:
                    if (is_subclass_of($prpType, AbstractValue::class)) {
                        $value = new $prpType($value);
                        break;
                    }
                    if (is_subclass_of($prpType, BackedEnum::class)) {
                        $value = $prpType::from($value);
                        break;
                    }

                    continue 2;

                case 'int':
                    if (!is_int($value)) {
                        continue 2;
                    }
                    break;
                case 'string':
                    if (!is_string($value)) {
                        continue 2;
                    }
                    break;
                case 'bool':
                    if (!is_bool($value)) {
                        continue 2;
                    }
                    break;
            }
            $this->{$prop->getName()} = $value;
        }
    }

    /**
     * Call in SettingObserver
     * @return void
     */
    public function packData(): void
    {
        $refProps = $this->getRefProps();
        $props = [];
        foreach ($refProps as $prop) {
            $value = $this->{$prop->getName()};
            $prpType = $prop->getType()?->getName();
//            if(is_subclass_of($prpType, AbstractValue::class)){
//                var_dump([
//                    $prop->getName(),
//                    $value,
//                    $prpType,
//                    $this->schedule
//                ]);
//            }
            if (is_null($value)) {
                if (is_subclass_of($prpType, AbstractValue::class)) {
                    $value = new $prpType();
                }
                if (is_subclass_of($prpType, EnumDefaultValue::class)) {
                    $value = $prpType::default();
                }
            }


            $props[$prop->getName()] = $value;
        }

        try {
            $json = json_encode($props, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Error during packing Setting Data in BooleanSetting: ' . $e->getMessage());
        }
        $this->data = new Attributes\Data($json);
    }

    /**
     * @return ReflectionProperty[]
     */
    protected function getRefProps(): array
    {
        $reflectionProperties = (new ReflectionClass(static::class))->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($reflectionProperties as $id => $prop) {
            if (static::class !== $prop->getDeclaringClass()->name
                || $prop->getName() === 'attributes'
            ) {
                unset($reflectionProperties[$id]);
            }
        }
        return $reflectionProperties;
    }


    public function newQuery($excludeDeleted = true): Builder
    {
        return parent::newQuery($excludeDeleted)->where('type', '=', $this->getType());
    }

    public static function getRelated(): array
    {
        return self::$related;
    }

//    protected function getAttributesForInsert()
//    {
//        $attributes = $this->getAttributes();
//        foreach ($this->getRefProps() as $prop) {
//            if (array_key_exists($prop->getName(), $attributes)) {
//                unset($attributes[$prop->getName()]);
//            }
//        }
//        return $attributes;
//    }
}
