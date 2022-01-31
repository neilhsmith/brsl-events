<?php

declare(strict_types=1);

namespace BRSL\Admin\Views;

if (!defined('ABSPATH')) exit;

class AppConfigTab {

    public static function render(): void {
        ?>
        <h3>BRSL Event Settings</h3>
        <form method="post" action="options.php">
            <?php
            settings_fields('brsl_options');
            do_settings_sections('brsl');
            submit_button();
            ?>
        </form>
        <?php
    }
    
}