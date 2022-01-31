<?php

declare(strict_types=1);

namespace BRSL\Admin\Views;

use \WP_List_Table;
use BRSL\Models\Reservation;

if (!defined('ABSPATH')) exit;

class ReservationsListTable extends WP_List_Table {

    public function __construct() {
        parent::__construct( [
			'singular' => __( 'Reservation' ),
			'plural'   => __( 'Reservations' ),
			'ajax'     => false
		] );
    }

    // public static function get_reservations( int $per_page = 5, int $page_number = 1 ): array {
    //     global $wpdb;
    //     $prefixedTableName = $wpdb->prefix . Reservation::$tableName;
    //     $sponsorsLjasTableName = $wpdb->prefix . 'brsl_sponsor_sponsee';
    //     $prefixedUsersTable = $wpdb->prefix . 'users';
    //     $prefixedUsersMetaTable = $wpdb->prefix . 'usermeta';
        
    //     $sponsorName = $_POST['sponsor_name'];
    //     $ljaName = $_POST['lja_name'];
    //     $ljaClass = $_POST['lja_class'];

    //     $sql = "
    //         SELECT 
    //             res.*,
    //             CONCAT(lja_meta1.meta_value, ', ', lja_meta2.meta_value) AS 'lja_name',
    //             CONCAT(sponsor_meta1.meta_value, ', ', sponsor_meta2.meta_value) AS 'sponsor_name'
    //         FROM wp_brsl_reservation AS res
    //         INNER JOIN wp_usermeta AS lja_meta1 ON res.lja_id = lja_meta1.user_id
    //         INNER JOIN wp_usermeta AS lja_meta2 ON res.lja_id = lja_meta2.user_id
    //         INNER JOIN wp_usermeta AS lja_meta3 ON res.lja_id = lja_meta3.user_id
    //         INNER JOIN wp_brsl_sponsor_sponsee AS ss1 ON res.lja_id = ss1.sponsee_id
    //         INNER JOIN wp_usermeta AS sponsor_meta1 ON ss1.sponsor_id = sponsor_meta1.user_id
    //         INNER JOIN wp_usermeta AS sponsor_meta2 ON ss1.sponsor_id = sponsor_meta2.user_id
    //         WHERE 
    //             lja_meta1.meta_key = 'last_name' AND lja_meta2.meta_key = 'first_name' AND
    //             sponsor_meta1.meta_key = 'last_name' AND sponsor_meta2.meta_key = 'first_name'
    //     ";

    //     if (!empty($sponsorName)) {
    //         $sql .= " AND CONCAT(sponsor_meta1.meta_value, ', ', sponsor_meta2.meta_value) LIKE '%" . $sponsorName . "%'";
    //     }
    //     if (!empty($ljaName)) {
    //         $sql .= " AND CONCAT(lja_meta1.meta_value, ', ', lja_meta2.meta_value) LIKE '%" . $ljaName . "%'";
    //     }

    //     if ( ! empty( $_REQUEST['orderby'] ) ) {
	// 		$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
	// 		$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
	// 	}

	// 	$sql .= " LIMIT $per_page";
	// 	$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

    //     //echo $sql . '<br><br>';
    //     print_r($_POST);

    //     $results = $wpdb->get_results($sql, ARRAY_A);

    //     return $results;
    // }

