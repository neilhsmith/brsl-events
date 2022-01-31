<?php

declare(strict_types=1);

namespace BRSL\Models;

use \WP_User;
use \DateTime;

if (!defined('ABSPATH')) exit;

class SponsorSponsee {
    public static string $tableName = BRSL_SLUG . '_sponsor_sponsee';

    public int $id;
    public DateTime  $createdAt;
    public int $sponsorId;
    public int $sponseeId;

    public static function withID(int $id): ?SponsorSponsee {
        global $wpdb;
        $prefixedTableName = $wpdb->prefix . self::$tableName;

        $sql = $wpdb->prepare("SELECT * FROM $prefixedTableName WHERE id=%d", $id);
        $row = $wpdb->get_row($sql, ARRAY_A);

        $instance = new self();
        $instance->id = absint($row['id']);
        $instance->createdAt = new DateTime($row['created_at']);
        $instance->sponsorId = absint($row['sponsor_id']);
        $instance->sponseeId = absint($row['sponsee_id']);
        return $instance;
    }

    public static function create($sponsorId, $sponseeId): ?SponsorSponsee {
        global $wpdb;
        $prefixedTableName = $wpdb->prefix . self::$tableName;

        $result = $wpdb->insert($prefixedTableName, array(
            'sponsor_id' => $sponsorId,
            'sponsee_id' => $sponseeId
        ), array('%d', '%d'));

        if (!$result)
            return null;

        return self::withId($wpdb->insert_id);
    }

    public static function delete($id): int {
        global $wpdb;
        $prefixedTableName = $wpdb->prefix . self::$tableName;

        return $wpdb->delete($prefixedTableName, array('id' => $id), array('%d'));
    }

    public static function recordCount(): int {
        global $wpdb;
        $prefixedTableName = $wpdb->prefix . self::$tableName;

		$sql = "SELECT COUNT(*) FROM $prefixedTableName";

		return absint($wpdb->get_var( $sql ));
    }

    public static function getSponseeIds(): array {
        global $wpdb;
        $prefixedTableName = $wpdb->prefix . self::$tableName;

        $results = $wpdb->get_results("SELECT sponsee_id FROM $prefixedTableName", ARRAY_A);

        $ids = array();
        foreach ($results as $result) {
            array_push($ids, $result['sponsee_id']);
        }
        return $ids;
    }

    public static function getSponseesBySponsorId(int $id): array {
        global $wpdb;
        $prefixedTableName = $wpdb->prefix . self::$tableName;
        $prefixedUsersTableName = $wpdb->prefix . "users";
        $prefixedMetaTableName = $wpdb->prefix . "usermeta";

        $sql = "
            SELECT $prefixedUsersTableName.*, um.meta_value as 'user_firstname', um2.meta_value as 'user_lastname'
            FROM $prefixedUsersTableName
            INNER JOIN $prefixedTableName ON $prefixedUsersTableName.ID = $prefixedTableName.sponsee_id
            LEFT JOIN $prefixedMetaTableName as um ON $prefixedUsersTableName.ID = um.user_id
            LEFT JOIN $prefixedMetaTableName as um2 ON $prefixedUsersTableName.ID = um2.user_id
            WHERE $prefixedTableName.sponsor_id = $id AND um.meta_key = 'first_name' AND um2.meta_key = 'last_name'
        ";
        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    public static function createTable(): void {
        global $wpdb;
        $prefixedTableName = $wpdb->prefix . self::$tableName;
        $userTableName = $wpdb->prefix . "users";

        $sql = "CREATE TABLE IF NOT EXISTS $prefixedTableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            sponsor_id BIGINT(20) UNSIGNED NOT NULL,
            sponsee_id BIGINT(20) UNSIGNED NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (sponsor_id) REFERENCES $userTableName (ID),
            FOREIGN KEY (sponsee_id) REFERENCES $userTableName (ID)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}