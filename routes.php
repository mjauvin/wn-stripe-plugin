<?php

Event::listen('system.route', function () {
    Route::group(['prefix' => 'stripe', 'middleware' => ['web']], function () {
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