    public static function get_sql(int $per_page = 100, int $page_number = 1): string {
        global $wpdb;
        $reservationTable = $wpdb->prefix . Reservation::$tableName;
        $bridgeTable = $wpdb->prefix . 'brsl_sponsor_sponsee';
        $userMetaTable = $wpdb->prefix . 'usermeta';

        $sql = "
            FROM $reservationTable AS res
            LEFT JOIN $bridgeTable ON res.lja_id = $bridgeTable.sponsee_id
            LEFT JOIN $userMetaTable AS sponsor_meta_last ON wp_brsl_sponsor_sponsee.sponsor_id = sponsor_meta_last.user_id 
	            AND sponsor_meta_last.meta_key = 'last_name'
            LEFT JOIN $userMetaTable AS sponsor_meta_first ON wp_brsl_sponsor_sponsee.sponsor_id = sponsor_meta_first.user_id 
                AND sponsor_meta_first.meta_key = 'first_name'
            LEFT JOIN $userMetaTable AS lja_meta_last ON res.lja_id = lja_meta_last.user_id 
                AND lja_meta_last.meta_key = 'last_name'
            LEFT JOIN $userMetaTable AS lja_meta_first ON res.lja_id = lja_meta_first.user_id 
                AND lja_meta_first.meta_key = 'first_name'
            LEFT JOIN $userMetaTable AS lja_meta_resp ON res.lja_id = lja_meta_resp.user_id 
                AND lja_meta_resp.meta_key = 'brsl_acknowledges_responsibility'
        ";

        $ljaClass = $_POST['lja_class'];
        if (!empty($ljaClass)) {
            $sql .= "
                INNER JOIN $userMetaTable as lja_meta_role ON res.lja_id = lja_meta_role.user_id AND lja_meta_role.meta_key = 'wp_capabilities' 
                    AND lja_meta_role.meta_value LIKE '%" . $ljaClass . "%'
            ";
        }

        $wheres = array();

        $sponsorName = $_POST['sponsor_name'];
        $ljaName = $_POST['lja_name'];

        if (!empty($sponsorName) || !empty($ljaName) || !empty($under21) || !empty($veganMeal)) {
            if (!empty($sponsorName)) {
                array_push($wheres, 
                    'CONCAT(sponsor_meta_last.meta_value, ", ", sponsor_meta_first.meta_value) LIKE "%' . $sponsorName . '%"');
            }
            if (!empty($ljaName)) {
                array_push($wheres,
                    'CONCAT(lja_meta_last.meta_value, ", ", lja_meta_first.meta_value) LIKE "%' . $ljaName . '%"');
            }
            if (!empty($veganMeal)) {
                array_push($wheres,
                    'res.vegan_meal = true');
            }
        }
        if (!empty($_POST['under_21_true'])) {
            array_push($wheres, 'res.under_21 = true');
        }
        else if (!empty($_POST['under_21_false'])) {
            array_push($wheres, 'res.under_21 = false');
        }
        if (!empty($_POST['vegan_meal_true'])) {
            array_push($wheres, 'res.vegan_meal = true');
        }
        else if (!empty($_POST['vegan_meal_false'])) {
            array_push($wheres, 'res.vegan_meal = false');
        }

        if (!empty($wheres)) {
            $sql .= " WHERE ";
            $sql .= implode(" AND ", $wheres);
        }

        $sql .= ' ORDER BY sponsor_meta_last.meta_value ASC, lja_meta_last.meta_value ASC';
        $sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;

        return $sql;
    }

    public static function get_reservations( int $per_page = 100, int $page_number = 1 ): array {
        global $wpdb;

        $sql = "
        SELECT
            res.first_name,
            res.last_name,
            res.order,
            CASE WHEN res.under_21 = '1' THEN 'yes' ELSE 'no' END as 'under_21',
            CASE WHEN res.vegan_meal = '1' THEN 'yes' ELSE 'no' END as 'vegan_meal',
            CONCAT(sponsor_meta_last.meta_value, ', ', sponsor_meta_first.meta_value) AS 'sponsor_name',
            CONCAT(lja_meta_last.meta_value, ', ', lja_meta_first.meta_value) AS 'lja_name',
            CASE WHEN lja_meta_resp.meta_value = '1' THEN 'yes' ELSE 'no' END as 'lja_acknowleges_responsibility'
        " . self::get_sql($per_page, $page_number);

        echo $sql;

        $results = $wpdb->get_results($sql, ARRAY_A);
        return $results;
    }

    public static function record_count() {
        global $wpdb;

        $sql = "
            SELECT COUNT(*)
        " . self::get_sql(100000);

        $result = $wpdb->get_var($sql);
        
        return (int) $result;
	}

    public function no_items() {
		_e( 'No Reservations avaliable.' );
	}

	public function column_default( $item, $column_name ) {
		return $item[$column_name];
	}

	function get_columns() {
        return array(
            'sponsor_name' => 'Sponsor Name',
            'lja_name' => 'LJA Name',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'order' => 'order',
			'under_21' => 'Under 21',
            'vegan_meal' => 'Vegetarian Meal',
            'lja_acknowleges_responsibility' => 'Responsibility'
        );
	}

	public function get_sortable_columns() {
        return array(
            'sponsor_name' => array('sponsor_name', false),
            'lja_name' => array('lja_name', false),
            'first_name' => array('first_name', false),
            'last_name' => array('last_name', false),
            'under_21' => array('under_21', false),
            'vegan_meal' => array('vegan_meal', false),
            'lja_acknowleges_responsibility' => array('lja_acknowleges_responsibility', false)
        );
	}

    public function prepare_items() {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

		$per_page     = 250;
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

        $this->_column_headers = array($columns, $hidden, $sortable);
		$this->items = self::get_reservations( $per_page, $current_page );
    }

}