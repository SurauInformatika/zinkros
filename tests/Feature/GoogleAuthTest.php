<?php

namespace Tests\Feature;

use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    /** @var array<int, string> created users to clean up */
    private array $createdUserIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => '127.0.0.1']);
        config(['database.connections.mysql.port' => '3306']);
        config(['database.connections.mysql.database' => 'sit_school']);
        config(['database.connections.mysql.username' => 'root']);
        config(['database.connections.mysql.password' => '']);

        config(['services.google' => [
            'client_id' => 'test-client-id.apps.googleusercontent.com',
            'client_secret' => 'test-secret',
            'redirect' => 'http://localhost/auth/google/callback',
        ]]);
    }

    protected function tearDown(): void
    {
        User::whereIn('id', $this->createdUserIds)->forceDelete();

        parent::tearDown();
    }

    private function makeUser(array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $this->createdUserIds[] = $user->id;

        return $user;
    }

    private function mockGoogleDriver(string $googleId, string $email, string $name, ?string $avatar = null): void
    {
        $socialUser = (new SocialiteUser)
            ->map([
                'id' => $googleId,
                'name' => $name,
                'email' => $email,
                'avatar' => $avatar,
            ])
            ->setToken('fake-token');

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($socialUser);

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);
    }

    public function test_redirect_route_forwards_to_google(): void
    {
        $provider = Mockery::mock();
        $provider->shouldReceive('redirect')->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/v2/auth?client_id=test-client-id'));

        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('auth.google.redirect'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/v2/auth?client_id=test-client-id');
    }

    public function test_google_button_is_visible_on_login_page(): void
    {
        $this->get(route('auth.login'))
            ->assertOk()
            ->assertSee('Masuk dengan Google')
            ->assertSee(route('auth.google.redirect'));
    }

    public function test_callback_with_unknown_email_is_blocked(): void
    {
        $this->mockGoogleDriver('google-unknown', 'stranger@example.com', 'Orang Asing');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error');

        $this->assertGuest();
    }

    public function test_callback_with_linked_account_logs_in_directly(): void
    {
        $user = $this->makeUser([
            'role' => User::ROLE_GURU,
            'google_id' => 'google-already-linked',
            'google_avatar' => 'https://example.com/old-avatar.png',
        ]);

        $this->mockGoogleDriver('google-already-linked', $user->email, 'Nama Baru', 'https://example.com/new-avatar.png');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('guru.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => 'google-already-linked',
        ]);
    }

    public function test_callback_with_matching_email_links_and_logs_in_directly_without_password(): void
    {
        $user = $this->makeUser(['role' => User::ROLE_GURU]);

        $this->mockGoogleDriver('google-needs-link', $user->email, 'Nama Google', 'https://example.com/avatar.png');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('guru.dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'google_id' => 'google-needs-link',
            'google_avatar' => 'https://example.com/avatar.png',
        ]);
    }

    public function test_callback_when_email_linked_to_other_google_account_is_blocked(): void
    {
        $user = $this->makeUser(['role' => User::ROLE_GURU, 'google_id' => 'google-other']);

        $this->mockGoogleDriver('google-new-account', $user->email, 'Nama Baru');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('auth.login'))
            ->assertSessionHas('error');

        $this->assertGuest();
        $this->assertSame('google-other', $user->fresh()->google_id);
        $this->assertNull($user->fresh()->google_avatar);
    }

    public function test_link_requires_pending_session(): void
    {
        $this->get(route('auth.google.link'))
            ->assertRedirect(route('auth.login'));

        $this->post(route('auth.google.link.post'), [
            'email' => 'someone@example.com',
            'password' => 'whatever',
        ])->assertRedirect(route('auth.login'));

        $this->assertGuest();
    }

    public function test_cancel_clears_pending_session(): void
    {
        $this->withSession(['google.pending' => [
            'google_id' => 'google-cancel',
            'email' => 'pending@example.com',
            'name' => 'Pending User',
            'avatar' => null,
        ]]);

        $this->post(route('auth.google.link.cancel'))
            ->assertRedirect(route('auth.login'));

        $this->get(route('auth.google.link'))
            ->assertRedirect(route('auth.login'));
    }
}
