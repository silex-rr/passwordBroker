<?php

namespace Tests\Unit\PasswordBroker\Domain\Entry;

use Identity\Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PasswordBroker\Domain\Entry\Models\Entry;
use PasswordBroker\Domain\Entry\Models\EntryGroup;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\Random;
use phpseclib3\Crypt\Rijndael;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PrivateKey;
use phpseclib3\Crypt\RSA\PublicKey;
use Tests\TestCase;

class EntryTest extends TestCase
{
    use WithFaker;
    use RefreshDatabase;

    public function test_cryptography(): void
    {
        $master_pass = $this->faker()->password;

        $data = $this->faker->sentence;

        $privateKey = RSA::createKey(4096);
        $privateKey = $privateKey->withPassword($master_pass);
        $privateKey = $privateKey->withHash('sha512');

        $publicKey = $privateKey->getPublicKey();
        $privateKey = PublicKeyLoader::load((string)$privateKey, $master_pass);
        $publicKey = PublicKeyLoader::load((string)$publicKey);

        $encrypted_data = $publicKey->encrypt($data);

        $this->assertEquals($data, $privateKey->decrypt($encrypted_data));


        $pass = $this->faker->password();

        $data_pass = $this->faker->sentence();

        $cipher = new Rijndael('ctr');
        $iv = Random::string(16);
        $cipher->setIV($iv);
        $cipher->setPassword($pass);
        $data_pass_encrypted = $cipher->encrypt($data_pass);

        $cipher = new Rijndael('ctr');
        $cipher->setIV($iv);
        $cipher->setPassword($pass);

        $this->assertEquals($data_pass, $cipher->decrypt($data_pass_encrypted));
    }

    public function test_an_entry_can_belong_to_an_entry_group(): void
    {
        /**
         * @var Entry $entry
         * @var EntryGroup $entryGroup
         */
        $entry = Entry::factory()->create();
        $entryGroup = EntryGroup::factory()->create();
        $entry->entryGroup()->dissociate();
        $this->assertNull($entry->entryGroup()->first());

        $entry->entryGroup()->associate($entryGroup);

        $this->assertTrue($entryGroup->is($entry->entryGroup()->first()));
    }

    public function test_an_entry_can_have_a_password(): void
    {
        /**
         * @var Entry $entry
         * @var User $user
         */
        $entry = Entry::factory()->create();
        $user = User::factory()->create();

        $password = $entry->addPassword(
            userId: $user->user_id,
            password_encrypted: $this->faker->password(16, 16),
            initializing_vector: $this->faker->password(128, 128),
            login: 'test_login'
        );

        $this->assertEquals(
            1,
            $entry->passwords()->where('field_id', $password->field_id)->count()
        );
    }

    public function test_an_entry_can_have_a_note(): void
    {
        /**
         * @var Entry $entry
         * @var User $user
         */
        $entry = Entry::factory()->create();
        $user = User::factory()->create();

        $note = $entry->addNote(
            $user->user_id,
            $this->faker->password(16, 16),
            $this->faker->password(128, 128)
        );

        $this->assertEquals(
            1,
            $entry->notes()->where('field_id', $note->field_id)->count()
        );
    }

    public function test_an_entry_can_have_a_link(): void
    {
        /**
         * @var Entry $entry
         * @var User $user
         */
        $entry = Entry::factory()->create();
        $user = User::factory()->create();

        $link = $entry->addLink(
            $user->user_id,
            $this->faker->password(16, 16),
            $this->faker->password(128, 128)
        );

        $this->assertEquals(
            1,
            $entry->links()->where('field_id', $link->field_id)->count()
        );
    }
    public function test_an_entry_can_have_a_files(): void
    {
        /**
         * @var Entry $entry
         * @var User $user
         */
        $entry = Entry::factory()->create();
        $user = User::factory()->create();

        $file = $entry->addFile(
            $user->user_id,
            $this->faker->text(),
            $this->faker->password(128, 128)
        );

        $this->assertEquals(
            1,
            $entry->files()->where('field_id', $file->field_id)->count()
        );
    }
}
