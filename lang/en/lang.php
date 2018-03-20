<?php return [
    'plugin' => [
        'name' => 'Stripe',
        'description' => 'Stripe Payment integration',
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
