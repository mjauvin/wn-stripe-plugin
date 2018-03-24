<?php return [
    'plugin' => [
        'name' => 'Stripe',
        'description' => 'Stripe Payment Widget integration',
    ],
    'settings' => [
        'label'       => 'Stripe',
        'description' => 'Configure Stripe API',
        'keywords'    => 'stripe payment settings config',
      ],
    'fields' => [
        'logo' => [
            'label' => 'Site Logo',
            'comment' => '(optional, square) <a target="_blank" href="https://stripe.com/docs/checkout#integration">more information</a>',
        ],
        'currency' => [
            'label' => 'Default Currency',
            'comment' => 'Please see <a target="_blank" href="https://stripe.com/docs/currencies#charge-currencies">supported currencies</a>',
        ],
        'is_billing_address' => [
            'label' => 'Collect Billing Address',
            'comment' => 'Whether billing address information should be collected',
        ],
        'is_shipping_address' => [
            'label' => 'Collect Shipping Address',
            'comment' => 'Whether shipping address information should be collected',
        ],
        'is_zip_code' => [
            'label' => 'Validate Billing Postal Code',
            'comment' => 'Whether billing postal code should be validated',
        ],
        'pub_key' => 'Publishable Key',
        'secret_key' => 'Secret Key',
    ],
    'tabs' => [
        'general' => 'General',
        'live' => 'Live keys',
        'test' => 'Test keys',
    ],
    'permissions' => [
        'manage_settings' => [
            'label' => 'Manage Stripe Payment Settings',
            'tab' => 'Stripe',
        ],
    ],
    'form' => [
        'submit_label' => 'Pay by credit card',
    ],
];
