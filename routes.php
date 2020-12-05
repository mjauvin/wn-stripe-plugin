<?php

App::before(function ($request) {
    Route::any('/stripe/checkout/session/completed', function () {
        $checkoutResult = \Request::input();
        \Event::fire('studioazura.stripe.payment.completed', [$checkoutResult]);
    })->middleware('web'); 
});
