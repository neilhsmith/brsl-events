<?php

declare(strict_types=1);

namespace BRSL\Models;

if (!defined('ABSPATH')) exit;

class Reservation {
    public static string $tableName = BRSL_SLUG . '_reservation';

    public int $id;
    public int $ljaId;
    public \DateTime $createdAt;
    public int $order;
    public string $firstName;
    public string $lastName;
    public bool $under21;
    public bool $veganMeal;
    
    public static function getAll(): array {
        global $wpdb;
        $tableName = $wpdb->prefix . self::$tableName;

        $sql = "SELECT * FROM $tableName";
        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    public static function getByIds(array $ids): array {
        global $wpdb;
        $tableName = $wpdb->prefix . self::$tableName;

        $sql = "SELECT * FROM $tableName WHERE id IN (" . implode(', ', $ids) . ")";
        $results = $wpdb->get_results($sql, ARRAY_A);

        return $results;
    }

    public static function getCount(): int {
        global $wpdb;
        $tableName = $wpdb->prefix . self::$tableName;

        $count = $wpdb->get_var("SELECT COUNT(*) FROM $tableName");
        if (!$count)
            $count = 0;

        return (int) $count;
    }

    public static function createMany(int $ljaId, int $count): array {
        global $wpdb;
        $tableName = $wpdb->prefix . self::$tableName;

        $order = self::getThisLjasHighestOrder($ljaId);

        $insert_ids = array();
        for ($i = 0; $i < $count; $i++) {
            $result = $wpdb->insert($tableName, array(
                'lja_id' => $ljaId,
                'order' => $order + $i + 1,
                'under_21' => true,
                'vegan_meal' => false
            ));

            array_push($insert_ids, $wpdb->insert_id);
        }

        return $insert_ids;
    }

    public static function update(array $reservations): int {
        global $wpdb;
        $tableName = $wpdb->prefix . self::$tableName;

        $resultCount = 0;
        foreach ($reservations as $reservation) {
            $result = $wpdb->update($tableName, array(
                'lja_id'      => $reservation['ljaId'],
                'order'       => $reservation['order'],
                'first_name'  => $reservation['firstName'],
                'last_name'   => $reservation['lastName'],
                'under_21'    => $reservation['under21'],
                'vegan_meal'  => $reservation['veganMeal']
            ), array(
                'id' => $reservation['id']
            ));

            if ($result !== false)
                $resultCount = $resultCount + 1;
        }

        return $resultCount;
    }

    public static function createTable(): void {
        global $wpdb;
        $prefixedTableName = $wpdb->prefix . self::$tableName;
        $usersTable = $wpdb->prefix . "users";
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $prefixedTableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            lja_id BIGINT(20) UNSIGNED NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            `order` mediumint(9) NOT NULL,
            first_name text NOT NULL,
            last_name text NOT NULL,
            under_21 boolean NOT NULL,
            vegan_meal boolean NOT NULL,
            PRIMARY KEY (id),
            FOREIGN KEY (lja_id) REFERENCES $usersTable (ID)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function deleteByLjaId(int $ljaId): int {
        global $wpdb;
        $tableName = $wpdb->prefix . self::$tableName;

        $result = $wpdb->delete($tableName, array(
            'lja_id' => $ljaId
        ), array(
            '%d'
        ));

        if (!is_int($result)) {
            return 0;
        }

        return $result;
    }

    public static function getThisLjasHighestOrder(int $ljaId) {
        global $wpdb;
        $tableName = $wpdb->prefix . self::$tableName;

        $sql = $wpdb->prepare("
            SELECT `order`
            FROM $tableName
            WHERE lja_id = %d
            ORDER BY `order` DESC
            LIMIT 1;", 
        $ljaId);
        $result = $wpdb->get_row($sql);

        if (!isset($result->order))
            return -1;

        return $result->order;
    }

}