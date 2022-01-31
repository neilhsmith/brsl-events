<?php

declare(strict_types=1);

namespace BRSL\Admin;

use BRSL\Admin\Views\AppConfigTab;
use BRSL\Admin\Views\ReservationsTab;
use BRSL\Admin\Views\SponsorSponseesTab;
use BRSL\Admin\ExportReservations;

class MenuPage {
    
    private static string $menuSlug = BRSL_SLUG . '-event-settings';

    public function __construct() {
        $this->initializeHooks();

        // new this up just so actions are hooked into to handle form submits
        new ExportReservations();
    }

    public function initializeHooks(): void {
        add_action('admin_menu', array($this, 'addMenuPage'));
    }

    public function addMenuPage(): void {
        add_menu_page(
            'BRSL Event Settings',
            'BRSL Events',
            'manage_options',
            self::$menuSlug,
            array($this, 'settingsPageContent'),
            'dashicons-smiley',
            51
        );
    }

    public function settingsPageContent(): void {
        if (!current_user_can('manage_options'))
            return;

        $tab = isset($_GET['tab']) ? $_GET['tab'] : '';


        settings_errors(self::$menuSlug);
        ?>
        <div class="wrap">
            <h2>BRSL Event Settings & Reports</h2>
            <div class="nav-tab-wrapper">
                <a href="?page=<?php echo self::$menuSlug; ?>" class="nav-tab <?php echo $tab === '' ? 'nav-tab-active' : ''; ?>">Settings</a>
                <a href="?page=<?php echo self::$menuSlug . '&tab=sponsor-sponsees'; ?>" class="nav-tab <?php echo $tab === 'sponsor-sponsees' ? 'nav-tab-active' : ''; ?>">Sponsor / LJAs</a>
                <a href="?page=<?php echo self::$menuSlug . '&tab=reservations'; ?>" class="nav-tab <?php echo $tab === 'reservations' ? 'nav-tab-active' : ''; ?>">Reservations</a>
            </div>
            <div class="tabs-content">
                <div class="postbox">
                    <div class="inside">
                        <?php switch($tab) {
                            case '':
                                AppConfigTab::render();
                                break;
                            case 'sponsor-sponsees':
                                SponsorSponseesTab::render();
                                break;
                            case 'reservations':
                                ReservationsTab::render();
                                break;
                            default:
                                echo 'Error...';
                                break;
                        } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

}