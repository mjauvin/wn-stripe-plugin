<?php namespace StudioAzura\Stripe\Components;

use Session;
use Stripe\StripeClient;
use StudioAzura\Stripe\Models\Settings;

use Cms\Classes\Controller;
use Cms\Classes\Theme;

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

        $properties['cancelPage'] = [
            'title'             => 'studioazura.stripe::lang.properties.cancelPage.label',
            'description'       => 'studioazura.stripe::lang.properties.cancelPage.description',
            'type'              => 'dropdown',
            'options'           => $this->getCmsPagesOptions(),
            'emptyOption'       => '-- Select Page --',
            'required'          => true,
            'showExternalParam' => false,
        ];

        $properties['successPage'] = [
            'title'             => 'studioazura.stripe::lang.properties.successPage.label',
            'description'       => 'studioazura.stripe::lang.properties.successPage.description',
            'type'              => 'dropdown',
            'options'           => $this->getCmsPagesOptions(),
            'emptyOption'       => '-- Select Page --',
            'required'          => true,
            'showExternalParam' => false,
        ];

        return array_except($properties, ['appName']);
    }

    public function onRun()
    {
        if (!$this->pubKey()) {
            return $this->renderPartial('@need-setup');
        }
    }

    public function getCmsPagesOptions()
    {
        $theme = Theme::getActiveTheme();
        $pages = $theme->listPages();
        $options = [];

        foreach ($pages as $page) {
            $value = sprintf("[%s]", $page->baseFileName);
            $options[$page->baseFileName] = strlen($page->title) ? sprintf("%s - %s", $page->title, $value) : $value;
        }
        ksort($options);

        return $options;
    }

    public function onStripeCheckout()
    {
        $controller = Controller::getController() ?? new Controller;

        $email = $this->page->viewBag->property('emailAddress') ?: post('emailAddress');
        $orderAmount = $this->page->viewBag->property('orderAmount') ?: post('orderAmount');
        $orderDescription = $this->page->viewBag->property('orderDescription') ?: post('orderDescription');

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
          'cancel_url' => $controller->pageUrl($this->property('cancelPage'), false),
          'success_url' => $controller->pageUrl($this->property('successPage'), false),
          'locale' => $this->locale(),
          'metadata' => post('meta', []),
          'payment_intent_data' => [
              'metadata' => post('meta', []),
              'description' => $orderDescription,
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
