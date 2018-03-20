<?php namespace StudioAzura\Stripe\Components;

use Cms\Classes\ComponentBase;
use StudioAzura\Stripe\Models\Settings;

class Stripe extends ComponentBase
{
    public $pub_key;
    public $locale;
    public $currency;
    public $app_name;
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
                'default'           => false,
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
                'default'           => 'auto',
            ],
        ];
    }

    public function onRun()
    {
        $settings = new Settings;

        $this->pub_key = $this->property('isTestMode') ? $settings::get('pk_test') : $settings::get('pk_live');

        $this->secret_key = $this->test_mode ? $settings::get('sk_test') : $settings::get('sk_live');

        if ($settings::get('logo')) {
            $this->logo = url(config('cms.storage.media.path') . $settings::get('logo'));
        }
        $this->app_name = config('app.name');
    }

    public function onRender()
    {
        $this->locale = $this->property('locale');
        $this->currency = $this->property('currency');
    }
}
