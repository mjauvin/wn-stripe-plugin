#### SCA compliant Stripe Checkout Component

This plugin create a checkout session with a prebuilt checkout page hosted on Stripe.com servers.

A new event has been added to get notified once the payment has been completed:

    \Event::listen('studioazura.stripe.payment.completed', function ($data) {
        trace_log($data);
    });

