<?php namespace StudioAzura\Stripe\Components;

use Event;
use Flash;
use Log;
use Redirect;

class Stripe extends BaseStripeComponent
{
    public function componentDetails()
    {
        return [
            'name'        => 'Stripe',
            'description' => 'Stripe API Component implements the legacy checkout.js API',
        ];
    }

    public function onRun()
    {
        if (!$this->pubKey()) {
            return $this->renderPartial('@need-setup');
        }
        $this->addJs('assets/js/ajax.js', ['build' => 'StudioAzura.Stripe', 'defer', 'async']);
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

        $response = $this->stripeRequest('/charges', $postData);

        $params = [ $this, $response, $redirect ];
        // hook to handle routing after stripe_charge()
        if( ($results = Event::fire('studioazura.stripe.handleStripeChargeResponse', $params))) {
            return $results;
        }

        if (isset($response['error'])) {
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
