<?php

declare(strict_types=1);

namespace BRSL\Frontend\Shortcodes;

if (!defined('ABSPATH')) exit;

class ReservationsShortcode {

    private string $slug;
    private string $version;
    private string $scriptHandle;

    public function __construct(string $slug, string $version) {
        $this->slug = $slug;
        $this->version = $version;
        $this->scriptHandle = $slug . '-reservations';

        $this->initializeHooks();
    }

    public function initializeHooks(): void {
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
        add_shortcode($this->slug . '_reservations', array($this, 'doShortcode'));
    }

    public function enqueueScripts(): void {
        $bundleUrl = plugins_url('../app/build/bundle.js', __FILE__);
        wp_register_script(
            $this->scriptHandle,
            $bundleUrl,
            array(),
            $this->version,
            true
        );
    }

    public function doShortcode($atts = array()): void {
        wp_enqueue_script($this->scriptHandle);

        echo '<div id="reservations-app">Loading...</div>';
    }

}