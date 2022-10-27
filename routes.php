<?php

Event::listen('system.route', function () {
    Route::any('/stripe/checkout/session/completed', function () {
        $checkoutResult = Request::input();
        if ($checkoutResult) {
            Event::fire('studioazura.stripe.payment.completed', [$checkoutResult]);
        }
    })->middleware('web'); 
});
