<?php namespace StudioAzura\Stripe\Components;

use Cms\Classes\ComponentBase;
use StudioAzura\Stripe\Models\Settings;

class Stripe extends ComponentBase
{
    public $pub_key;
    public $locale;
    public $currency;
    public $name;
    public $logo;

    public function componentDetails()
    {
        return [
            'name'        => 'Stripe',
            'description' => 'Stripe API Component',
        ];
    }

    public function defineProperties()
    {
        $settings = new Settings;

        $currency = $settings::get('currency') ? $settings::get('currency') : 'USD';

        return [
            'isTestMode' => [
                'title'             => 'Test Mode',
                'description'       => 'enable stripe test mode',
                'type'              => 'checkbox',
            ],
            'currency' => [
                'title'             => 'Currency',
                'description'       => 'Currency used for the transactions',
                'type'              => 'string',
                'default'           => $currency,
            ],
            'locale' => [
                'title'             => 'Locale',
                'description'       => 'Locale to use with Stripe',
                'type'              => 'string',
                'default'           => config('app.locale'),
            ],
        ];
    }

    public function onRender()
    {
        $settings = new Settings;

        $this->test_mode = $this->property('isTestMode') ? true : false;
        $this->pub_key = $this->test_mode ? $settings::get('pk_test') : $settings::get('pk_live');
        $this->secret_key = $this->test_mode ? $settings::get('sk_test') : $settings::get('sk_live');

        $this->locale = $this->property('locale');
        $this->currency = $this->property('currency');
        $this->logo = $settings::get('logo');
        $this->name = config('app.name');
    }
}
