<?php

declare(strict_types=1);

namespace BRSL\Admin;

if (!defined('ABSPATH')) exit;

class Settings {

    public function __construct() {
        $this->initializeHooks();
    }

    public function initializeHooks(): void {
        add_action('admin_init', array($this, 'initializeOptions'));
    }

    // SETUP -------------------------------------------------------------------

    public function initializeOptions(): void {
        $this->setupSeatOptions();
        $this->setupEnableOptions();
        $this->setupRelinquishOptions();
        $this->setupStripeOptions();
    }

    public function setupSeatOptions(): void {
        add_settings_section(
            'brsl_seat_options_section', 
            'Seat Options', 
            null, 
            'brsl'
        );

        add_settings_field(
            'total_available_seats',
            'Total Available Seats',
            array($this, 'numberCallback'),
            'brsl',
            'brsl_seat_options_section',
            array(
                'option_name' => 'seat_settings',
                'setting_id' => 'total_available_seats'
            )
        );

        add_settings_field(
            'additional_purchasable_seats',
            'Additional Purchasable Seats',
            array($this, 'numberCallback'),
            'brsl',
            'brsl_seat_options_section',
            array(
                'option_name' => 'seat_settings',
                'setting_id' => 'additional_purchasable_seats'
            )
        );

        add_settings_field(
            'senior_prepaid_seats',
            'Senior Prepaid Seats',
            array($this, 'numberCallback'),
            'brsl',
            'brsl_seat_options_section',
            array(
                'option_name' => 'seat_settings',
                'setting_id' => 'senior_prepaid_seats'
            )
        );

        add_settings_field(
            'junior_prepaid_seats',
            'Junior Prepaid Seats',
            array($this, 'numberCallback'),
            'brsl',
            'brsl_seat_options_section',
            array(
                'option_name' => 'seat_settings',
                'setting_id' => 'junior_prepaid_seats'
            )
        );

        add_settings_field(
            'sophomore_prepaid_seats',
            'Sophomore Prepaid Seats',
            array($this, 'numberCallback'),
            'brsl',
            'brsl_seat_options_section',
            array(
                'option_name' => 'seat_settings',
                'setting_id' => 'sophomore_prepaid_seats'
            )
        );

        add_settings_field(
            'freshman_prepaid_seats',
            'Freshman Prepaid Seats',
            array($this, 'numberCallback'),
            'brsl',
            'brsl_seat_options_section',
            array(
                'option_name' => 'seat_settings',
                'setting_id' => 'freshman_prepaid_seats'
            )
        );

        register_setting('brsl_options', 'seat_settings', array(
            'type' => 'array',
            'show_in_rest' => false,
            'sanitize_callback' => array($this, 'sanitizeSeatSettings')
        ));
    }

    public function setupEnableOptions(): void {
        add_settings_section(
            'brsl_enable_options_section', 
            'Enable Options', 
            null,
            'brsl'
        );

        add_settings_field(
            'senior_enable_date',
            'Senior Enable Date',
            array($this, 'dateCallback'),
            'brsl',
            'brsl_enable_options_section',
            array(
                'option_name' => 'enable_settings',
                'setting_id' => 'senior_enable_date'
            )
        );

        add_settings_field(
            'junior_enable_date',
            'Junior Enable Date',
            array($this, 'dateCallback'),
            'brsl',
            'brsl_enable_options_section',
            array(
                'option_name' => 'enable_settings',
                'setting_id' => 'junior_enable_date'
            )
        );

        add_settings_field(
            'sophomore_enable_date',
            'Sophomore Enable Date',
            array($this, 'dateCallback'),
            'brsl',
            'brsl_enable_options_section',
            array(
                'option_name' => 'enable_settings',
                'setting_id' => 'sophomore_enable_date'
            )
        );

        add_settings_field(
            'freshman_enable_date',
            'Freshman Enable Date',
            array($this, 'dateCallback'),
            'brsl',
            'brsl_enable_options_section',
            array(
                'option_name' => 'enable_settings',
                'setting_id' => 'freshman_enable_date'
            )
        );

        register_setting('brsl_options', 'enable_settings', array(
            'sanitize_callback' => array($this, 'sanitizeEnableSettings')
        ));
    }

    public function setupRelinquishOptions(): void {
        add_settings_section(
            'brsl_relinquish_options_section', 
            'Relinquish Options', 
            null,
            'brsl'
        );

        add_settings_field(
            'senior_relinquish_date',
            'Senior Relinquish Date',
            array($this, 'dateCallback'),
            'brsl',
            'brsl_relinquish_options_section',
            array(
                'option_name' => 'relinquish_settings',
                'setting_id' => 'senior_relinquish_date'
            )
        );

        add_settings_field(
            'junior_relinquish_date',
            'Junior Relinquish Date',
            array($this, 'dateCallback'),
            'brsl',
            'brsl_relinquish_options_section',
            array(
                'option_name' => 'relinquish_settings',
                'setting_id' => 'junior_relinquish_date'
            )
        );

        add_settings_field(
            'sophomore_relinquish_date',
            'Sophomore Relinquish Date',
            array($this, 'dateCallback'),
            'brsl',
            'brsl_relinquish_options_section',
            array(
                'option_name' => 'relinquish_settings',
                'setting_id' => 'sophomore_relinquish_date'
            )
        );

        add_settings_field(
            'freshman_relinquish_date',
            'Freshman Relinquish Date',
            array($this, 'dateCallback'),
            'brsl',
            'brsl_relinquish_options_section',
            array(
                'option_name' => 'relinquish_settings',
                'setting_id' => 'freshman_relinquish_date'
            )
        );
        
        register_setting('brsl_options', 'relinquish_settings', array(
            'sanitize_callback' => array($this, 'sanitizeRelinquishSettings')
        ));
    }

