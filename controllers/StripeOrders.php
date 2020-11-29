<?php namespace StudioAzura\Stripe\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Event;
use Session;

class StripeOrders extends Controller
{
    protected function webhooks()
    {
        $checkoutResult = \Request::input();
        Event::fire('studioazura.stripe.payment.completed', [$checkoutResult]);
    }
}
