<?php

use StudioAzura\Stripe\Middleware\VerifyStripeEvent;

Event::listen('system.route', function () {
    Route::group(['prefix' => 'stripe', 'middleware' => [VerifyStripeEvent::class]], function () {
        Route::any('payment/authorized', function () {
            $result = Request::input();
            if ($result) {
                Event::fire('studioazura.stripe.payment.authorized', [$result]);
            }
        });
        Route::any('payment/captured', function () {
            $result = Request::input();
            if ($result) {
                Event::fire('studioazura.stripe.payment.captured', [$result]);
            }
        });
        Route::any('checkout/session/completed', function () {
            $result = Request::input();
            if ($result) {
                Event::fire('studioazura.stripe.payment.completed', [$result]);
            }
        });
    });
});
