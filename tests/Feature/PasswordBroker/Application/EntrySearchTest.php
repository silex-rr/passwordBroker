<?php

namespace PasswordBroker\Application;

use Exception;
use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use PasswordBroker\Application\Services\EntryGroupService;
use PasswordBroker\Domain\Entry\Models\Attributes\GroupName;
use PasswordBroker\Domain\Entry\Models\Attributes\Title;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use Tests\Feature\PasswordBroker\Application\PasswordHelper;
use Tests\TestCase;

class EntrySearchTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    use PasswordHelper;

    public function test_a_user_can_find_an_entry(): void
    {
        $user = User::factory()->systemAdministrator()->create();
        $entryTitles = [
            $this->faker->word,
            $this->faker->word,
            $this->faker->word,
        ];
        $entryFieldTitles = [
            $this->faker->word,
            $this->faker->word,
            $this->faker->word,
        ];

        $this->actingAs($user);

        $this->seedDatabase($user, $entryTitles, $entryFieldTitles);

        foreach ($entryTitles as $entryTitle) {
            $found = false;
            $this->getJson(route('entrySearch', ['q' => $entryTitle, 'perPage' => 100]))
                ->assertStatus(200)
                ->assertJson(function (AssertableJson $json) use ($entryTitle, &$found) {
                    $json->has('data');
                    $resultNum = count($json->toArray()['data']);
                    for ($i = 0; $i < $resultNum; $i++) {
                        $json->where('data.' . $i . '.title', function (string $title) use ($entryTitle, &$found) {
//                            var_dump(['title', $title]);
                            if ($entryTitle === $title) {
                                $found = true;
                            }
                            return true;
                        });
                    }

                    $json->etc();
//                    $json->has('data', null, function (AssertableJson $entry) use ($entryTitle, &$found) {
//                        $entry->where('title', function (string $title) use ($entryTitle, &$found) {
//                            var_dump(['title', $title]);
//                            if ($entryTitle === $title) {
//                                $found = true;
//                            }
//                            return true;
//                        })->etc();
//                    })->etc();
//                    if (!$found) {
//                        var_dump($entryTitle);
//                        $json->dd();
//                    }
                });
            $this->assertTrue($found, 'Entry with the Title ' . $entryTitle . ' was not found');
        }

        foreach ($entryFieldTitles as $entryFieldTitle) {
            $found = false;
            $this->getJson(route('entrySearch', ['q' => $entryFieldTitle, 'perPage' => 100]))
                ->assertStatus(200)
                ->assertJson(function (AssertableJson $json) use ($entryFieldTitle, &$found) {
                    $json->has('data');

                    $array = $json->toArray();
                    foreach ($array['data'] as $entry) {
                        foreach ($entry['passwords'] as $password) {
                            if ($entryFieldTitle === $password['title']) {
                                $found = true;
                            }
                        }
                    }

                    $json->etc();
//                    $json->has('data', null, function (AssertableJson $entry) use ($entryFieldTitle, &$found) {
//
//                        $entry->has('passwords', null,  function (AssertableJson $field) use ($entryFieldTitle, &$found) {
//                            $field->where('title',  function (string $title) use ($entryFieldTitle, &$found) {
//                                if ($entryFieldTitle === $title) {
//                                    $found = true;
//                                }
//                                return $found;
//                            })->etc();
//                        })->etc();
//                    })->etc();
                });
            $this->assertTrue($found, 'Entry Field with the Title ' . $entryFieldTitle . ' was not found');
        }
    }

    public function test_a_user_can_not_find_entries_that_they_has_not_access_to(): void
    {
        $user = User::factory()->systemAdministrator()->create();

        $entryTitles = [
            'aaaa',
            'bbbb',
            'cccc',
        ];
        $entryFieldTitles = [
            'aaaaT',
            'bbbbT',
            'ccccT',
        ];
        $this->seedDatabase($user, $entryTitles, $entryFieldTitles);

        $user_other = User::factory()->create();
        $entryTitlesOther = [
            'dddd',
            'eeee',
            'ffff',
        ];
        $entryFieldTitlesOther = [
            'ddddT',
            'eeeeT',
            'ffffT',
        ];
        $this->seedDatabase($user_other, $entryTitlesOther, $entryFieldTitlesOther);

        $this->actingAs($user);

        $this->getJson(route('entrySearch', ['q' => $entryTitles[0], 'perPage' => 100]))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', 1)->etc();
            });

        $this->getJson(route('entrySearch', ['q' => $entryTitlesOther[0], 'perPage' => 100]))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', 0)->etc();
            });
    }

    private function seedDatabase(User $user, array $entryTitles = [], array $entryFieldTitles = []): void
    {
        $groupFactory = EntryGroup::factory();
        $entryFactory = Entry::factory();

        $groupA = $groupFactory->create(['name' => GroupName::fromNative('groupA' . $this->faker->word())]);
        $groupB = $groupFactory->create(['name' => GroupName::fromNative('groupB' . $this->faker->word())]);
        $groupC = $groupFactory->create(['name' => GroupName::fromNative('groupC' . $this->faker->word())]);

        /**
         * @var EntryGroupService $entryGroupService
         */
        $entryGroupService = app(EntryGroupService::class);

        foreach ([$groupA, $groupB, $groupC] as $group) {
            $entryGroupService->addUserToGroupAsAdmin($user, $group);
            try {
                $count = random_int(2, 4);
            } catch (Exception $e) {
                $count = 2;
            }

            $addPasswords = function ($entry, $title = null) use ($user, $group){
                $this->getPasswordHelper(
                    owner: $user,
                    entryGroup: $group,
                    entry: $entry,
                    password_str: $this->faker->password,
                    title: $title ?? $this->faker->word
                );
            };

            $entries = $entryFactory->withEntryGroup($group)->count($count)->create();
            foreach ($entries as $en) {
                for ($i = 0; $i < 2; $i++) {
                    $addPasswords($en);
                }
            }

            if (count($entryTitles)) {
                $title = array_shift($entryTitles);
                $entry = $entryFactory->withEntryGroup($group)->create(['title' => Title::fromNative($title)]);
                if (count($entryFieldTitles)) {
                    $fieldTitle = array_shift($entryFieldTitles);
                    $addPasswords($entry, $fieldTitle);
                }
            }
        }
    }
}