    public function setupStripeOptions(): void {
        add_settings_section(
            'brsl_stripe_options_section', 
            'Stripe Options', 
            null,
            'brsl'
        );

        add_settings_field(
            'test_mode_enabled',
            'Test Mode Enabled',
            array($this, 'checkboxCallback'),
            'brsl',
            'brsl_stripe_options_section',
            array(
                'option_name' => 'stripe_settings',
                'setting_id' => 'test_mode_enabled'
            )
        );

        add_settings_field(
            'stripe_test_publishable_key',
            'Stripe Test Publishable Key',
            array($this, 'textCallback'),
            'brsl',
            'brsl_stripe_options_section',
            array(
                'option_name' => 'stripe_settings',
                'setting_id' => 'stripe_test_publishable_key'
            )
        );

        add_settings_field(
            'stripe_test_secret_key',
            'Stripe Test Secret Key',
            array($this, 'passwordCallback'),
            'brsl',
            'brsl_stripe_options_section',
            array(
                'option_name' => 'stripe_settings',
                'setting_id' => 'stripe_test_secret_key'
            )
        );

        add_settings_field(
            'stripe_publishable_key',
            'Stripe Publishable Key',
            array($this, 'textCallback'),
            'brsl',
            'brsl_stripe_options_section',
            array(
                'option_name' => 'stripe_settings',
                'setting_id' => 'stripe_publishable_key'
            )
        );

        add_settings_field(
            'stripe_secret_key',
            'Stripe Secret Key',
            array($this, 'passwordCallback'),
            'brsl',
            'brsl_stripe_options_section',
            array(
                'option_name' => 'stripe_settings',
                'setting_id' => 'stripe_secret_key'
            )
        );

        register_setting('brsl_options', 'stripe_settings', array(
            'sanitize_callback' => array($this, 'sanitizeStripeSettings')
        ));
    }

    // SANITIZE ----------------------------------------------------------------

    public function sanitizeSeatSettings(array $input): array {
        if ($input === null) return array();

        $output = array();
        foreach ($input as $key => $value) {
            $output[$key] = sanitize_text_field($input[$key]);
        }

        return $output;
    }

    public function sanitizeEnableSettings(array $inputs): array {
        return $inputs;
    }

    public function sanitizeRelinquishSettings(array $inputs): array {
        return $inputs;
    }

    public function sanitizeStripeSettings(array $inputs): array {
        // TODO: sanitize stripe inputs if needed
        return $inputs;
    }

    // HELPER CALLBACKS --------------------------------------------------------

    public function numberCallback(array $args): void {
        $option_name = $args['option_name'];
        $setting_id = $args['setting_id'];
        $value = !isset(get_option($option_name)[$setting_id]) ? "" : get_option($option_name)[$setting_id];

        printf('<input type="number" name="%s[%s]" value="%s" min="0">',
            $option_name, $setting_id, $value
        );
    }

    public function dateCallback(array $args): void {
        $option_name = $args['option_name'];
        $setting_id = $args['setting_id'];

        $id = $setting_id . '-datepicker';
        $value = empty(get_option($option_name)[$setting_id]) ? "" : get_option($option_name)[$setting_id];

        printf('<input type="date" id="%s" name="%s[%s]" value="%s" class="date">',
            $id, $option_name, $setting_id, $value
        );
    }

    public function checkboxCallback(array $args): void {
        $option_name = $args['option_name'];
        $setting_id = $args['setting_id'];
        $value = empty(get_option($option_name)[$setting_id]) ? "" : get_option($option_name)[$setting_id];

        printf('<input type="checkbox" name="%s[%s]" ' . checked($value, 'on', false) . '>',
            $option_name, $setting_id
        );
    }

    public function textCallback(array $args): void {
        $option_name = $args['option_name'];
        $setting_id = $args['setting_id'];
        $value = empty(get_option($option_name)[$setting_id]) ? "" : get_option($option_name)[$setting_id];

        printf('<input type="text" name="%s[%s]" value="%s">',
            $option_name, $setting_id, $value
        );
    }

    public function passwordCallback(array $args): void {
        $option_name = $args['option_name'];
        $setting_id = $args['setting_id'];
        $value = empty(get_option($option_name)[$setting_id]) ? "" : get_option($option_name)[$setting_id];

        printf('<input type="password" name="%s[%s]" value="%s">',
            $option_name, $setting_id, $value
        );
    }

