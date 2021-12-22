<?php namespace StudioAzura\Stripe\Components;

use Event;
use Flash;
use Http;
use Log;
use Redirect;
use ValidationException;
use Cms\Classes\ComponentBase;
use StudioAzura\Stripe\Models\Settings;

abstract class BaseStripeComponent extends ComponentBase
{
    public $stripeUrl = "https://api.stripe.com/v1";

    public function defineProperties()
    {
        $currency = Settings::get('currency') ?: 'USD';

        return [
            'isTestMode' => [
                'title'             => 'studioazura.stripe::lang.properties.isTestMode.label',
                'description'       => 'studioazura.stripe::lang.properties.isTestMode.description',
                'type'              => 'checkbox',
                'default'           => false,
            ],
            'currency' => [
                'title'             => 'studioazura.stripe::lang.properties.currency.label',
                'description'       => 'studioazura.stripe::lang.properties.currency.description',
                'type'              => 'string',
                'default'           => $currency,
            ],
            'locale' => [
                'title'             => 'studioazura.stripe::lang.properties.locale.label',
                'description'       => 'studioazura.stripe::lang.properties.locale.description',
                'type'              => 'string',
                'default'           => 'auto',
            ],
            'appName' => [
                'title'             => 'studioazura.stripe::lang.properties.appName.label',
                'description'       => 'studioazura.stripe::lang.properties.appName.description',
                'type'              => 'string',
                'default'           => config('app.name'),
            ],
        ];
    }

    public function locale()
    {
        return $this->property('locale');
    }

    public function currency()
    {
        return strtoupper($this->property('currency'));
    }

    public function billingAddress()
    {
        return Settings::get('is_billing_address');
    }

    public function shippingAddress()
    {
        return Settings::get('is_shipping_address');
    }

    public function taxCollection()
    {
        return Settings::get('is_tax_collection');
    }

    public function promotionCodes()
    {
        return Settings::get('is_promotion_codes');
    }

    public function zipCode()
    {
        return Settings::get('is_zip_code');
    }

    public function appName()
    {
        return $this->property('appName');
    }

    public function logo()
    {
        $logo = Settings::get('logo');
        if ($logo) {
            return url(config('cms.storage.media.path') . $logo);
        } else {
            return 'https://stripe.com/img/documentation/checkout/marketplace.png';
        }
    }

    public function pubKey()
    {
        if ($this->property('isTestMode')) {
            return Settings::get('pk_test');
        } else {
            return Settings::get('pk_live');
        }
    }

    public function secretKey()
    {
        if ($this->property('isTestMode')) {
            return Settings::get('sk_test');
        } else {
            return Settings::get('sk_live');
        }
    }
}
