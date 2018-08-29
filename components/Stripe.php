<?php namespace StudioAzura\Stripe\Components;

use Event;
use Flash;
use Http;
use Log;
use Redirect;
use ValidationException;
use Cms\Classes\ComponentBase;
use StudioAzura\Stripe\Models\Settings;

class Stripe extends ComponentBase
{
    public $stripeUrl = "https://api.stripe.com/v1";

    public function componentDetails()
    {
        return [
            'name'        => 'Stripe',
            'description' => 'Stripe API Component',
        ];
    }

    public function defineProperties()
    {
        $currency = Settings::get('currency') ?: 'USD';

        return [
            'isTestMode' => [
                'title'             => 'Test Mode',
                'description'       => 'enable stripe test mode',
                'type'              => 'checkbox',
                'default'           => false,
            ],
            'currency' => [
                'title'             => 'Currency',
                'description'       => 'Currency used for the transactions',
                'type'              => 'string',
                'default'           => $currency,
            ],
            'locale' => [
                'title'             => 'Locale',
                'description'       => 'Locale to use with Stripe',
                'type'              => 'string',
                'default'           => 'auto',
            ],
            'appName' => [
                'title'             => 'Application Name',
                'description'       => 'What to show as Stripe Pop-up Title',
                'type'              => 'string',
                'default'           => config('app.name'),
            ],
        ];
    }

    public function locale()
    {
        return $this->property('locale');
    }

    public function currency()
    {
        return strtoupper($this->property('currency'));
    }

    public function billingAddress()
    {
        return Settings::get('is_billing_address');
    }

    public function shippingAddress()
    {
        return Settings::get('is_shipping_address');
    }
    public function zipCode()
    {
        return Settings::get('is_zip_code');
    }

    public function appName()
    {
        return $this->property('appName');
    }

    public function logo()
    {
        $logo = Settings::get('logo');
        if ($logo) {
            return url(config('cms.storage.media.path') . $logo);
        } else {
            return 'https://stripe.com/img/documentation/checkout/marketplace.png';
        }
    }

    public function pubKey()
    {
        if ($this->property('isTestMode')) {
            return Settings::get('pk_test');
        } else {
            return Settings::get('pk_live');
        }
    }
    public function secretKey()
    {
        if ($this->property('isTestMode')) {
            return Settings::get('sk_test');
        } else {
            return Settings::get('sk_live');
        }
    }

    public function onRun()
    {
        $this->addJs('assets/js/ajax.js');
    }

    public function onStripeCallback()
    {
        $stripe = post('stripeData');
        $invoice = post('invoiceData');
        $address = post('addressData');
        $redirect = post('redirect');

        $params = [ $this, $stripe, $invoice, $address, $redirect ];

        // hook before stripe_charge()
        // if the hook returns something true, bypass the stripe_charge() call
        if( ($results = Event::fire('studioazura.stripe.handleStripeCallback', $params, true)) ) {
            return $results;
        }

        return $this->stripe_charge($stripe, $invoice, $address, $redirect);
    }

    public function stripe_charge($stripe, $invoice, $address, $redirect)
    {
        $postData = array( 
          'source' => $stripe['id'],
          'amount' => $invoice['amount'] * 100,
          'capture' => 'true',
          'currency' => $invoice['currency'],
          'description' => $invoice['description'],
          'metadata' => array(
            'email' => $stripe['email'],
            'client_ip' => $stripe['client_ip'],
          ),
        );

        $params = [ $this, &$postData, $stripe, $invoice, $address ];
        Event::fire('studioazura.stripe.setChargePostData', $params);

        Log::info( var_export($postData, true) );

        $request = Http::make($this->stripeUrl . '/charges', 'POST');
        $request->auth($this->secretKey());
        $request->data($postData);

        $response = $request->send();

        $params = [ $this, $response, $redirect ];
        // hook to handle routing after stripe_charge()
        if( ($results = Event::fire('studioazura.stripe.handleStripeChargeResponse', $params))) {
            return $results;
        }

        if ($response->code != 200 && !$response->body) {
            Log::error( var_export(array('response'=>$response, 'request'=>$request->requestData), true) );
            Flash::error( 'Fatal Communication Error' );
            return;
        }
        $results = json_decode($response->body, true);
        if (isset($results['error'])) {
            Log::error( var_export(array('error'=>$results['error'], 'request'=>$request->requestData), true) );
            Flash::error( 'Something went wrong' );
            return;
        }
        if ($results['paid'] && $results['captured']) {
            Log::info( var_export($results, true) );
            $msg = 'Payment Status: ' . $results['status'];
            Flash::success($msg);
            return Redirect::to($redirect);
        } else {
            Log::error( var_export($results, true) );
            Flash::error('Something went wrong; Payment Status: ' . $results['status']);
            return;
        }
    }
}
