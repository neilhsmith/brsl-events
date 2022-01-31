<?php

declare(strict_types=1);

namespace BRSL\Api\Controllers;

use WP_REST_Server;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

use BRSL\Models\Role;

if (!defined('ABSPATH')) exit;

class SponsorController extends WP_REST_Controller {
    public function __construct(string $namespace) {
        $this->namespace = $namespace;
        $this->resource_name = 'sponsor';
    }

    public function register_routes(): void {
        register_rest_route($this->namespace, '/' . $this->resource_name, array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($this, 'getSponsor'),
            'permission_callback' => array($this, 'permissionCallback'),
            'args' => array()
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
     * Returns the WP_User object for the current user. The permissionCallback 
     * already ensured the current user is a Sponsor or Admin so we can simply 
     * grab the info we need and return it.
     */
    public function getSponsor(WP_REST_Request $request) {
        $user = wp_get_current_user();
        $firstName = get_user_meta($user->ID, 'first_name', true);
        $lastName = get_user_meta($user->ID, 'last_name', true);

        $payload = array(
            'id' => $user->ID,
            'firstName' => $firstName,
            'lastName' => $lastName
        );

        return new WP_Rest_Response($payload, 200);
    }
}