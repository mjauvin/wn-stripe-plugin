<?php return [
    'form' => [
        'submit_label' => 'Pay by credit card',
    ],
    'permissions' => [
        'manage_settings' => [
            'label' => 'Manage Stripe Payment Settings',
            'tab' => 'Stripe',
        ],
    ],
    'plugin' => [
        'name' => 'Stripe',
        'description' => 'Stripe Checkout integration',
    ],
    'properties' => [
        'isTestMode' => [
            'label' => 'Test Mode',
            'description' => 'enable stripe test mode',
        ],
        'currency' => [
            'label' => 'Currency',
            'description' => 'Currency used for the transactions',
        ],
        'locale' => [
            'label' => 'Locale',
            'description' => 'Locale to use with Stripe',
        ],
        'appName' => [
            'label' => 'Application Name',
            'description' => 'What to show as Stripe Pop-up Title',
        ],
        'cancelPage' => [
            'label' => 'Cancelled Payment Page',
            'description' => 'Provide a page for cancelled payments',
        ],
        'successPage' => [
            'label' => 'Completed Payment Page',
            'description' => 'Provide a page for completed payments',
        ],
    ],
    'settings' => [
        'label'       => 'Stripe',
        'description' => 'Configure Stripe API',
        'keywords'    => 'stripe payment settings config',

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
            'is_tax_collection' => [
                'label' => 'Collect Taxes',
                'comment' => 'Whether sales tax should be collected',
            ],
            'is_promotion_codes' => [
                'label' => 'Add promotional codes support',
                'comment' => 'Whether promotional codes are supported',
            ],
            'is_zip_code' => [
                'label' => 'Validate Billing Postal Code',
                'comment' => 'Whether billing postal code should be validated',
            ],
            'pub_key' => 'Publishable Key',
            'secret_key' => 'Secret Key',
        ],
    ],
    'stripe-error' => 'Error redirecting to Stripe checkout.',
    'tabs' => [
        'general' => 'General',
        'live' => 'Live keys',
        'test' => 'Test keys',
    ],
    'webhook' => [
        'fetchError' => 'Could not retrieve previously saved webhook.',
        'createError' => 'Could not create new Stripe webhook.',
        'created' => 'Stripe webhook successfully created.',
    ],
];
