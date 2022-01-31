<?php

declare(strict_types=1);

namespace BRSL\Admin;

use BRSL\Models\Reservation;

if (!defined('ABSPATH')) exit;

class ExportReservations {

  public function __construct() {
    add_action('init', array($this, 'downloadReservationsCsv'));
  }

  public function downloadReservationsCsv(): void {
    if (!isset($_POST['download_reservations_csv']))
        return;

    $exportReservations = $this->getReservationsForExport();

    $output_filename = 'reservations.csv';
    $output_handle = @fopen('php://output', 'w');

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Content-Type: application/octet-stream");
    header("Content-Disposition: attachment; filename=\"" . $output_filename . "\";" );
    header("Content-Transfer-Encoding: binary");

    $first = true;
    foreach ($exportReservations as $reservation) {
        if ($first) {
            $titles = array();
            foreach ($reservation as $key => $value) {
                $titles[] = $key;
            }
            fputcsv($output_handle, $titles);
            $first = false;
        }

        $leadArray = (array) $reservation;
        fputcsv($output_handle, $leadArray);
    }

    fclose($output_handle);
    die();
  }
 
  private function getReservationsForExport(): array {
    global $wpdb;
    $reservationTable = $wpdb->prefix . Reservation::$tableName;
    $bridgeTable = $wpdb->prefix . 'brsl_sponsor_sponsee';
    $userMetaTable = $wpdb->prefix . 'usermeta';

    $sql = "
      SELECT
        res.first_name,
        res.last_name,
        res.order,
        CONCAT(sponsor_meta_last.meta_value, ', ', sponsor_meta_first.meta_value) AS 'sponsor_name',
        CONCAT(lja_meta_last.meta_value, ', ', lja_meta_first.meta_value) AS 'lja_name',
        CASE WHEN res.under_21 = '1' THEN 'yes' ELSE 'no' END as 'under_21',
        CASE WHEN res.vegan_meal = '1' THEN 'yes' ELSE 'no' END as 'vegan_meal',
        CASE WHEN lja_meta_resp.meta_value = '1' THEN 'yes' ELSE 'no' END as 'lja_acknowleges_responsibility'
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

    $sponsorName = $_POST['sponsor_name'];
    $ljaName = $_POST['lja_name'];
    $under21 = $_POST['under_21'];

    if (!empty($sponsorName) || !empty($ljaName)) {
        $wheres = array();

        if (!empty($sponsorName)) {
            array_push($wheres, 
                'CONCAT(sponsor_meta_last.meta_value, ", ", sponsor_meta_first.meta_value) LIKE "%' . $sponsorName . '%"');
        }
        if (!empty($ljaName)) {
            array_push($wheres,
                'CONCAT(lja_meta_last.meta_value, ", ", lja_meta_first.meta_value) LIKE "%' . $ljaName . '%"');
        }

        $sql .= " WHERE ";
        $sql .= implode(" OR ", $wheres);
    }

    $sql .= ' ORDER BY sponsor_meta_last.meta_value ASC, lja_meta_last.meta_value ASC';

    $results = $wpdb->get_results($sql, ARRAY_A);

    // ugh, get the role for each lja
    $reservations = array();
    foreach ($results as $result) {
      $user_meta = get_userdata($result['lja_id']);
      $user_role = $user_meta->roles[0];
      
      $role = '';
      if ($user_role === 'brsl_senior')
        $role = 'Senior';
      else if ($user_role === 'brsl_junior')
        $role = 'Junior';
      else if ($user_role === 'brsl_sophomore')
        $role = 'Sophomore';
      else if ($user_role === 'brsl_freshman')
        $role = 'Freshman';

      $reservation = $result;
      $reservation['lja_role'] = $role;

      array_push($reservations, $reservation);
    }

    return $reservations;
  }

}