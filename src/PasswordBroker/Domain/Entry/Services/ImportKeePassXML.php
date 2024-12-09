<?php

namespace PasswordBroker\Domain\Entry\Services;

use Carbon\Carbon;
use Illuminate\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Testing\MimeType;
use PasswordBroker\Domain\Entry\Models\Attributes\GroupName;
use PasswordBroker\Domain\Entry\Models\Attributes\Title;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\File;
use PasswordBroker\Domain\Entry\Models\Fields\Link;
use PasswordBroker\Domain\Entry\Models\Fields\Note;
use PasswordBroker\Domain\Entry\Models\Fields\Password;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryGroupValidationHandler;
use PasswordBroker\Infrastructure\Validation\Handlers\EntryValidationHandler;
use RuntimeException;
use SimpleXMLElement;

class ImportKeePassXML implements ShouldQueue
{
    use Dispatchable;

    private array $jobs = [];

    public function __construct(
        protected string              $filePath,
        protected string              $masterPassword,
        protected readonly Dispatcher $dispatcher,
    ) {

    }


    public function handle(): void
    {
        if (!file_exists($this->filePath)) {
            throw new RuntimeException('XML file "' . $this->filePath . '" doesnt exists or cannot be read');
        }

        $keePassFile = simplexml_load_string(file_get_contents($this->filePath));

        if ($keePassFile === false) {
            throw new RuntimeException('XML file "' . $this->filePath . '" contains invalid XML structure');
        }

        $files = [];

        if ($keePassFile->Meta->Binaries
            && $keePassFile->Meta->Binaries->Binary->count()
        ) {
            foreach ($keePassFile->Meta->Binaries->Binary as $binary) {
                $data = (binary) $binary;

                if ($binary['Compressed']) {
                    $data = gzdecode(base64_decode($data));
                }

//                $temp = tmpfile();
//                fwrite($temp, $data);

                $files[(int) $binary['ID']] = $data;
            }
        }

        $data = [];

        foreach ($keePassFile->Root->Group as $group) {
            $data[] = $this->treeHelper($group);
        }
        //$f_path = stream_get_meta_data($tmpHandle)['uri'];

//        dd($data);
        $i = 0;
        $keepassNameBase = 'keepass';
        $keepassName = 'keepass';
        while (true) {
            $exists = EntryGroup::where('name', $keepassName)->whereNull('parent_entry_group_id')->first();
            if ($exists) {
                $keepassName = $keepassNameBase . '_' . ++$i;
                continue;
            }
            break;
        }
        /**
         * @var \Illuminate\Contracts\Bus\Dispatcher $dispatcher
         */
        $dispatcher = app(\Illuminate\Contracts\Bus\Dispatcher::class);

        $keepassGroup = new EntryGroup();
        $keepassGroup->entry_group_id;
        $keepassGroup->name = new GroupName($keepassName);

        $dispatcher->dispatchSync(
            new AddEntryGroup(
                $keepassGroup,
                new EntryGroupValidationHandler()
            )
        );
        foreach ($data as $datum) {
            $this->convertAndSaveTreeToDB($datum, $files, $keepassGroup);
        }

//        Bus::batch($this->jobs)->onConnection('database')->onQueue('importFromKeePass')->dispatch();
//        foreach ($this->jobs as $job) {
//            app(\Illuminate\Contracts\Bus\Dispatcher::class)->dispatchSync($job);
//        }
//        foreach ($files as $file) {
//            fclose($file);
//        }
    }

