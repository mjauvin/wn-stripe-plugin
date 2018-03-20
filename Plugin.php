<?php namespace StudioAzura\Stripe;

use Backend;
use System\Classes\PluginBase;

/**
 * Stripe Plugin Information File
 */
class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name'        => 'Stripe',
            'description' => 'Stripe Payment integration',
            'author'      => 'StudioAzura',
            'icon'        => 'icon-credit-card',
            'homepage'    => 'https://studioazura.com',
        ];
    }

    public function registerComponents()
    {
        return [
            'StudioAzura\Stripe\Components\Stripe' => 'stripe',
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Stripe',
                'description' => 'Configure Stripe API',
                'icon'        => 'icon-credit-card',
                'class'       => 'StudioAzura\Stripe\Models\Settings',
                'keywords'    => 'stripe payment settings config',
                'order'       => 500,
                'permissions' => ['studioazura.stripe.manage_settings'],
              ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'studioazura.stripe.manage_settings' => [
                'label' => 'Manage Stripe Payment Settings',
                'tab' => 'Stripe',
            ]
        ];
    }
}
