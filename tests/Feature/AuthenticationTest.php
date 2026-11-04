<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->role = UserRole::Admin;
        $admin->save();

        return $admin;
    }

    /** @return array<string, string> */
    private function registration(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Болд',
            'email' => 'bold@example.com',
            'password' => 'nuutsug123',
            'password_confirmation' => 'nuutsug123',
        ], $overrides);
    }

    public function test_student_can_register_and_is_logged_in(): void
    {
        $this->post('/register', $this->registration(['email' => 'BOLD@Example.com']))
            ->assertRedirect(route('student.dashboard'));

        $user = User::where('email', 'bold@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::Student, $user->role);
        $this->assertTrue(Hash::check('nuutsug123', $user->password));
    }

    public function test_registration_cannot_grant_admin_role(): void
    {
        $this->post('/register', $this->registration(['role' => 'admin', 'is_active' => '0']));

        $user = User::where('email', 'bold@example.com')->firstOrFail();
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->is_active);
    }

    public function test_registration_errors_are_in_mongolian_and_accessible(): void
    {
        User::factory()->create(['email' => 'bold@example.com']);

        $response = $this->followingRedirects()
            ->from('/register')
            ->post('/register', $this->registration(['password' => 'short', 'password_confirmation' => 'other']));

        $response->assertSee('Энэ Имэйл хаяг аль хэдийн бүртгэгдсэн байна.')
            ->assertSee('role="alert"', false)
            ->assertSee('data-focus-on-load', false)
            ->assertSee('aria-invalid="true"', false)
            ->assertSee('aria-describedby="email-error"', false)
            ->assertSee('<title>Алдаа: Бүртгүүлэх', false)
            ->assertSee('value="bold@example.com"', false);

        $this->assertGuest();
    }

    public function test_student_login_redirects_to_student_dashboard(): void
    {
        $user = User::factory()->create(['password' => 'nuutsug123']);

        $this->post('/login', ['email' => $user->email, 'password' => 'nuutsug123'])
            ->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = $this->admin();

        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_wrong_password_is_rejected_with_generic_message(): void
    {
        $user = User::factory()->create();

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'buruu'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Имэйл хаяг эсвэл нууц үг буруу байна.']);

        $this->assertGuest();
    }

    public function test_login_is_throttled_after_five_failures(): void
    {
        $user = User::factory()->create();
        RateLimiter::clear($user->email.'|127.0.0.1');

        foreach (range(1, 5) as $ignored) {
            $this->post('/login', ['email' => $user->email, 'password' => 'buruu']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        $user = User::factory()->create();
        $user->is_active = false;
        $user->save();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => __('auth.inactive')]);

        $this->assertGuest();
    }

    public function test_user_deactivated_during_session_is_logged_out(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk();

        $user->is_active = false;
        $user->save();

        $this->actingAs($user->fresh())->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_logout_ends_session(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_student_cannot_access_admin_area(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden()
            ->assertSee('Хандах эрхгүй байна');
    }

    public function test_admin_cannot_access_student_area(): void
    {
        $this->actingAs($this->admin())->get('/dashboard')->assertForbidden();
    }

    public function test_student_intending_admin_page_lands_on_own_dashboard(): void
    {
        $user = User::factory()->create();

        $this->get('/admin')->assertRedirect(route('login'));

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('student.dashboard'));
    }

    public function test_logged_in_user_is_redirected_away_from_login_page(): void
    {
        $this->actingAs($this->admin())->get('/login')->assertRedirect(route('admin.dashboard'));
    }

    public function test_dashboards_show_real_counts(): void
    {
        User::factory()->count(2)->create();

        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertOk()
            ->assertSeeInOrder(['Нийт суралцагч', '2'])
            ->assertSeeInOrder(['Нийт сургалт', '0']);
    }

    public function test_reset_link_is_sent_in_mongolian(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email])
            ->assertSessionHas('status', __('passwords.sent'));

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $mail = $notification->toMail($user);

            return $mail->subject === 'Нууц үг сэргээх'
                && str_contains($mail->actionUrl, '/reset-password/');
        });
    }

    public function test_forgot_password_does_not_reveal_whether_email_exists(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'baihgui@example.com'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', __('passwords.sent'));

        Notification::assertNothingSent();
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->get("/reset-password/{$token}?email={$user->email}")
            ->assertOk()
            ->assertSee('value="'.$user->email.'"', false);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'shineug456',
            'password_confirmation' => 'shineug456',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('shineug456', $user->fresh()->password));
    }

    public function test_password_reset_fails_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $this->from('/reset-password/buruu')
            ->post('/reset-password', [
                'token' => 'buruu',
                'email' => $user->email,
                'password' => 'shineug456',
                'password_confirmation' => 'shineug456',
            ])
            ->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_pages_have_required_accessibility_structure(): void
    {
        foreach (['/', '/login', '/register', '/forgot-password', '/page-that-does-not-exist'] as $path) {
            $this->get($path)
                ->assertSee('<html lang="mn">', false)
                ->assertSee('href="#main-content"', false)
                ->assertSee('<main id="main-content"', false)
                ->assertSee('aria-label="Үндсэн цэс"', false)
                ->assertSee('<h1', false);
        }

        $this->get('/login')
            ->assertSee('<label for="email"', false)
            ->assertSee('<label for="password"', false)
            ->assertSee('aria-current="page"', false);
    }
}
