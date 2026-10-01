<?php

namespace App\Providers;

use App\Models\QuranTeachingAssignment;
use App\Observers\QuranTeachingAssignmentObserver;
use Cose\Algorithm\ManagerFactory as CoseAlgorithmManagerFactory;
use Cose\Algorithm\Signature\ECDSA;
use Cose\Algorithm\Signature\EdDSA;
use Cose\Algorithm\Signature\RSA;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

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
        Route::singularResourceParameters(false);
        QuranTeachingAssignment::observe(QuranTeachingAssignmentObserver::class);

        if ($this->app->environment('production')) {
            @unlink(public_path('hot'));
        }

        $this->app->bind(
            CoseAlgorithmManagerFactory::class,
            function (): CoseAlgorithmManagerFactory {
                return tap(new CoseAlgorithmManagerFactory, function (CoseAlgorithmManagerFactory $factory): void {
                    $algorithms = [
                        RSA\RS1::class => [true],
                        RSA\RS256::class => [],
                        RSA\RS384::class => [],
                        RSA\RS512::class => [],
                        RSA\PS256::class => [],
                        RSA\PS384::class => [],
                        RSA\PS512::class => [],
                        ECDSA\ES256::class => [],
                        ECDSA\ES256K::class => [],
                        ECDSA\ES384::class => [],
                        ECDSA\ES512::class => [],
                        EdDSA\Ed256::class => [],
                        EdDSA\Ed512::class => [],
                        EdDSA\Ed25519::class => [],
                        EdDSA\EdDSA::class => [],
                    ];

                    foreach ($algorithms as $algorithm => $args) {
                        $factory->add((string) $algorithm::identifier(), new $algorithm(...$args));
                    }
                });
            }
        );
    }
}
