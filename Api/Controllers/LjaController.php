<?php

declare(strict_types=1);

namespace BRSL\Api\Controllers;

use WP_REST_Server;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP_User;

use BRSL\Models\Reservation;
use BRSL\Models\Role;

if (!defined('ABSPATH')) exit;

class LjaController extends WP_REST_Controller {
    public function __construct(string $namespace) {
        $this->namespace = $namespace;
        $this->resource_name = 'ljas';
    }

    public function register_routes(): void {
        register_rest_route($this->namespace, '/' . $this->resource_name, array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'getLjas'),
            'permission_callback' => array($this, 'permissionCallback'),
            'args' => array()
        ));

        register_rest_route($this->namespace, $this->resource_name . '/update', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'updateLjas'),
            'permission_callback' => array($this, 'permissionCallback'),
            'args' => array(
                'ljas' => array(
                    'required' => true,
                    'validate_callback' => function($param, $request, $key) {
                        return is_array($param);
                    }
                )
            )
        ));

        register_rest_route($this->namespace, $this->resource_name . '/relinquish_seats', array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => array($this, 'relinquishSeats'),
            'permission_callback' => array($this, 'permissionCallback'),
            'args' => array(
                'ljaId' => array(
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
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

    /**
     * Returns the LJAs for the current Sponsor.
     */
    public function getLjas(WP_REST_Request $request) {
        $ljas = $this->getCurrentSponsorsLjas();
        $payload = array();

        foreach ($ljas as $lja) {
            $id = $lja->ID;
            $firstName = get_user_meta($id, 'first_name', true);
            $lastName = get_user_meta($id, 'last_name', true);
            $notes = get_user_meta($id, 'brsl_notes', true);
            $acknowledgesResponsibility = get_user_meta($id, 'brsl_acknowledges_responsibility', true);
            $acknowledgesRelinquish = get_user_meta($id, 'brsl_acknowledges_relinquish', true);
            $didRelinquish = get_user_meta($id, 'brsl_did_relinquish', true);
            $role = get_userdata($id)->roles[0];

            array_push($payload, array(
                'id' => $id,
                'role' => $role,
                'firstName' => $firstName,
                'lastName' => $lastName,
                'notes' => $notes,
                'acknowledgesResponsibility' => $acknowledgesResponsibility === "1",
                'acknowledgesRelinquish' => $acknowledgesRelinquish === "1",
                'didRelinquish' => $didRelinquish === "1"
            ));
        }
        
        return new WP_Rest_Response($payload, 200);
    }

    public function updateLjas(WP_REST_Request $request) {
        $ljas = $request->get_param('ljas');

        // the only thing the app allows updating on LJAs is the notes meta field
        foreach ($ljas as $lja) {
            update_user_meta($lja['id'], 'brsl_notes', $lja['notes']);
            update_user_meta($lja['id'], 'brsl_acknowledges_responsibility', $lja['acknowledgesResponsibility']);
            update_user_meta($lja['id'], 'brsl_acknowledges_relinquish', $lja['acknowledgesRelinquish']);
        }

        return new WP_Rest_Response($ljas, 200);
    }

    public function relinquishSeats(WP_REST_Request $request) {
        $ljaId = $request->get_param('ljaId');

        // TODO: security check - make sure this lja belongs to the current sponsor

        $result = Reservation::deleteByLjaId((int) $ljaId);

        if ($result > 0) {
            update_user_meta($ljaId, 'brsl_acknowledges_relinquish', true);
            update_user_meta($ljaId, 'brsl_did_relinquish', true);

            // TODO: return the lja object instead
            return new WP_Rest_Response($result, 200);
        }

        return new WP_Error('400', 'Error: nothing was deleted.', array( 'status' => 400));
    }

    private function getCurrentSponsorsLjas(): array {
        $user = wp_get_current_user();
        $userId = $user->ID;

        global $wpdb;
        $usersTable = $wpdb->prefix . 'users';

        $results = $wpdb->get_results(
            $wpdb->prepare("
                SELECT *
                FROM $usersTable
                INNER JOIN wp_brsl_sponsor_sponsee ON wp_users.ID = wp_brsl_sponsor_sponsee.sponsee_id
                WHERE wp_brsl_sponsor_sponsee.sponsor_id = %d;
        ", $userId));

        return $results;
    }
}