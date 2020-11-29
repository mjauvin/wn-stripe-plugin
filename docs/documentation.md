### Stripe Payment Integration 
 
#### Requirements 
 
This plugin requires: 
 
- `{% scripts %}` placeholder within your layout. ([see the documentation](http://octobercms.com/docs/markup/tag-scripts)) 
- An account with [Stripe](https://stripe.com) 
 
#### Plugin Settings 
 
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
 

#### Two components are available:

- A new SCA complient [Stripe Checkout Component](https://github.com/mjauvin/oc-stripe-plugin/tree/master/docs/checkout-component.md)
- A [legacy Stripe Component](https://github.com/mjauvin/oc-stripe-plugin/tree/master/docs/legacy-component.md) which is not goind to be updated
