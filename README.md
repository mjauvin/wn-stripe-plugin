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

## Usage

Example page code to use the default component markup:

    title = "test page"
    url = "/test"
    is_hidden = 0
    
    [stripe]
    isTestMode = true
    locale = "auto"
    ==
    <h2>My Order Form</h2>
    <hr>
    <form>
      <input type="hidden" id="redirectUrl" value="/thankyou">
      <input type="hidden" id="orderDescription" value="My Order Description">
      <input type="hidden" id="orderAmount" value="24.95">
      <button id="stripeCheckout">Checkout</button>
    </form>
    
    {% component 'stripe' %}

The hidden input fields and the button above are mandatory for the default component markup to work.

The emailAddress field as shown below is optional and will be used to pre-fill the email address in the Stripe widget if provided:

      <input type="hidden" id="emailAddress" value="user@domain.tld">

The component will get inserted directly in the layout using the `{% scripts %}` anonymous placeholder, not directly in the page's content.

## Variables

The following public variables are available in the component:

- appName
	Set to config(app.name)
- pubKey
	Point to the publishable key for the component's mode (live or test)
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
                data: {'stripeData':token, 'invoiceData':get_order_data(), 'addressData':args, 'redirect':$('#redirectUrl').val() },
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
          return {'amount': $('#orderAmount').val(), 'description': $('#orderDescription').val()}
        }
    
        window.addEventListener('popstate', function() {
          checkout.close();
        });
      </script>
      {% endput %}
    {% endif %}
