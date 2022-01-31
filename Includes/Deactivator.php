<?php

declare(strict_types=1);

namespace BRSL\Includes;

if (!defined('ABSPATH')) exit;

class Deactivator {
    public static function deactivate(string $configurationOptionName): void {
        // permission check
        if (!current_user_can('activate_plugins')) {
            wp_die('You don\'t have proper authorization to deactivation a plugin!');
        }

        self::onDeactivation($configurationOptionName);
    }
    
    /**
     * The actual tasks performed during deactivation of the plugin.
     */
    public static function onDeactivation(string $configurationOptionName) {
        // delete the configuration option array
        delete_option($configurationOptionName);
    }
}