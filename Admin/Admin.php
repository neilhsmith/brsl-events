<?php

declare(strict_types=1);

namespace BRSL\Admin;

use BRSL\Admin\MenuPage;
use BRSL\Admin\Settings;

if (!defined('ABSPATH')) exit;

class Admin {
    private string $slug;
    private string $version;

    public function __construct(string $slug, string $version) {
        $this->slug = $slug;
        $this->version = $version;

        $this->initializeHooks();

        $menuPage = new MenuPage();
        $settings = new Settings();
    }

    public static function isAdmin(): bool {
        return is_admin();
    }

    public function initializeHooks(): void {
        
    }
}