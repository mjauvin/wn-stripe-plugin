<?php

App::before(function ($request) {
    Route::any('/stripe/checkout/session/completed', '\StudioAzura\Stripe\Controllers\StripeOrders@webhooks')->middleware('web'); 
});
