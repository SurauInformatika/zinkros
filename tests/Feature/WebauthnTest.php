<?php

namespace Tests\Feature;

use App\Models\User;
use LaravelWebauthn\Models\WebauthnKey;
use Tests\TestCase;

class WebauthnTest extends TestCase
{
    /** @var array<int, string> created users to clean up */
    private array $createdUserIds = [];

    /** @var array<int, int> created keys to clean up */
    private array $createdKeyIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => '127.0.0.1']);
        config(['database.connections.mysql.port' => '3306']);
        config(['database.connections.mysql.database' => 'sit_school']);
        config(['database.connections.mysql.username' => 'root']);
        config(['database.connections.mysql.password' => '']);
    }

    protected function tearDown(): void
    {
        if ($this->createdKeyIds !== []) {
            WebauthnKey::whereIn('id', $this->createdKeyIds)->forceDelete();
        }

        User::whereIn('id', $this->createdUserIds)->forceDelete();

        parent::tearDown();
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $this->createdUserIds[] = $user->id;

        return $user;
    }

    private function makeKey(User $user, string $name = 'Test key'): WebauthnKey
    {
        $key = new WebauthnKey;
        $key->forceFill([
            'user_id' => $user->id,
            'name' => $name,
            'credentialId' => 'test-credential-id',
            'type' => 'public-key',
            'transports' => ['internal'],
            'attestationType' => 'none',
            'trustPath' => [],
            'aaguid' => '00000000-0000-0000-0000-000000000000',
            'credentialPublicKey' => 'public-key-bytes',
            'counter' => 0,
        ])->save();

        $this->createdKeyIds[] = $key->id;

        return $key;
    }

    public function test_guest_cannot_reach_registration_endpoints(): void
    {
        $this->postJson(route('webauthn.store.options'))
            ->assertStatus(401);

        $this->postJson(route('webauthn.store'), [
            'id' => 'x',
            'type' => 'public-key',
            'rawId' => 'x',
            'response' => ['attestationObject' => 'x', 'clientDataJSON' => 'x'],
            'name' => 'Test',
        ])->assertStatus(401);
    }

    public function test_guest_cannot_open_passkey_management_page(): void
    {
        $this->get(route('passkey.index'))
            ->assertStatus(302);
    }

    public function test_login_options_do_not_require_an_email_in_userless_mode(): void
    {
        $this->postJson(route('webauthn.auth.options'))
            ->assertOk()
            ->assertJsonPath('publicKey.challenge', fn ($challenge) => is_string($challenge) && strlen($challenge) > 20)
            ->assertJsonPath('publicKey.allowCredentials', [])
            ->assertJsonPath('publicKey.userVerification', 'required');
    }

    public function test_login_options_return_public_key_request_options(): void
    {
        $user = $this->makeUser(['role' => User::ROLE_GURU]);
        $this->makeKey($user, 'Laptop kantor');

        $this->postJson(route('webauthn.auth.options'), ['email' => $user->email])
            ->assertOk()
            ->assertJsonPath('publicKey.challenge', fn ($challenge) => is_string($challenge) && strlen($challenge) > 20)
            ->assertJsonPath('publicKey.rpId', 'localhost')
            ->assertJsonPath('publicKey.allowCredentials', [])
            ->assertJsonPath('publicKey.userVerification', 'required');
    }

    public function test_login_attempt_with_unknown_credential_is_rejected(): void
    {
        $this->makeUser(['role' => User::ROLE_GURU]);

        $this->postJson(route('webauthn.auth'), [
            'id' => 'bm90LXN0b3JlZA==',
            'type' => 'public-key',
            'rawId' => 'bm90LXN0b3JlZA==',
            'response' => [
                'authenticatorData' => 'ew==',
                'clientDataJSON' => 'ew==',
                'signature' => 'c2ln',
            ],
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }

    public function test_register_options_return_challenge_for_authenticated_user(): void
    {
        $user = $this->makeUser(['role' => User::ROLE_GURU]);

        $this->actingAs($user)
            ->postJson(route('webauthn.store.options'))
            ->assertOk()
            ->assertJsonPath('publicKey.challenge', fn ($challenge) => is_string($challenge) && strlen($challenge) > 20)
            ->assertJsonPath('publicKey.pubKeyCredParams', fn ($params) => is_array($params) && count($params) > 0)
            ->assertJsonPath('publicKey.rp.id', 'localhost');
    }

    public function test_authenticated_user_can_view_passkey_page_with_their_keys(): void
    {
        $user = $this->makeUser(['role' => User::ROLE_GURU]);
        $this->makeKey($user, 'Laptop kantor');

        $this->actingAs($user)
            ->get(route('passkey.index'))
            ->assertOk()
            ->assertSee('Keamanan Akun')
            ->assertSee('Laptop kantor');
    }

    public function test_user_can_only_delete_their_own_keys(): void
    {
        $user = $this->makeUser(['role' => User::ROLE_GURU]);
        $other = $this->makeUser(['role' => User::ROLE_GURU]);
        $ownKey = $this->makeKey($user, 'Milik saya');
        $otherKey = $this->makeKey($other, 'Milik orang lain');

        $this->actingAs($user)
            ->deleteJson(route('webauthn.destroy', $otherKey->id))
            ->assertStatus(404);

        $this->assertDatabaseHas('webauthn_keys', ['id' => $otherKey->id]);

        $this->actingAs($user)
            ->deleteJson(route('webauthn.destroy', $ownKey->id))
            ->assertStatus(204);

        $this->assertDatabaseMissing('webauthn_keys', ['id' => $ownKey->id]);
    }

    public function test_password_login_still_works_with_webauthn_provider(): void
    {
        $user = $this->makeUser([
            'role' => User::ROLE_GURU,
            'password' => 'ini-password-123',
        ]);

        $this->post(route('auth.login.post'), [
            'email' => $user->email,
            'password' => 'ini-password-123',
        ])->assertRedirect(route('guru.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_after_login_redirect_uses_role_dashboard(): void
    {
        $user = $this->makeUser(['role' => User::ROLE_GURU]);

        $this->actingAs($user)
            ->get(route('passkey.after-login'))
            ->assertRedirect(route('guru.dashboard'));
    }

    public function test_after_login_redirect_forces_change_password_for_ortu(): void
    {
        $user = $this->makeUser(['role' => User::ROLE_ORTU]);

        $this->actingAs($user)
            ->get(route('passkey.after-login'))
            ->assertRedirect(route('ortu.password.change'));
    }
}
