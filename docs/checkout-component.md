#### SCA compliant Stripe Checkout Component

This plugin create a checkout session with a prebuilt checkout page hosted on Stripe.com servers.

Here is a complete example of a CMS page that uses the component:

```
title = "Stripe Test Page"
url = "/stripe"
layout = "default"
is_hidden = 0

[viewBag]
orderAmount = 25
orderDescription = "My New Test Order"
emailAddress = "test.email@domain.tld"

[stripeCheckout]
isTestMode = 1
currency = "USD"
locale = "auto"
cancelUrl = "/payment/cancelled"
successUrl = "/payment/completed"
==
{% component "stripeCheckout" %}
```

A new event has been added to get notified once the payment has been completed:

    \Event::listen('studioazura.stripe.payment.completed', function ($data) {
        trace_log($data);
    });

