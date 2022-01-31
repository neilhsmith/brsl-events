<?php

declare(strict_types=1);

namespace BRSL\Admin\Views;

use BRSL\Models\Role;
use BRSL\Models\SponsorSponsee;
use BRSL\Admin\Views\SponsorSponseesListTable;

if (!defined('ABSPATH')) exit;

class SponsorSponseesTab {

    public static function render(): void {
        self::renderAddSponsorSponseeFormSection();
        echo '<hr />';
        self::renderSponsorSponseesTableSection();
    }

    private static function renderAddSponsorSponseeFormSection(): void {
        // handle form submit
        if (isset($_POST['add_sponsor_sponsee'])) {
            if (!isset($_POST['add-sponsor-sponsee-nonce-field']) || !wp_verify_nonce($_POST['add-sponsor-sponsee-nonce-field'], 'add-sponsor-sponsee')) {
                print 'Sorry, your nonce did not verify.';
                exit;
            } else {
                $sponsor_id = $_POST['sponsor'];
                $sponsee_id = $_POST['sponsee'];

                SponsorSponsee::create($sponsor_id, $sponsee_id);
            }
        }

        // get form data & render the form
        $sponsors = get_users(array(
            'role' => Role::getSponsorKey(),
            'orderby' => 'user_nicename',
            'order' => 'ASC'
        ));
        $allSponsees = get_users(array(
            'role__in' => Role::getSponseeKeys(),
            'orderby' => 'user_nicename',
            'order' => 'ASC'
        ));
        $usedSponseeIds = SponsorSponsee::getSponseeIds();
        $sponsees = array_filter($allSponsees, function($sponsee) use ($usedSponseeIds) {
            return !in_array($sponsee->ID, $usedSponseeIds);
        });
        ?>
        <h3>Add a Sponsee to a Sponsor</h3>
        <form id="add-sponsor-sponsee" method="POST" action="">
            <label for="sponsor">Sponsor</label>
            <select id="sponsor" name="sponsor" required>
                <option value="">Select a Sponsor</option>
                <?php
                    foreach ($sponsors as $sponsor) {
                        echo '<option value="' . $sponsor->ID . '">' . $sponsor->display_name . '</option>';
                    }
                ?>
            </select>
            <label for="sponsee">Sponsee</label>
            <select id="sponsee" name="sponsee" required>
                <option value="">Select a Sponsee</option>
                <?php
                    foreach ($sponsees as $sponsee) {
                        echo '<option value="' . $sponsee->ID . '">' . $sponsee->display_name . '</option>';
                    }
                ?>
            </select>
            <input type="hidden" name="add_sponsor_sponsee" />
            <?php
                wp_nonce_field('add-sponsor-sponsee', 'add-sponsor-sponsee-nonce-field');
                submit_button('Add Sponsee to Sponsor');
            ?>
        </form>
        <?php
    }

    private static function renderSponsorSponseesTableSection(): void {
        $sponsorSponseesListTable = new SponsorSponseesListTable();
        
        echo '<h3>Sponsors Listing</h3>';
        echo '<form method="post">';
        $sponsorSponseesListTable->prepare_items();
        $sponsorSponseesListTable->display();
        echo '</form>';
    }

}