<?php namespace StudioAzura\Stripe\Components;

use Log;
use Flash;
use Redirect;
use Http;
use ValidationException;
use Cms\Classes\ComponentBase;
use StudioAzura\Stripe\Models\Settings;

class Stripe extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Stripe',
            'description' => 'Stripe API Component',
        ];
    }

    public function defineProperties()
    {
        $currency = Settings::get('currency') ? Settings::get('currency') : 'USD';

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
        ];
    }

    public function onRun()
    {
        if ($this->pub_key()) {
            $this->addJs('https://checkout.stripe.com/checkout.js');
            $this->addJs('assets/js/ajax.js');
        }
    }

    public function locale()
    {
        return $this->property('locale');
    }

    public function currency()
    {
        return strtoupper($this->property('currency'));
    }

    public function app_name()
    {
        return config('app.name');
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

    public function pub_key()
    {
        if ($this->property('isTestMode')) {
            return Settings::get('pk_test');
        } else {
            return Settings::get('pk_live');
        }
    }
    protected function secret_key()
    {
        if ($this->property('isTestMode')) {
            return Settings::get('sk_test');
        } else {
            return Settings::get('sk_live');
        }
    }

    protected function stripe_url()
    {
        return "https://api.stripe.com/v1";
    }

    public function onStripeCallback()
    {
        $stripe = post('stripeData');
        $invoice = post('invoiceData');
        $redirect = post('redirect');

        $response = $this->stripe_charge($stripe, $invoice);

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

    protected function stripe_charge($stripe, $invoice)
    {
        $postData = array( 
          'source' => $stripe['id'],
          'amount' => $invoice['amount'] * 100,
          'capture' => 'true',
          'currency' => $this->property('currency'),
          'description' => $invoice['description'],
          'metadata' => array(
            'email' => $stripe['email'],
            'client_ip' => $stripe['client_ip'],
          ),
        );
        $request = Http::make($this->stripe_url() . '/charges', 'POST');
        $request->auth($this->secret_key());
        $request->data($postData);

        return $request->send();
    }
}
