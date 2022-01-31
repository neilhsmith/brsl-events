<?php

declare(strict_types=1);

namespace BRSL\Includes;

if (!defined('ABSPATH')) exit;

/**
 * Redirects to the login page when a user is not logged in and the current page is blocked.
 */

class WPMem {
    public function __construct() {
        add_action( 'template_redirect', array($this, 'handleRedirect') );
    }

    function handleRedirect() {
        if ( ! is_user_logged_in() && wpmem_is_blocked() ) {
            wpmem_redirect_to_login();
        }
        return;
    }
}