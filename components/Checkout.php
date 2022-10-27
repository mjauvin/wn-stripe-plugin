<?php namespace StudioAzura\Stripe\Components;

use Session;
use Stripe\StripeClient;
use StudioAzura\Stripe\Models\Settings;
use Url;

class Checkout extends BaseStripeComponent
{
    public function componentDetails()
    {
        return [
            'name'        => 'Stripe Checkout Component',
            'description' => 'Stripe.js API integration'
        ];
    }

    public function defineProperties()
    {
        $properties = parent::defineProperties();
        $properties['cancelUrl'] = [
            'title'             => 'studioazura.stripe::lang.properties.cancelUrl.label',
            'description'       => 'studioazura.stripe::lang.properties.cancelUrl.description',
            'type'              => 'string',
            'default'           => Settings::get('cancelUrl', '/payment/cancelled'),
        ];
        $properties['successUrl'] = [
            'title'             => 'studioazura.stripe::lang.properties.successUrl.label',
            'description'       => 'studioazura.stripe::lang.properties.successUrl.description',
            'type'              => 'string',
            'default'           => Settings::get('successUrl', '/payment/completed'),
        ];
        return array_except($properties, ['appName']);
    }

    public function onRun()
    {
        if (!$this->pubKey()) {
            return $this->renderPartial('@need-setup');
        }
    }

    public function onStripeCheckout()
    {
        $email = $this->page->viewBag->property('emailAddress') ?: post('emailAddress');
        $orderAmount = $this->page->viewBag->property('orderAmount') ?: post('orderAmount');
        $orderDescription = $this->page->viewBag->property('orderDescription') ?: post('orderDescription');
        $productId = $this->page->viewBag->property('productId') ?: post('productId');

        $data = array( 
          'payment_method_types' => ['card'],
          'mode' => 'payment',
          'line_items' => [[
              'price_data' => [
                  'product_data' => [
                      'name' => $orderDescription
                  ],
                  'currency' => $this->currency(),
                  'unit_amount' => (int) $orderAmount * 100,
                  'tax_behavior' => 'exclusive',
              ],
              'quantity' => 1,
          ]],
          'cancel_url' => Url::secure($this->property('cancelUrl')),
          'success_url' => Url::secure($this->property('successUrl')),
          'locale' => $this->locale(),
          'metadata' => [
              'product_id' => $productId,
          ],
        );

        if ($email) {
            $data['customer_email'] = $email;
        }

        if ($this->billingAddress()) {
            $data['billing_address_collection'] = 'required';
        }

        if ($this->shippingAddress()) {
            $data['shipping_address_collection'] = [
                'allowed_countries' => ['US','CA'],
            ];
        }

        if ($this->promotionCodes()) {
            $data['allow_promotion_codes'] = true;
        }

        if ($this->taxCollection()) {
            $data['automatic_tax'] = ['enabled' => true];
        }

        $stripe = new StripeClient(['api_key' => $this->secretKey()]);
        $stripeSession = $stripe->checkout->sessions->create($data);

        if (!isset($stripeSession->id)) {
            throw new \Exception('Could not create Stripe session.');
        }
        return $stripeSession;
    }
}
