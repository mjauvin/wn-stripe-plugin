<?php namespace StudioAzura\Stripe;

use Backend;
use Flash;
use Lang;
use Stripe\StripeClient;
use System\Classes\PluginBase;
use Url;

/**
 * Stripe Plugin Information File
 */
class Plugin extends PluginBase
{
    public static $webhook = '/stripe/checkout/session/completed';

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
        \StudioAzura\Stripe\Models\Settings::extend(function ($model) {
            $model->bindEvent('model.beforeSave', function () use ($model) {
                if (!isset($model->attributes['value'])) {
                    return;
                }
                $originals = json_decode($model->attributes['value']);
                foreach(['live', 'test'] as $mode) {
                    $key = 'sk_' . $mode;
                    $we_key = 'webhook_' . $mode;
                    $sk_value = \Input::get('Settings.' . $key);
                    $we_value = \Input::get('Settings.' . $we_key);
                    if ($sk_value && $originals->$key != $sk_value) {
                        $stripe = new StripeClient(['api_key' => $sk_value]);
                        if ($we_value) {
                            try {
                                $hook = $stripe->webhookEndpoints->retrieve($we_value, []);
                            } catch (\Exception $e) {
                                Flash::warning(Lang::get('studioazura.stripe::lang.webhook.fetchError'));
                                $we_value = null;
                            }
                        }
                        if (!$we_value) {
                            try {
                                $hook = $stripe->webhookEndpoints->create([
                                    'url' => Url::secure(static::$webhook),
                                    'enabled_events' => ['checkout.session.completed'],
                                    'description' => Lang::get('studioazura.stripe::lang.plugin.description'),
                                ]);
                            } catch (\Exception $e) {
                                trace_log($e);
                                $hook = null;
                            }
                            if (!$hook) {
                                Flash::error(Lang::get('studioazura.stripe::lang.webhook.createError'));
                            }
                        }
                        if ($hook && isset($hook->id) && $hook->id != $we_value) {
                            $model->setSettingsValue($we_key, $hook->id);
                            Flash::success(Lang::get('studioazura.stripe::lang.webhook.created'));
                        }
                    }
                }
            });
        });
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
