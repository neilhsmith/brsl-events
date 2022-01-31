<?php

declare(strict_types=1);

namespace BRSL\Frontend;

use BRSL\Frontend\Shortcodes\EditLjasShortcode;
use BRSL\Frontend\Shortcodes\ReservationsShortcode;

if (!defined('ABSPATH')) exit;

class Frontend {

    private string $slug;
    private string $version;

    public function __construct(string $slug, string $version) {
        $this->slug = $slug;
        $this->version = $version;

        $this->initializeModules();
        $this->initializeHooks();
    }

    private function initializeModules(): void {
        new EditLjasShortcode($this->slug, $this->version);
        new ReservationsShortcode($this->slug, $this->version);
    }

    private function initializeHooks(): void {
        add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'), 99);
    }

    public function enqueueScripts(): void {
        wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css', array(), '5.1.3', 'all');
        wp_enqueue_style($this->slug . '-frontend', plugin_dir_url( __FILE__ ) . '../assets/frontend.css', array(), $this->version, 'all');
        
        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', array(), '5.1.3', false);
    }

}