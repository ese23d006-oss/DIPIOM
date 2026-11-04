<?php

namespace App\Providers;

use App\Enums\FeedbackStatus;
use App\Models\Feedback;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('lms.force_https')) {
            URL::forceScheme('https');
        }

        if ($proxies = config('lms.trusted_proxies')) {
            TrustProxies::at(array_map('trim', explode(',', $proxies)));
        }

        Password::defaults(fn () => Password::min(8)->letters()->numbers());

        ResetPassword::toMailUsing(function ($user, string $token) {
            $url = route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);
            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)
                ->subject('Нууц үг сэргээх')
                ->greeting('Сайн байна уу!')
                ->line('Таны бүртгэлийн нууц үгийг сэргээх хүсэлт ирлээ.')
                ->action('Нууц үг сэргээх', $url)
                ->line("Энэ холбоос {$minutes} минутын дараа хүчингүй болно.")
                ->line('Хэрэв та хүсэлт илгээгээгүй бол энэ захидлыг үл тоомсорлоно уу.');
        });

        // Мэдэгдэл нь feedback хүснэгтийн бодит өгөгдлөөс бүрдэнэ.
        View::composer('components.layout.topbar', function ($view) {
            $user = auth()->user();

            if ($user->isAdmin()) {
                $pending = Feedback::where('status', FeedbackStatus::Pending);
                $view->with([
                    'notificationCount' => (clone $pending)->count(),
                    'notifications' => (clone $pending)->latest()->limit(5)->get()
                        ->map(fn (Feedback $item) => ["Шинэ санал хүсэлт: {$item->subject}", route('admin.feedback.show', $item)]),
                ]);

                return;
            }

            $answered = $user->feedback()->where('status', FeedbackStatus::Answered);
            $view->with([
                'notificationCount' => (clone $answered)->where('replied_at', '>=', now()->subDays(7))->count(),
                'notifications' => (clone $answered)->latest('replied_at')->limit(5)->get()
                    ->map(fn (Feedback $item) => ["Хариу ирлээ: {$item->subject}", route('student.feedback')]),
            ]);
        });
    }
}
