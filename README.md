# Stripe Payment Plugin

## Stripe Component

This plugin provides functionality to integrate [Stripe.com](https://Stripe.com) JS Payment Widget into an [OctoberCMS](https://octobercms.com) website.

## Requirements

This plugin requires:

- `{% scripts %}` placeholder within your layout. ([see the documentation](http://octobercms.com/docs/markup/tag-scripts))
- An account with [Stripe](https://stripe.com)

## Plugin Settings

This plugin creates a Settings menu item, found by navigating to ***Settings > Misc > Stripe***. This page allows the setting of common features, described below:

- Site Logo
	Optional square logo to be shown on the Stripe Payment Widget
- Default Currency
	Default currency used for the payments
- Enable Billing Address
	Should we collect billing address in Stripe widget
- Enable Shipping Address
	Should we collect shipping address in Stripe widget
- Enable Postal Code Validation
	Should we enable Stripe billing postal code validation
- Publishable keys (Live& Test mode)
	Stripe key used in the form
- Secret keys (Live& Test mode)
	Secret key used to complete the payment on the backend (must NOT be used in the form)

## Component Properties

The following properties can be used in the component inspector:

- isTestMode (allows choosing the test pub/secret keys for testing)
- currency (defaults to plugin setting)
- locale (defaults to auto)
- appName (defaults to config('app.name') )

## Usage

Example page code to use the default component markup:

    title = "test page"
    url = "/test"
    is_hidden = 0

    [viewBag]
    redirectUrl = "/thankyou"
    emailAddress = "user@domain.tld"
    orderAmount = 24.95
    orderDescription = "My viewBag Description"
    
    [stripe]
    isTestMode = true
    locale = "auto"
    ==
    <h2>My Order Form</h2>
    <hr>
    <form>
      <button id="stripeCheckout">Checkout</button>
    </form>
    
    {% component 'stripe' %}

Note: the button above and the viewBag properties are mandatory for the default component markup to work.

The emailAddress viewBag property is optional and will be used to pre-fill the email address in the Stripe widget if provided:

The component will get inserted directly in the layout using the `{% scripts %}` anonymous placeholder, not directly in the page's content.

## Variables

The following public variables are available in the component:

- pubKey
	Point to the publishable key for the component's mode (live or test)
- secretKey
	Point to the secret key for the component's mode (live or test)
- logo
	Optional, defaults to Stripe marketplace logo
- locale
	Can be overriden in component inspector (defaults to 'auto')
- currency
	Can be overriden in component inspector (defaults to global plugin setting)

## Default component markup

The default markup injects the stripe code in the scripts anonymous placeholder if a publishable key is defined:  
(see [Stripe checkout reference guide](https://stripe.com/docs/checkout#integration-custom))

    {% if not __SELF__.pubKey %}
      <div>
        <h2>{{ "A Stripe publishable key MUST be defined in the plugin settings"}}</h2>
      </div>
    {% else %}
      {% put scripts %}
      <script src='https://checkout.stripe.com/checkout.js'></script>
      <script>
        var checkout = StripeCheckout.configure({
          key: "{{ __SELF__.pubKey }}",
          image: "{{ __SELF__.logo }}",
          locale: "{{ __SELF__.locale }}",
          currency: "{{ __SELF__.currency }}",
          email:"{{viewBag.emailAddress}}",
        {% if __SELF__.billingAddress %}
          billingAddress: true,
        {% endif %}
        {% if __SELF__.shippingAddress %}
          shippingAddress: true,
        {% endif %}
        {% if __SELF__.billingAddress and __SELF__.zipCode %}
          zipCode: true,
        {% endif %}
          token: function(token, args) {
            $.request('{{ __SELF__ }}::onStripeCallback', {
                data: {'stripeData':token, 'invoiceData':get_order_data(), 'addressData':args, 'redirect':"{{viewBag.redirectUrl}}" },
            });
          }
        });
    
        $('#stripeCheckout').click(function(e) {
          order_data = get_order_data();
          checkout.open({
            name: "{{ appName | default(__SELF__.appName) }}",
            amount: order_data.amount * 100,
            description: order_data.description,
          });
          e.preventDefault();
        });
    
        function get_order_data() {
          return { 'currency':"{{ __SELF__.currency }}", 'amount':{{viewBag.orderAmount}}, 'description':"{{viewBag.orderDescription}}", }
        }
    
        window.addEventListener('popstate', function() {
          checkout.close();
        }, {passive: true});
      </script>
      {% endput %}
    {% endif %}

## Hooking into this plugin

You can hook into the following events from the php code block in pages/layout or partials (see page.htm code below):  

    [stripe]
    isTestMode = "true"
    ==
    function onInit()
    {
        Event::listen('studioazura.stripe.handleStripeCallback', function($self, $stripe, $invoice, $address, $redirect) {
            // Code to bypass the plugin and handle the payment
        });
    
        Event::listen('studioazura.stripe.setChargePostData', function($self, &$postData, $stripe, $invoice, $address) {
            // Code to modify the payment data before the payment request has been sent to stripe
        });
    
        Event::listen('studioazura.stripe.handleStripeChargeResponse', function($self, $response, $redirect) {
            // Code to handle the response after the payment request has been sent to stripe
        });
    }
    ==
    {# twig markup for the page here #}
    ...
***Note: the $postData variable above is a reference, so it can be modified from within your hook function.

Here is a complete working example on how to setup the postData for the stripe charges API call from a hook:

    title = "test payment page"
    url = "/payment"
    layout = "default"
    is_hidden = 0
    
    [viewBag]
    redirectUrl = "/thankyou"
    emailAddress = "user@domain.tld"
    orderAmount = 24.95
    orderDescription = "Order Description from viewBag"
    
    [stripe]
    isTestMode = true
    locale = "auto"
    ==
    function onInit()
    {
        // this is how you can dynamically set viewBag properties from within your PHP code block
        $this['viewBag']->setProperty('redirectUrl', '/order_completed');

        Event::listen('studioazura.stripe.setChargePostData', function($self, &$postData, $stripe, $invoice, $address) {
            // override amount and description; add key to metadata
            $postData['amount'] = 29.99 * 100;
            $postData['description'] = 'My Overriden Description';
            $postData['metadata']['new_info'] = 'new value';
        });
    }
    ==
    <style>
      input,label { display:block; margin-bottom: 0.5em; }
      button { margin-top:1em; }
    </style>
    <h2>My Order</h2>
    <form>
      <label>Item: {{viewBag.orderDescription}}</label>
      <label>Price: {{viewBag.orderAmount}}</label>
      <button id="stripeCheckout">Checkout</button>
    </form>
    {% component 'stripe' %}
