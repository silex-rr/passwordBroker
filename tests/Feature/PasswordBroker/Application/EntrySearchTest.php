<?php

namespace PasswordBroker\Application;

use Exception;
use Identity\Domain\User\Models\User;
use Identity\Infrastructure\Factories\User\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
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
            $this->getJson(route('entrySearch', ['query' => $entryTitle]))
                ->assertStatus(200)
                ->assertJson(function (AssertableJson $json) use ($entryTitle, &$found) {
                    $json->has('result', 1, function (AssertableJson $result) use ($entryTitle, &$found) {
                        $result->has('entryGroups', function (AssertableJson $entryGroup) use ($entryTitle, &$found) {
                            $entryGroup->has('entries', function (AssertableJson $entry) use ($entryTitle, &$found) {
                                $entry->where('title', function (string $title) use ($entryTitle, &$found) {
                                    if ($entryTitle === $title) {
                                        $found = true;
                                    }
                                    return true;
                                });
                            });
                        });
                    });
                });
            $this->assertTrue($found, 'Entry with the Title ' . $entryTitle . ' was not found');
        }

        foreach ($entryFieldTitles as $entryFieldTitle) {
            $found = false;
            $this->getJson(route('entrySearch', ['query' => $entryFieldTitle]))
                ->assertStatus(200)
                ->assertJson(function (AssertableJson $json) use ($entryFieldTitle, &$found) {
                    $json->has('result', 1, function (AssertableJson $result) use ($entryFieldTitle, &$found) {
                        $result->has('entryGroups', function (AssertableJson $entryGroup) use ($entryFieldTitle, &$found) {
                            $entryGroup->has('entries', function (AssertableJson $entry) use ($entryFieldTitle, &$found) {
                                $entry->has('fields', function (AssertableJson $field) use ($entryFieldTitle, &$found) {
                                    $field->where('title', function (string $title) use ($entryFieldTitle, &$found) {
                                        if ($entryFieldTitle === $title) {
                                            $found = true;
                                        }
                                        return true;
                                    });
                                });
                            });
                        });
                    });
                });
            $this->assertTrue($found, 'Entry Field with the Title ' . $entryFieldTitle . ' was not found');
        }
    }

    private function seedDatabase(User $user, array $entryTitles = [], array $entryFieldTitles = []): void
    {
        $groupFactory = EntryGroup::factory();
        $entryFactory = Entry::factory();

        $groupA = $groupFactory->create(['name' => GroupName::fromNative('groupA')]);
        $groupB = $groupFactory->create(['name' => GroupName::fromNative('groupB')]);
        $groupC = $groupFactory->create(['name' => GroupName::fromNative('groupC')]);
        foreach ([$groupA, $groupB, $groupC] as $group) {
            /**
             * @var EntryGroup $group
             */
            $group->addAdmin($user, $this->faker->password(128,128));
            try {
                $count = random_int(10, 20);
            } catch (Exception $e) {
                $count = 10;
            }
            $entryFactory->withEntryGroup($group)->count($count)->create();
            if (count($entryTitles)) {
                $title = array_shift($entryTitles);
                $entry = $entryFactory->withEntryGroup($group)->create(['title' => Title::fromNative($title)]);
                if (count($entryFieldTitles)) {
                    $fieldTitle = array_shift($entryFieldTitles);
                    $this->getPasswordHelper(
                        owner: $user,
                        entryGroup: $group,
                        entry: $entry,
                        password_str: $this->faker->password,
                        title: $fieldTitle
                    );
                }
            }
        }
    }
}
