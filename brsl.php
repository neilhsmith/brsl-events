<?php

/**
 * @wordpress-plugin
 * Plugin Name: BRSL
 * Version:     0.0.1
 * Description: Provides the custom functionality used for BRSL events.
 * Author:      Neil Smith
 */

declare(strict_types=1);

namespace BRSL;

use BRSL\Includes\Activator;
use BRSL\Includes\Deactivator;
use BRSL\Includes\Main;

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'Autoloader.php';

require_once plugin_dir_path(__FILE__) . 'Vendor/stripe-php-7.100.0/init.php';

define('BRSL_VERSION', '2.0.3');
define('BRSL_SLUG', 'brsl');

/**
 * Configuration data & the option's name
 * 
 * - version: Version of the plugin when it was last activated. Useful when updating the plugin & db.
 * 
 * (This data is stored via the Options API. Useful for backend settings which the plugin 
 * uses. This is not admin 'frontend' configuration which uses the Settings API.)
 */
$configurationOptionName = BRSL_SLUG . '-configuration';
$configuration = array(
    'version'    => BRSL_SLUG
);

register_activation_hook(__FILE__, function() use($configuration, $configurationOptionName) {
    Activator::activate($configuration, $configurationOptionName);
});

register_deactivation_hook(__FILE__, function() use($configurationOptionName) {
    Deactivator::deactivate($configurationOptionName);
});

function runPlugin(): void {
    $plugin = new Main();
    $plugin->run();
}
runPlugin();

