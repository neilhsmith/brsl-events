<?php

declare(strict_types=1);

namespace BRSL\Includes;

use BRSL\Includes\WPMem;
use BRSL\Admin\Admin;
use BRSL\Api\Api;
use BRSL\Frontend\Frontend;

if (!defined('ABSPATH')) exit;

class Main {
    protected string $slug;
    protected string $version;

    public function __construct() {
        $this->slug = BRSL_SLUG;
        $this->version = BRSL_VERSION;
    }

    private function initialize(): void {
        $admin = new Admin($this->slug, $this->version);
        $api = new Api($this->slug, $this->version);  
        $frontend = new Frontend($this->slug, $this->version);

        $wpMem = new WPMem();
    }

    public function run(): void {
        $this->initialize();
    }
}