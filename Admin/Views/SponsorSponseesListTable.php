<?php

declare(strict_types=1);

namespace BRSL\Admin\Views;

use \WP_List_Table;
use BRSL\Models\Role;
use BRSL\Models\SponsorSponsee;

if (!defined('ABSPATH')) exit;

// https://github.com/w3guy/WP_List_Table-Class-Plugin-Example/blob/master/plugin.php

class SponsorSponseesListTable extends WP_List_Table {
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Sponsor Sponsee', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Sponsor Sponsees', 'sp' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}

	public static function get_sponsor_sponsees( $per_page = 5, $page_number = 1 ) {
        global $wpdb;
        $prefixedTableName = $wpdb->prefix . SponsorSponsee::$tableName;
        $prefixedUsersTable = $wpdb->prefix . 'users';
        $prefixedUsersMetaTable = $wpdb->prefix . 'usermeta';

        $sql = "
            SELECT 
            ss.id, ss.created_at, 
            ss.sponsor_id, u1.display_name as 'sponsor_name',
            ss.sponsee_id, u2.display_name as 'sponsee_name'
            FROM $prefixedTableName AS ss
            LEFT JOIN $prefixedUsersTable as u1 ON ss.sponsor_id = u1.ID
            LEFT JOIN $prefixedUsersTable as u2 ON ss.sponsee_id = u2.ID
        ";

        if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		}

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        $results = $wpdb->get_results($sql, ARRAY_A);

        $sponsorSponsees = array();
        foreach ($results as $result) {
            $sponseeMeta = get_userdata($result['sponsee_id']);
            $sponseeRole = $sponseeMeta->roles[0];

			$reservationsTable = $wpdb->prefix . 'brsl_reservation';
			$results = $wpdb->get_row($wpdb->prepare("SELECT COUNT(*) as 'count' FROM $reservationsTable WHERE lja_id = %d", $result['sponsee_id']));

			

            array_push($sponsorSponsees, array(
                'id' => $result['id'],
                'sponsor_id' => $result['sponsor_id'],
                'sponsor_name' => $result['sponsor_name'],
                'sponsee_id' => $result['sponsee_id'],
                'sponsee_name' => $result['sponsee_name'],
                'sponsee_year' => Role::getName($sponseeRole),
				'seat_count' => $results->count
            ));
        }

        return $sponsorSponsees;
	}

	public static function delete_sponsor_sponsee( $id ) {
		SponsorSponsee::delete($id);
	}

	public static function record_count() {
		return SponsorSponsee::recordCount();
	}

	public function no_items() {
		_e( 'No Sponsor/LJAs avaliable.' );
	}

	public function column_default( $item, $column_name ) {
		return $item[$column_name];
	}

	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}

	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'brsl_delete_sponsor_sponsee' );

		$title = '<strong>' . $item['name'] . '</strong>';

		$actions = [
            // TODO
			'delete' => sprintf( '<a href="?page=%s&action=%s&sponsor_sponsee=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}

	function get_columns() {
        return array(
            'cb'      => '<input type="checkbox" />',
            'sponsor_name' => 'Sponsor',
            'sponsee_name' => 'Sponsee',
            'sponsee_year' => 'Year',
			'seat_count' => 'Seat Count'
        );
	}

	public function get_sortable_columns() {
        return array(
            'sponsor_name' => array('sponsor_name', false),
            'sponsee_name' => array('sponsee_name', false)
        );
	}

	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
	}

	public function prepare_items() {
		//$this->_column_headers = $this->get_column_info();
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = 100;
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

        $this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = self::get_sponsor_sponsees( $per_page, $current_page );
	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'brsl_delete_sponsor_sponsee' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_sponsor_sponsee( absint( $_GET['sponsor_sponsee'] ) );

                // esc_url_raw() is used to prevent converting ampersand in url to "#038;"
                // add_query_arg() return the current url
                wp_redirect( esc_url_raw(add_query_arg()) );
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_sponsor_sponsee( $id );

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
            // add_query_arg() return the current url
            wp_redirect( esc_url_raw(add_query_arg()) );
			exit;
		}
	}
}