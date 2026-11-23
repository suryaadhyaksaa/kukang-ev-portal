<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        Fortify::loginView(fn () => view('auth.login'));
        Fortify::registerView(fn () => view('auth.register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth.forgot-password'));
        Fortify::resetPasswordView(fn (Request $request) => view('auth.reset-password', ['request' => $request]));

        Fortify::authenticateUsing(function (Request $request) {
            $user = User::where('email', $request->email)->first();

            if ($user && $user->is_active && Hash::check($request->password, $user->password)) {
                return $user;
            }

            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->email.$request->ip());
        });

        Event::listen(Login::class, function (Login $event) {
            if ($event->user instanceof User) {
                ActivityLog::create([
                    'user_id' => $event->user->id,
                    'action' => 'auth.login',
                    'description' => "User {$event->user->email} berhasil login.",
                    'entity_type' => User::class,
                    'entity_id' => $event->user->id,
                    'ip_address' => request()?->ip(),
                    'created_at' => now(),
                ]);
            }
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user instanceof User) {
                ActivityLog::create([
                    'user_id' => $event->user->id,
                    'action' => 'auth.logout',
                    'description' => "User {$event->user->email} logout dari sistem.",
                    'entity_type' => User::class,
                    'entity_id' => $event->user->id,
                    'ip_address' => request()?->ip(),
                    'created_at' => now(),
                ]);
            }
        });
    }
}
