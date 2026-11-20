<?php namespace StudioAzura\Stripe;

use Backend;
use Flash;
use Lang;
use Stripe\StripeClient;
use StudioAzura\Stripe\Models\Settings;
use System\Classes\PluginBase;
use Url;

/**
 * Stripe Plugin Information File
 */
class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'studioazura.stripe::lang.plugin.name',
            'description' => 'studioazura.stripe::lang.plugin.description',
            'author'      => 'StudioAzura',
            'icon'        => 'icon-credit-card',
            'homepage'    => 'https://studioazura.com',
        ];
    }

    public function boot()
    {
        Settings::extend(function () {
            $this->addDynamicMethod('getWebHook', function ($mode) {
                $sk = Settings::get('sk_' . $mode);
                $stripe = new StripeClient(['api_key' => $sk]);
                foreach($stripe->webhookEndpoints->all() as $wh) {
                    if (in_array('checkout.session.completed', $wh->enabled_events) && $wh->status === 'enabled') {
                        return $wh;
                    }
                }
                return null;
            });

            $this->addDynamicMethod('getAppUrl', function ($mode, $path = 'stripe/checkout/session/completed') {
                if ($mode === 'test') {
                    $mode = "dev";
                }
                $appUrl = array_get(array_flip(config('environment.hosts')), $mode);
                $url = sprintf("https://%s/%s", $appUrl, $path);

                return $url;
            });

            $this->bindEvent('model.beforeSave', function () {
                if (!isset($this->attributes['value'])) {
                    return;
                }
                $originals = json_decode($this->attributes['value']);
                foreach(['live', 'test'] as $mode) {
                    $sk_value = \Input::get('Settings.sk_'.$mode);
                    $we_value = \Input::get('Settings.webhook_'.$mode);
                    $wh_sec = \Input::get('Settings.whsec_'.$mode);
                    if ($sk_value) {
                        $stripe = new StripeClient(['api_key' => $sk_value]);
                        if ($we_value) {
                            try {
                                $hook = $stripe->webhookEndpoints->retrieve($we_value, []);
                            } catch (\Exception $e) {
                                Flash::warning(Lang::get('studioazura.stripe::lang.webhook.fetchError'));
                                $we_value = null;
                            }
                        }
                        if (!($we_value && $wh_sec)) {
                            try {
                                $oldHook = $this->getWebHook($mode)?->delete();
                                $hook = $stripe->webhookEndpoints->create([
                                    'url' => $this->getAppUrl($mode),
                                    'enabled_events' => ['checkout.session.completed'],
                                    'description' => Lang::get('studioazura.stripe::lang.plugin.description'),
                                ]);
                                if ($hook && isset($hook->secret)) {
                                    $this->setSettingsValue('whsec_'.$mode, $hook->secret);
                                }
                            } catch (\Exception $e) {
                                traceLog($e);
                                $hook = null;
                            }
                            if (!$hook) {
                                Flash::error(Lang::get('studioazura.stripe::lang.webhook.createError'));
                            }
                        }
                        if ($hook && isset($hook->id) && $hook->id != $we_value) {
                            $this->setSettingsValue('webhook_'.$mode, $hook->id);
                            Flash::success(Lang::get('studioazura.stripe::lang.webhook.created'));
                        }
                    }
                }
            });
        }, true);
    }

    public function registerComponents()
    {
        return [
            'StudioAzura\Stripe\Components\Stripe' => 'stripe',
            'StudioAzura\Stripe\Components\Checkout' => 'stripeCheckout',
        ];
    }

    public function registerPageSnippets()
    {   
        return [
            'StudioAzura\Stripe\Components\Stripe' => 'stripe',
            'StudioAzura\Stripe\Components\Checkout' => 'stripeCheckout',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'studioazura.stripe::lang.settings.label',
                'description' => 'studioazura.stripe::lang.settings.description',
                'icon'        => 'icon-credit-card',
                'class'       => 'StudioAzura\Stripe\Models\Settings',
                'keywords'    => 'studioazura.stripe::lang.settings.keywords',
                'order'       => 500,
                'permissions' => ['studioazura.stripe.manage_settings'],
              ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'studioazura.stripe.manage_settings' => [
                'label' => 'studioazura.stripe::lang.permissions.manage_settings.label',
                'tab' => 'studioazura.stripe::lang.permissions.manage_settings.tab',
            ]
        ];
    }
}
