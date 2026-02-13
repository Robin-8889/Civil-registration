<?php

namespace App\Providers;

use App\Models\BirthRecord;
use App\Models\DeathRecord;
use App\Models\MarriageRecord;
use App\Models\RegistrationOffice;
use App\Policies\BirthRecordPolicy;
use App\Policies\DeathRecordPolicy;
use App\Policies\MarriageRecordPolicy;
use App\Policies\RegistrationOfficePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        RegistrationOffice::class => RegistrationOfficePolicy::class,
        BirthRecord::class => BirthRecordPolicy::class,
        MarriageRecord::class => MarriageRecordPolicy::class,
        DeathRecord::class => DeathRecordPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
