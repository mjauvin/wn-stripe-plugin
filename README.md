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
	  <input type=hidden id="redirect_url" value="/thankyou">
	  <input type=text id="order_description" value="My Order Description">
	  <input type=text id="order_amount" value="24.95">
	  <button id="stripe_checkout">Checkout</button>
	</form>

	{% component 'stripe' %}

The component will get inserted directly in the layout using the `{% scripts %}` anonymous placeholder, not directly in the page's content.

## Variables

The following variables are available in the component:

- app_name
	Set to config(app.name)
- pub_key
	Point to the publishable key for the component's mode (live or test)
- logo
	Optional, defaults to Stripe marketplace logo
- locale
	Can be overriden in component inspector (defaults to 'auto')
- currency
	Can be overriden in component inspector (defaults to global plugin setting)

## Default component markup

The default markup injects the stripe script in the scripts anonymous placeholder.

	{% if not __SELF__.pub_key %}
	  <div>
	    <h2>{{ "A Stripe publishable key MUST be defined in the plugin settings"}}</h2>
	  </div>
	{% else %}
	  {% put scripts %}
	  <script>
	    var checkout = StripeCheckout.configure({
	      key: "{{ __SELF__.pub_key }}",
	      image: "{{ __SELF__.logo }}",
	      locale: "{{ __SELF__.locale }}",
	      currency: "{{ __SELF__.currency }}",
	      token: function(results) {
		$.request('{{ __SELF__ }}::onStripeCallback', {
		    data: {'stripeData': results, 'invoiceData': get_order_data(), 'redirect': $('#redirect_url').val() },
		});
	      }
	    });

	    $('#stripe_checkout').click(function(e) {
	      order_data = get_order_data();
	      checkout.open({
		name: "{{ app_name | default(__SELF__.app_name) }}",
		amount: order_data.amount * 100,
		description: order_data.description,
	      });
	      e.preventDefault();
	    });

	    function get_order_data() {
	      return {'amount': $('#order_amount').val(), 'description': $('#order_description').val()}
	    }

	    window.addEventListener('popstate', function() {
	      checkout.close();
	    });
	  </script>
	  {% endput %}
	{% endif %}