    // HELPER STATIC FUNCTIONS -------------------------------------------------

    public static function getTotalAvailableSeats(): ?int {
        $options = get_option('seat_settings');
        $val = isset($options['total_available_seats']) ? $options['total_available_seats'] : "";
        return $val !== "" ? (int) $val : null;
    }

    public static function getAdditionalPurchasableSeats(): ?int {
        $options = get_option('seat_settings');
        $val = isset($options['additional_purchasable_seats']) ? $options['additional_purchasable_seats'] : "";
        return $val !== "" ? (int) $val : null;
    }

    public static function getSeniorPrepaidSeats(): ?int {
        $options = get_option('seat_settings');
        $val = isset($options['senior_prepaid_seats']) ? $options['senior_prepaid_seats'] : "";
        return $val !== "" ? (int) $val : null;
    }

    public static function getJuniorPrepaidSeats(): ?int {
        $options = get_option('seat_settings');
        $val = isset($options['junior_prepaid_seats']) ? $options['junior_prepaid_seats'] : "";
        return $val !== "" ? (int) $val : null;
    }

    public static function getSophomorePrepaidSeats(): ?int {
        $options = get_option('seat_settings');
        $val = isset($options['sophomore_prepaid_seats']) ? $options['sophomore_prepaid_seats'] : "";
        return $val !== "" ? (int) $val : null;
    }

    public static function getFreshmanPrepaidSeats(): ?int {
        $options = get_option('seat_settings');
        $val = isset($options['freshman_prepaid_seats']) ? $options['freshman_prepaid_seats'] : "";
        return $val !== "" ? (int) $val : null;
    }

    public static function getSeniorEnableDate(): ?\DateTime {
        $options = get_option('enable_settings');
        $val = isset($options['senior_enable_date']) ? $options['senior_enable_date'] : "";

        return $val !== "" ? new \DateTime($val) : null;
    }

    public static function getJuniorEnableDate(): ?\DateTime {
        $options = get_option('enable_settings');
        $val = isset($options['junior_enable_date']) ? $options['junior_enable_date'] : "";

        return $val !== "" ? new \DateTime($val) : null;
    }

    public static function getSophomoreEnableDate(): ?\DateTime {
        $options = get_option('enable_settings');
        $val = isset($options['sophomore_enable_date']) ? $options['sophomore_enable_date'] : "";

        return $val !== "" ? new \DateTime($val) : null;
    }

    public static function getFreshmanEnableDate(): ?\DateTime {
        $options = get_option('enable_settings');
        $val = isset($options['freshman_enable_date']) ? $options['freshman_enable_date'] : "";

        return $val !== "" ? new \DateTime($val) : null;
    }

    public static function getSeniorRelinquishDate(): ?\DateTime {
        $options = get_option('relinquish_settings');
        $val = isset($options['senior_relinquish_date']) ? $options['senior_relinquish_date'] : "";

        return $val !== "" ? new \DateTime($val) : null;
    }

    public static function getJuniorRelinquishDate(): ?\DateTime {
        $options = get_option('relinquish_settings');
        $val = isset($options['junior_relinquish_date']) ? $options['junior_relinquish_date'] : "";

        return $val !== "" ? new \DateTime($val) : null;
    }

    public static function getSophomoreRelinquishDate(): ?\DateTime {
        $options = get_option('relinquish_settings');
        $val = isset($options['sophomore_relinquish_date']) ? $options['sophomore_relinquish_date'] : "";

        return $val !== "" ? new \DateTime($val) : null;
    }

    public static function getFreshmanRelinquishDate(): ?\DateTime {
        $options = get_option('relinquish_settings');
        $val = isset($options['freshman_relinquish_date']) ? $options['freshman_relinquish_date'] : "";

        return $val !== "" ? new \DateTime($val) : null;
    }

    public static function getStripeTestModeEnabled(): bool {
        $options = get_option('stripe_settings');
        $val = isset($options['test_mode_enabled']) ? $options['test_mode_enabled'] : "";

        return $val !== "" ? $val === "on" : false;
    }

    public static function getStripeTestPublishableKey(): ?string {
        $options = get_option('stripe_settings');
        $val = isset($options['stripe_test_publishable_key']) ? $options['stripe_test_publishable_key'] : "";

        return $val !== "" ? $val : null;
    }

    public static function getStripeTestSecretKey(): ?string {
        $options = get_option('stripe_settings');
        $val = isset($options['stripe_test_secret_key']) ? $options['stripe_test_secret_key'] : "";

        return $val !== "" ? $val : null;
    }

    public static function getStripePublishableKey(): ?string {
        $options = get_option('stripe_settings');
        $val = isset($options['stripe_publishable_key']) ? $options['stripe_publishable_key'] : "";

        return $val !== "" ? $val : null;
    }

    public static function getStripeSecretKey(): ?string {
        $options = get_option('stripe_settings');
        $val = isset($options['stripe_secret_key']) ? $options['stripe_secret_key'] : "";

        return $val !== "" ? $val : null;
    }

}