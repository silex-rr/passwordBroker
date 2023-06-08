<?php

namespace PasswordBroker\Application;

use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use PasswordBroker\Domain\Entry\Models\Fields\File;
use PasswordBroker\Domain\Entry\Models\Fields\Link;
use PasswordBroker\Domain\Entry\Models\Fields\Note;
use PasswordBroker\Domain\Entry\Models\Fields\Password;
use PasswordBroker\Domain\Entry\Services\ImportKeePassXML;
use Tests\Feature\PasswordBroker\Application\EntryGroupRandomAttributes;
use Tests\TestCase;

class ImportTest extends TestCase
{

    private string $keepass_xml = __DIR__ . DIRECTORY_SEPARATOR
        . '..'  . DIRECTORY_SEPARATOR
        . '..'  . DIRECTORY_SEPARATOR
        . '..' . DIRECTORY_SEPARATOR
        . 'TestData' . DIRECTORY_SEPARATOR
        . 'DatabaseTestKeepassXML_2_x.xml';

    use RefreshDatabase;
    use WithFaker;
    use EntryGroupRandomAttributes;
    public function test_import_data_from_keepass_xml(): void
    {

        $this->withoutExceptionHandling();

        /**
         * @var User $user
         */
        $user = User::factory()->create();
        $this->actingAs($user);

        $dispatcher = new Dispatcher($this->app);
        $dispatcher->dispatchSync(new ImportKeePassXML(
            filePath: $this->keepass_xml,
            masterPassword: UserFactory::MASTER_PASSWORD,
            dispatcher: app(Dispatcher::class)
        ));

        // Data from test keepass XLS
        $this->validateDataFromTestKeepassXLS();
    }

    public function test_a_user_can_import_keepass_xml(): void
    {
        /**
         * @var User $user
         */
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->postJson(route('import'), [
            'file' => new \Illuminate\Http\Testing\File('file.xml', fopen($this->keepass_xml,  "rb")),
            'master_password' => UserFactory::MASTER_PASSWORD
        ])->assertStatus(200);

        $this->validateDataFromTestKeepassXLS();
    }

    /**
     * @return void
     */
    public function validateDataFromTestKeepassXLS(): void
    {
        $test_groups = [
            [
                'name' => 'DatabaseTest',
                'parent_name' => null,
                'entries' => []
            ],
            [
                'name' => 'TestGroup1',
                'parent_name' => 'DatabaseTest',
                'entries' => [
                    [
                        'title' => 'TestEntry1',
                        'UserName' => 'useLogin1',
                        'password' => 'qUEnul8CU9kY6s6YYZt2',
                        'url' => '',
                        'note' => '',
                        'binaries' => []
                    ],
                    [
                        'title' => 'TestEntry2',
                        'UserName' => 'useLogin2',
                        'password' => 'wbdkPegWIeDmhHQtNYUA',
                        'url' => '',
                        'note' => '',
                        'binaries' => [
                            [
                                'name' => 'test_file_1.txt',
                                'contains' => 'Test data'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'name' => 'TestGroup1_2',
                'parent_name' => 'TestGroup1',
                'entries' => [
                    [
                        'title' => 'TestEntry1_2_1',
                        'UserName' => 'userLogin1_2_1',
                        'password' => 'RyEA0DRfi8qdLtaBc9Oc',
                        'url' => '',
                        'note' => '',
                        'binaries' => []
                    ]
                ]
            ],
            [
                'name' => 'TestGroup3',
                'parent_name' => 'DatabaseTest',
                'entries' => [
                    [
                        'title' => 'TestEntry3_1',
                        'UserName' => 'TestEntryLogin3_1',
                        'password' => '1I1H3lXJzKwMsfZAcyV3',
                        'url' => '',
                        'note' => '',
                        'binaries' => []
                    ],
                    [
                        'title' => 'TestEntry3_2',
                        'UserName' => 'TestUserLogin3_2-google',
                        'password' => 'rChCpodOKzln8CAW5822',
                        'url' => 'https://google.com',
                        'note' => 'Some  note',
                        'binaries' => []
                    ],
                ]
            ],

        ];

        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);

        foreach ($test_groups as $group) {
            $data = [
                'name' => $group['name']
            ];

            if (!is_null($group['parent_name'])) {
                $data['parent_entry_group_id'] = EntryGroup::where('name', $group['parent_name'])->firstOrFail()->entry_group_id->getValue();
            }

            $this->assertDatabaseHas(EntryGroup::class, $data);

            if (count($group['entries'])) {
                $group_id = EntryGroup::where('name', $group['name'])->firstOrFail()->entry_group_id->getValue();
                foreach ($group['entries'] as $entry) {
                    $data = [
                        'entry_group_id' => $group_id,
                        'title' => $entry['title']
                    ];
                    $this->assertDatabaseHas(Entry::class, $data);
                    $entry_id = Entry::where('entry_group_id', $group_id)->where('title', $entry['title'])->firstorFail()->entry_id->getValue();
                    if (!empty($entry['password'])) {
                        /**
                         * @var Password $field
                         */
                        $field = Password::where('entry_id', $entry_id)->firstOrFail();

                        $this->assertEquals(
                            $entry['password'],
                            $entryGroupService->decryptField($field, UserFactory::MASTER_PASSWORD)
                        );
                        $this->assertEquals(
                            $entry['UserName'],
                            $field->login->getValue()
                        );
                    }

                    if (!empty($entry['url'])) {
                        $field = Link::where('entry_id', $entry_id)->firstOrFail();

                        $this->assertEquals(
                            $entry['url'],
                            $entryGroupService->decryptField($field, UserFactory::MASTER_PASSWORD)
                        );
                    }

                    if (!empty($entry['note'])) {
                        $field = Note::where('entry_id', $entry_id)->firstOrFail();

                        $this->assertEquals(
                            $entry['note'],
                            $entryGroupService->decryptField($field, UserFactory::MASTER_PASSWORD)
                        );
                    }
                    if (!empty($entry['binaries'])) {
                        $this->assertEquals(count($entry['binaries']),
                            File::where('entry_id', $entry_id)->count()
                        );
                        foreach ($entry['binaries'] as $binary) {

                            /**
                             * @var File $file
                             */
                            $file = File::where('entry_id', $entry_id)->firstOrFail();
                            $this->assertEquals(
                                $binary['name'],
                                $file->file_name->getValue()
                            );
                            $this->assertEquals(
                                $binary['contains'],
                                $entryGroupService->decryptField($file, UserFactory::MASTER_PASSWORD)
                            );
                        }
                    }
                }
            }
        }
    }
}
