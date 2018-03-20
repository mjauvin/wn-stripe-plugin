<?php namespace StudioAzura\Stripe;

use Lang;
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
            'name'        => 'studioazura.stripe::lang.plugin.name',
            'description' => 'studioazura.stripe::lang.plugin.description',
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

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'translate' => 'Lang::get',
            ],
        ];
    }
}
