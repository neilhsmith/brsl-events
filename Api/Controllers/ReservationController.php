<?php

declare(strict_types=1);

namespace BRSL\Api\Controllers;

use WP_REST_Server;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP_User;

use \Stripe\Stripe;
use \Stripe\Charge;
use \Stripe\Customer;
use \Stripe\StripeClient;

use BRSL\Models\Role;
use BRSL\Models\Reservation;
use BRSL\Admin\Settings;

if (!defined('ABSPATH')) exit;

class ReservationController extends WP_REST_Controller {
    public function __construct(string $namespace) {
        $this->namespace = $namespace;
        $this->resource_name = 'reservation';
    }

    public function register_routes(): void {
        register_rest_route($this->namespace, '/' . $this->resource_name, array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'getReservations'),
            'permission_callback' => array($this, 'permissionCallback'),
            'args' => array(
                'ljaIds' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return is_array($param);
                    }
                )
            )
        ));

        register_rest_route($this->namespace, $this->resource_name . '/update', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'updateReservations'),
            'permission_callback' => array($this, 'permissionCallback'),
            'args' => array(
                'reservations' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return is_array($param);
                    }
                )
            )
        ));

        register_rest_route($this->namespace, $this->resource_name . '/purchase', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($this, 'purchaseReservations'),
            'permission_callback' => array($this, 'permissionCallback'),
            'args' => array(
                'token' => array(
                    'required' => true
                ),
                'items' => array(
                    'required' => true
                )
            )
        ));
    }

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

    public function getReservations(WP_REST_Request $request) {
        $ljaIds = $request->get_param('ljaIds');
        $reservations = $this->getReservationsByLjaIds($ljaIds);
        $payload = array();

        foreach ($reservations as $reservation) {
            array_push($payload, array(
                'id' => $reservation->id,
                'ljaId' => $reservation->lja_id,
                'createdAt' => $reservation->created_at,
                'order' => (int) $reservation->order,
                'firstName' => $reservation->first_name,
                'lastName' => $reservation->last_name,
                'under21' => $reservation->under_21 === "1",
                'veganMeal' => $reservation->vegan_meal === "1",
            ));
        }
        
        return new WP_Rest_Response($payload, 200);
    }

    public function updateReservations(WP_REST_Request $request) {
        $reservations = $request->get_param('reservations');

        $updatedCount = Reservation::update($reservations);
        
        if ($reservationResultCount === 0) {
            return new WP_Error('400', 'Error: nothing was updated.', array( 'status' => 400));
        }

        $payload = array(
            'count' => $updatedCount
        );

        return new WP_Rest_Response($payload, 200);
    }

    public function purchaseReservations(WP_REST_Request $request) {
        $token = $request->get_param('token');
        $items = $request->get_param('items');
        $user  = wp_get_current_user();
        $pricePerTicket = 12500; // todo: move this to Settings
              
        $stripe = new StripeClient($this->getStripeSecretKey());

        // setup the description
        $itemCount = 0;
        $descrip = 'LJA Seat Purchase -';
        foreach ($items as $key => $value) {
            if ($value > 0) {
                $firstName = get_user_meta($key, 'first_name', true);
                $lastName = get_user_meta($key, 'last_name', true);

                $itemCount += $value;
                $descrip   .= ' ' . $value . ' seats for ' . $firstName . ' ' . $lastName . ',';
            }
        }
        $descrip = rtrim($descrip, ',');

        // error if user is purchasing more tickets than are available
        $totalAvailableCount = Settings::getTotalAvailableSeats();
        $purchasedCount      = Reservation::getCount();
        $purchasableCount = $totalAvailableCount - $purchasedCount;

        if ($itemCount > $purchasableCount) {
            return new WP_Rest_Response(array(
                'message' => 'There are only ' . $purchasableCount . ' seats left.'
            ), 400);
        }

        // get or create the stripe customer
        $existingCustomers = $stripe->customers->all(['email' => $user->user_email, 'limit' => 1]);
        if (empty($existingCustomers->data)) {
            $customer = $stripe->customers->create([
                'email' => $user->user_email,
                'description' => 'LJA Sponsor',
                'name' => $user->first_name . ' ' . $user->last_name
            ]);
            $customerId = $customer->id;
        }
        else {
            $customerId = $existingCustomers->data[0]->id;
            $customer = $existingCustomers->data[0];
        }

        // assign the payment token to the customer & charge the customer
        $source = $customer->sources->create(['source' => $token['id']]);
        $charge = $stripe->charges->create([
            'amount' => $itemCount * $pricePerTicket,
            'currency' => 'usd',
            'description' => $descrip,
            'customer' => $customerId,
            'source' => $source->id
        ]);

        // create new reservations
        $newIds = array();
        foreach ($items as $key => $value) {
            if ($value <= 0) continue;

            $reservationIds = Reservation::createMany($key, $value);
            foreach ($reservationIds as $ids)
                array_push($newIds, $ids);
        }

        $newReservations = Reservation::getByIds($newIds);
        $payload = array();

        foreach ($newReservations as $res) {
            array_push($payload, array(
                'id' => $res['id'],
                'ljaId' => $res['lja_id'],
                'createdAt' => $res['created_at'],
                'order' => (int) $res['order'],
                'firstName' => $res['first_name'],
                'lastName' => $res['last_name'],
                'under21' => $res['under_21'] === "1",
                'veganMeal' => $res['vegan_meal'] === "1"
            ));
        }

        return new WP_Rest_Response($payload, 200);
    }

    private function getReservationsByLjaIds(array $ids): array {
        if (empty($ids)) {
            return array();
        }

        global $wpdb;
        $reservationsTable = $wpdb->prefix . 'brsl_reservation';

        $results = $wpdb->get_results("
                SELECT *
                FROM $reservationsTable
                WHERE lja_id IN (" . implode(', ', $ids) . ")
        ");

        return $results;
    }

    private function getStripeSecretKey(): string {
        $testMode = Settings::getStripeTestModeEnabled();
        return $testMode ? Settings::getStripeTestSecretKey() : Settings::getStripeSecretKey();
    }
}