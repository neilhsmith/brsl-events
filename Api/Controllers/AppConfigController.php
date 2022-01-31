<?php

declare(strict_types=1);

namespace BRSL\Api\Controllers;

use WP_REST_Server;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

use BRSL\Models\Role;
use BRSL\Models\Reservation;
use BRSL\Admin\Settings;

if (!defined('ABSPATH')) exit;

class AppConfigController extends WP_REST_Controller {
    public function __construct(string $namespace) {
        $this->namespace = $namespace;
        $this->resource_name = 'get_app_config';
    }

    public function register_routes(): void {
        register_rest_route($this->namespace, '/' . $this->resource_name, array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'getAppConfig'),
            'permission_callback' => array($this, 'permissionCallback'),
            'args' => array()
        ));
    }

    /**
     * Returns 401 resonse if the user is not logged in, 403 response if the user
     * is logged in but not a brsl_sponsor, and true if this request should be
     * handled.
     */
    public function permissionCallback(WP_REST_Request $request) {
        $user = wp_get_current_user();

        if ($user->ID === 0) {
            return new WP_Error(401, esc_html('You must login to access the ' . $this->resource_name . ' resource.'));
        }
        else if (!in_array(Role::getSponsorKey(), $user->roles) && !in_array("administrator", $user->roles)) {
            return new WP_Error(403, esc_html('You do not have access to the ' . $this->resource_name . ' resource.'));
        }

        return true;
    }

    /**
     * Returns the reservation app's configration data like the purchasable seat
     * counts, enable dates, relinquish dates, and the Stripe publishable key.
     */
    public function getAppConfig(WP_REST_Request $request) {
        // get the appropriate stripe key for test & live modes
        $testModeEnabled = Settings::getStripeTestModeEnabled();
        $stripeKey = $testModeEnabled ? Settings::getStripeTestSecretKey() : Settings::getStripeSecretKey();

        $totalAvailableCount = Settings::getTotalAvailableSeats() === null ? 0 : Settings::getTotalAvailableSeats();
        $purchasedCount      = Reservation::getCount();
        $purchasableCount = $totalAvailableCount - $purchasedCount;

        $seniorEnableDate = Settings::getSeniorEnableDate();
        $juniorEnableDate = Settings::getJuniorEnableDate();
        $sophomoreEnableDate = Settings::getSophomoreEnableDate();
        $freshmanEnableDate = Settings::getFreshmanEnableDate();
        $now = date("Y-m-d H:i:s");

        $seniorEnabled = true;
        $juniorEnabled = true;
        $sophomoreEnabled = true;
        $freshmanEnabled = true;

        if ($seniorEnableDate !== null && $seniorEnableDate->format('Y-m-d H:i:s') > $now)
            $seniorEnabled = false;
        if ($juniorEnableDate !== null && $juniorEnableDate->format('Y-m-d H:i:s') > $now)
            $juniorEnabled = false;
        if ($sophomoreEnableDate !== null && $sophomoreEnableDate->format('Y-m-d H:i:s') > $now)
            $sophomoreEnabled = false;
        if ($freshmanEnableDate !== null && $freshmanEnableDate->format('Y-m-d H:i:s') > $now)
            $freshmanEnabled = false;

        // can relinquish if:
        // 1: the relinquish date is set
        // 2: the enable date is not set and the relinquish date is in the future OR
        //    the enable date is set, in the past, and relinquish date is in the future

        $seniorRelinquishDate = Settings::getSeniorRelinquishDate();
        $juniorRelinquishDate = Settings::getJuniorRelinquishDate();
        $sophomoreRelinquishDate = Settings::getSophomoreRelinquishDate();
        $freshmanRelinquishDate = Settings::getFreshmanRelinquishDate();
        $seniorCanRelinquish = false;
        $juniorCanRelinquish = false;
        $sophomoreCanRelinquish = false;
        $freshmanCanRelinquish = false;

        // 1 the class is enabled

        if (
            ($seniorEnableDate === null && $seniorRelinquishDate !== null && $now <= $seniorRelinquishDate->format('Y-m-d H:i:s')) ||
            ($senorEnabled && $seniorRelinquishDate !== null && $now <= $seniorRelinquishDate->format('Y-m-d H:i:s'))
        ) {
            $seniorCanRelinquish = true;
        }if (
            ($juniorEnableDate === null && $juniorRelinquishDate !== null && $now <= $juniorRelinquishDate->format('Y-m-d H:i:s')) ||
            ($juniorEnabled && $juniorRelinquishDate !== null && $now <= $juniorRelinquishDate->format('Y-m-d H:i:s'))
        ) {
            $juniorCanRelinquish = true;
        }if (
            ($sophomoreEnableDate === null && $sophomoreRelinquishDate !== null && $now <= $sophomoreRelinquishDate->format('Y-m-d H:i:s')) ||
            ($sophomoreEnabled && $sophomoreRelinquishDate !== null && $now <= $sophomoreRelinquishDate->format('Y-m-d H:i:s'))
        ) {
            $sophomoreCanRelinquish = true;
        }if (
            ($freshmanEnableDate === null && $freshmanRelinquishDate !== null && $now <= $freshmanRelinquishDate->format('Y-m-d H:i:s')) ||
            ($freshmanEnabled && $freshmanRelinquishDate !== null && $now <= $freshmanRelinquishDate->format('Y-m-d H:i:s'))
        ) {
            $freshmanCanRelinquish = true;
        }

        $payload = array(
            'purchasableCount'            => $purchasableCount,
            'stripeKey'                   => $stripeKey,
            'totalAvailableSeats'         => Settings::getTotalAvailableSeats(),
            'seniorPrepaidSeats'          => Settings::getSeniorPrepaidSeats(),
            'juniorPrepaidSeats'          => Settings::getJuniorPrepaidSeats(),
            'sophomorePrepaidSeats'       => Settings::getSophomorePrepaidSeats(),
            'freshmanPrepaidSeats'        => Settings::getFreshmanPrepaidSeats(),
            'seniorEnabled'               => $seniorEnabled,
            'juniorEnabled'               => $juniorEnabled,
            'sophomoreEnabled'            => $sophomoreEnabled,
            'freshmanEnabled'             => $freshmanEnabled,
            'seniorCanRelinquish'        => $seniorCanRelinquish,
            'juniorCanRelinquish'        => $juniorCanRelinquish,
            'sophomoreCanRelinquish'     => $sophomoreCanRelinquish,
            'freshmanCanRelinquish'       => $freshmanCanRelinquish,
            // TODO: don't think the following values are used in the frontend.
            //       should be able to remove them but im leaving for now just in case.
            'seniorEnableDate'            => $seniorEnableDate,
            'juniorEnableDate'            => $juniorEnableDate,
            'sophomoreEnableDate'         => $sophomoreEnableDate,
            'freshmanEnableDate'          => $freshmanEnableDate,
            'seniorRelinquishDate'        => $seniorRelinquishDate,
            'juniorRelinquishDate'        => $juniorRelinquishDate,
            'sophomoreRelinquishDate'     => $sophomoreRelinquishDate,
            'freshmanRelinquishDate'      => $freshmanRelinquishDate,
            // 'additionalPurchaseableSeats' => Settings::getAdditionalPurchasableSeats(),
            // 'seniorRelinquishDate'        => Settings::getSeniorRelinquishDate(),
            // 'juniorRelinquishDate'        => Settings::getJuniorRelinquishDate(),
            // 'sophomoreRelinquishDate'     => Settings::getSophomoreRelinquishDate(),
            // 'freshmanRelinquishDate'      => Settings::getFreshmanRelinquishDate(),
        );

        return new WP_Rest_Response($payload, 200);
    }

}