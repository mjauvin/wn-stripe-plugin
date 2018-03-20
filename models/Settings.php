<?php namespace StudioAzura\Stripe\Models;

use Model;

/**
 * settings Model
 */
class Settings extends Model
{
    public $implement = [
        'System.Behaviors.SettingsModel',
    ];

    // A unique code
    public $settingsCode = 'studioazura_stripe_settings';

    // Reference to field configuration
    public $settingsFields = 'fields.yaml';
}
