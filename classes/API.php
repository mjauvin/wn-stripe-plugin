<?php namespace StudioAzura\Stripe\Classes;

use App;
use Stripe\StripeClient;
use StudioAzura\Stripe\Models\Settings;

class API
{
    protected static $client = null;

    protected static $testMode = false;

    public static function isTestMode() : bool
    {
        return App::environment() === 'dev' || self::$testMode;
    }

    protected static function secretKey()
    {
        return self::isTestMode() ? Settings::get('sk_test') : Settings::get('sk_live');
    }
    
    public static function getClient()
    {
        if (!$client = self::$client) {
            self::$client = $client = new StripeClient(['api_key' => self::secretKey()]);
        }

        return $client;
    }

    public static function createPromoCode($coupon, $code)
    {
        return self::getClient()->promotionCodes->create([
            "coupon" => $coupon,
            "code" => $code,
            "max_redemptions" => 1,
        ]);
    }
}

