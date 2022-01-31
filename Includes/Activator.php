<?php

declare(strict_types=1);

namespace BRSL\Includes;

use BRSL\Models\Role;
use BRSL\Models\Reservation;
use BRSL\Models\SponsorSponsee;

if (!defined('ABSPATH')) exit;

class Activator {
    private const REQUIRED_PLUGINS = array(
    );

    /**
     * Runs when the plugin is activated.
     */
    public static function activate(array $configuration, string $configurationOptionName): void {
        // permission check
        if (!current_user_can('activate_plugins')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die("You don't have proper authorization to activate a plugin!");
        }

        self::checkDependencies();
        self::onActivation($configuration, $configurationOptionName);
    }

    /**
     * Deactivate the plugin if the required plugins are not activated.
     */
    private static function checkDependencies(): void {
        foreach (self::REQUIRED_PLUGINS as $pluginName => $pluginFilePath) {
            if (!is_plugin_active($pluginFilePath)) {
                deactivate_plugins(plugin_basename(__FILE__));
                wp_die("The BRSL plugin requires {$pluginName} plugin to be active!");
            }
        }
    }

    /**
     * The actual tasks performed during activation of the plugin.
     */
    public static function onActivation(array $configuration, string $configurationOptionName) {
        // store the configuration array to the options table
        add_option($configurationOptionName, $configuration);

        // create the roles
        $roles = Role::getRoles();
        foreach ($roles as $key => $value) {
            add_role($key, $value);
        }

        // create tabels
        Reservation::createTable();
        SponsorSponsee::createTable();
    }
}