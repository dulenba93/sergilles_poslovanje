<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Kreait\Firebase\Factory;

class FirebaseServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('firebase.firestore', function () {
            $factory = (new Factory)->withServiceAccount(base_path('firebase_credentials.json'));
            return $factory->createFirestore()->database();
        });
    }
}


