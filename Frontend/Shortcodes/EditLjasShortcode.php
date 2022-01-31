<?php

declare(strict_types=1);

namespace BRSL\Frontend\Shortcodes;

use BRSL\Models\SponsorSponsee;

if (!defined('ABSPATH')) exit;

class EditLjasShortcode {

    private string $slug;

    public function __construct(string $slug, string $version) {
        $this->slug = $slug;

        $this->initializeHooks();
    }

    public function initializeHooks(): void {
        add_shortcode($this->slug . '_edit_ljas', array($this, 'doShortcode'));
        add_action( 'gform_after_submission_8', array($this, 'handleSubmit'), 10, 2 );
    }

    public function handleSubmit($entry, $form): void {
        // TODO:
        // - ensure this current user a sponsor and the given sponsee_id does belong to this sponsor

        $sponseeId = $entry['12'];

        wp_update_user(array(
            'ID' => $sponseeId,
            'first_name' => $entry['1.3'],
            'last_name' => $entry['1.6'],
            'nickname' => $entry['4'],
            'display_name' => $entry['1.3'] . ' ' . $entry['1.6']
        ));
        update_user_meta($sponseeId, 'middle_name', $entry['1.4']);
        update_user_meta($sponseeId, 'cell_phone', $entry['2']);
        update_user_meta($sponseeId, 'address_1', $entry['3.1']);
        update_user_meta($sponseeId, 'address_2', $entry['3.2']);
        update_user_meta($sponseeId, 'address_city', $entry['3.3']);
        update_user_meta($sponseeId, 'address_state', $entry['3.4']);
        update_user_meta($sponseeId, 'address_zip', $entry['3.5']);
        update_user_meta($sponseeId, 'goes_by', $entry['4']);
        update_user_meta($sponseeId, 'school', $entry['5']);
        update_user_meta($sponseeId, 'tshirt_size', $entry['6']);
        update_user_meta($sponseeId, 'father_first', $entry['8.3']);
        update_user_meta($sponseeId, 'father_last', $entry['8.6']);
        update_user_meta($sponseeId, 'father_cell', $entry['10']);
        update_user_meta($sponseeId, 'father_email', $entry['11']);
    }

    public function doShortcode($atts = array()): void {
        $user_id = get_current_user_id();
        $sponsees = SponsorSponsee::getSponseesBySponsorId($user_id);

        if (empty($sponsees)) {
            echo '<p class="error">todo: better error message... There were no Sponsees found for this account.</p>';
            return;
        }
        ?>
        <div class="accordion accordion-flush" id="sponseesAccordion">
            <?php foreach ($sponsees as $sponsee):
                $sponseeId = $sponsee['ID'];
                $collapseId = 'collapse' . $sponseeId;
                $sponseeMeta = get_user_meta($sponseeId);

                $name = $sponsee['display_name'];
                $firstName = $sponsee['user_firstname'];
                $lastName = $sponsee['user_lastname'];
                $middleName = array_key_exists('middle_name', $sponseeMeta) ? $sponseeMeta['middle_name'][0] : '';
                $cellPhone = array_key_exists('cell_phone', $sponseeMeta) ? $sponseeMeta['cell_phone'][0] : '';
                $address1 = array_key_exists('address_1', $sponseeMeta) ? $sponseeMeta['address_1'][0] : '';
                $address2 = array_key_exists('address_2', $sponseeMeta) ? $sponseeMeta['address_2'][0] : '';
                $addressCity = array_key_exists('address_city', $sponseeMeta) ? $sponseeMeta['address_city'][0] : '';
                $addressState = array_key_exists('address_state', $sponseeMeta) ? $sponseeMeta['address_state'][0] : '';
                $addressZip = array_key_exists('address_zip', $sponseeMeta) ? $sponseeMeta['address_zip'][0] : '';
                $goesBy = array_key_exists('goes_by', $sponseeMeta) ? $sponseeMeta['goes_by'][0] : '';
                $school = array_key_exists('school', $sponseeMeta) ? $sponseeMeta['school'][0] : '';
                $tshirtSize = array_key_exists('tshirt_size', $sponseeMeta) ? $sponseeMeta['tshirt_size'][0] : '';
                $fatherFirst = array_key_exists('father_first', $sponseeMeta) ? $sponseeMeta['father_first'][0] : '';
                $fatherLast = array_key_exists('father_last', $sponseeMeta) ? $sponseeMeta['father_last'][0] : '';
                $fatherCell = array_key_exists('father_cell', $sponseeMeta) ? $sponseeMeta['father_cell'][0] : '';
                $fatherEmail = array_key_exists('father_email', $sponseeMeta) ? $sponseeMeta['father_email'][0] : '';
            ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="<?php echo $sponseeId; ?>">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#<?php echo $collapseId; ?>" aria-expanded="false" aria-controls="<?php echo $collapseId; ?>">
                            <?php echo $name; ?>
                        </button>
                    </h2>
                    <div id="<?php echo $collapseId; ?>" class="accordion-collapse collapse" aria-labelledby="<?php echo $sponseeId; ?>" data-bs-parent="#sponseesAccordion">
                        <div class="accordion-body">
                            <?php echo do_shortcode( '[gravityform id="8" title="false" description="false" ajax="true" field_values="'
                                . 'sponsee_id=' . $sponseeId
                                . '&first_name=' . $firstName
                                . '&last_name=' . $lastName
                                . '&middle_name=' . $middleName
                                . '&cell_phone=' . $cellPhone
                                . '&address_1=' . $address1
                                . '&address_2=' . $address2
                                . '&address_city=' . $addressCity
                                . '&address_state=' . $addressState
                                . '&address_zip=' . $addressZip
                                . '&goes_by=' . $goesBy
                                . '&school=' . $school
                                . '&tshirt_size='. $tshirtSize
                                . '&father_first=' . $fatherFirst
                                . '&father_last=' . $fatherLast
                                . '&father_cell=' . $fatherCell
                                . '&father_email=' . $fatherEmail
                            . '"]' ); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
    }
}