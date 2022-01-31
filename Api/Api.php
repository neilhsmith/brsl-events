<?php

declare(strict_types=1);

namespace BRSL\Api;

use BRSL\Api\Controllers\AppConfigController;
use BRSL\Api\Controllers\SponsorController;
use BRSL\Api\Controllers\LjaController;
use BRSL\Api\Controllers\ReservationController;
use BRSL\Admin\Settings;

if (!defined('ABSPATH')) exit;

class Api {
    private string $slug;
    private string $version;
    private string $namespace;

    public function __construct(string $slug, string $version) {
        $this->slug = $slug;
        $this->version = $version;

        $this->namespace = $slug . '/v1';

        add_action('init', array($this, 'init'));
        add_action('rest_api_init', array($this, 'restApiInit'));
    }

    public function init() {
        // todo: probably only need to enqueue this script on the reservation app's shortcode.
        //         but its useful here while testing so i can easily grab the nonce and auth cookie whenever i need.
        $testMode = Settings::getStripeTestModeEnabled();
        if ($testMode)
            $stripePublishableKey = Settings::getStripeTestPublishableKey();
        else
            $stripePublishableKey = Settings::getStripePublishableKey();

        wp_localize_script( 'wp-api', 'wpApiSettings', array( 
            'root' => esc_url_raw( rest_url() ), 
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'stripePublishableKey' => $stripePublishableKey
        ) );
        wp_enqueue_script('wp-api');
    }

    public function restApiInit() {
        $actions = array(
            new AppConfigController($this->namespace),
            new SponsorController($this->namespace),
            new LjaController($this->namespace),
            new ReservationController($this->namespace)
        );

        foreach ($actions as $action) {
            $action->register_routes();
        }
    }
}