    private function convertAndSaveTreeToDB(array $data, array $files, EntryGroup $parentEntryGroup): void
    {
        /**
         * @var \Illuminate\Contracts\Bus\Dispatcher $dispatcher
         */
        $dispatcher = app(\Illuminate\Contracts\Bus\Dispatcher::class);


        $entryGroup = new EntryGroup();
        $entryGroup->name = GroupName::fromNative($data['name']);
        $entryGroup->parentEntryGroup()->associate($parentEntryGroup);
        $entryGroup->created_at = $data['created_at'];
        $entryGroup->updated_at = $data['updated_at'];
        /**
         * set fixed UUID
         */
        $entryGroup->entry_group_id;

        $dispatcher->dispatchSync(
            new AddEntryGroup(
                entryGroup                 : $entryGroup,
                entryGroupValidationHandler: new EntryGroupValidationHandler()
            )
        );

        foreach ($data['entries'] as $entryArr) {
            $entry = new Entry();
            $entry->entry_id;
            $entry->title = new Title($entryArr['title']);
            $entry->created_at = $entryArr['created_at'];
            $entry->updated_at = $entryArr['updated_at'];

            $dispatcher->dispatchSync(
                new AddEntry(
                    entry                 : $entry,
                    entryGroup            : $entryGroup,
                    entryValidationHandler: new EntryValidationHandler()
                )
            );
            if ($entryArr['note']) {
                $dispatcher->dispatchSync(
                    new AddFieldToEntry(
                        entry                : $entry,
                        entryGroup           : $entryGroup,
                        type                 : Note::TYPE,
                        title                : '',
                        value_encrypted      : null,
                        initialization_vector: null,
                        value                : base64_encode($entryArr['note']),
                        file                 : null,
                        file_name            : null,
                        file_size            : null,
                        file_mime            : null,
                        login                : null,
                        totp_hash_algorithm  : null,
                        totp_timeout         : null,
                        master_password      : $this->masterPassword
                    )
                );
            }
            if ($entryArr['password']) {
                $dispatcher->dispatchSync(
                    new AddFieldToEntry(
                        entry                : $entry,
                        entryGroup           : $entryGroup,
                        type                 : Password::TYPE,
                        title                : '',
                        value_encrypted      : null,
                        initialization_vector: null,
                        value                : base64_encode($entryArr['password']),
                        file                 : null,
                        file_name            : null,
                        file_size            : null,
                        file_mime            : null,
                        login                : $entryArr['userName'],
                        totp_hash_algorithm  : null,
                        totp_timeout         : null,
                        master_password      : $this->masterPassword
                    )
                );
            }

            if ($entryArr['url']) {
                $dispatcher->dispatchSync(
                    new AddFieldToEntry(
                        entry                : $entry,
                        entryGroup           : $entryGroup,
                        type                 : Link::TYPE,
                        title                : '',
                        value_encrypted      : null,
                        initialization_vector: null,
                        value                : base64_encode($entryArr['url']),
                        file                 : null,
                        file_name            : null,
                        file_size            : null,
                        file_mime            : null,
                        login                : null,
                        totp_hash_algorithm  : null,
                        totp_timeout         : null,
                        master_password      : $this->masterPassword
                    )
                );
            }

            foreach ($entryArr['binaries'] as $binary) {
                $dispatcher->dispatchSync(
                    new AddFieldToEntry(
                        entry                : $entry,
                        entryGroup           : $entryGroup,
                        type                 : File::TYPE,
                        title                : '',
                        value_encrypted      : null,
                        initialization_vector: null,
                        value                : base64_encode($files[$binary['ref']]),
                        file                 : null,
                        file_name            : $binary['name'],
                        file_size            : strlen($files[$binary['ref']]),
                        file_mime            : MimeType::from($binary['name']),
                        login                : null,
                        totp_hash_algorithm  : null,
                        totp_timeout         : null,
                        master_password      : $this->masterPassword
                    )
                );
            }
        }

        foreach ($data['groups'] as $group) {
            $this->convertAndSaveTreeToDB(data: $group, files: $files, parentEntryGroup: $entryGroup);
        }
    }

    private function treeHelper(SimpleXMLElement $group): array
    {
        $data = [
            'name' => (string) $group->Name,
            'created_at' => new Carbon((string) $group->Times->CreationTime),
            'updated_at' => new Carbon((string) $group->Times->LastModificationTime),
            'accessed_at' => new Carbon((string) $group->Times->LastAccessTime),
            'entries' => [],
            'groups' => [],
        ];
        foreach ($group->Entry as $entry) {
            $entryArr = [
                'note' => '',
                'password' => '',
                'title' => '',
                'url' => '',
                'userName' => '',
                'created_at' => new Carbon((string) $entry->Times->CreationTime),
                'updated_at' => new Carbon((string) $entry->Times->LastModificationTime),
                'accessed_at' => new Carbon((string) $entry->Times->LastAccessTime),
                'binaries' => [],
            ];
            foreach ($entry->String as $string) {
                $value = (string) $string->Value;
                if (empty($value)) {
                    continue;
                }
                switch ((string) $string->Key) {
                    default:
                        break;

                    case 'Notes':
                        $entryArr['note'] = $value;
                        break;

                    case 'Password':
                        $entryArr['password'] = $value;
                        break;

                    case 'Title':
                        $entryArr['title'] = $value;
                        break;

                    case 'URL':
                        $entryArr['url'] = $value;
                        break;

                    case 'UserName':
                        $entryArr['userName'] = $value;
                        break;
                }
            }
            if ($entry->Binary) {
                foreach ($entry->Binary as $binary) {
                    if ($binary->Value
                        && $binary->Value['Ref']
                    ) {
                        $entryArr['binaries'][] = [
                            'name' => (string) $binary->Key,
                            'ref' => (int) $binary->Value['Ref'],
                        ];
                    }
                }
            }
            $data['entries'][] = $entryArr;
        }

        if (!$group->Group) {
            return $data;
        }

        foreach ($group->Group as $childGroup) {
            if ((string) $childGroup->Name === 'Recycle Bin') {
                continue;
            }
            $data['groups'][] = $this->treeHelper($childGroup);
        }

        return $data;
    }

}
