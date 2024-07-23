<?php

namespace App\Providers;

use App\Actions\Fortify\AttemptToAuthenticate;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\RedirectIfTwoFactorAuthenticatable;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Actions\Jetstream\ConfirmsAdminPasswords;
use App\Http\Controllers\Admin\Auth\AdminAuthenticationSessionController;
use App\Http\Controllers\Admin\TwoFactorAuthenticatedSessionController;
use App\Http\Requests\TwoFactorLoginRequest;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Jetstream\ConfirmsPasswords;
use Livewire\Livewire;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        //? Register the guards for the admin authentication
        $this->app->when([  //? When the following classes are instantiated
            AdminAuthenticationSessionController::class,
            AttemptToAuthenticate::class,
            RedirectIfTwoFactorAuthenticatable::class,
            TwoFactorAuthenticatedSessionController::class,
            TwoFactorLoginRequest::class,
        ])->needs(StatefulGuard::class) //? Needs an instance of StatefulGuard
            ->give(function(){
            return Auth::guard('admin'); //? Return the admin guard
        });


    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

    }
}
