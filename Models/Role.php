<?php

declare(strict_types=1);

namespace BRSL\Models;

class Role {
    private static $roles = array(
        'brsl_sponsor'   => 'Sponsor',
        'brsl_senior'    => 'Senior',
        'brsl_junior'    => 'Junior',
        'brsl_sophomore' => 'Sophomore',
        'brsl_freshman'  => 'Freshman'
    );

    // Returns the keys as an array
    public static function getRoleKeys(): array {
        return array_keys(self::$roles);
    }

    // Returns the friendly names as an array
    public static function getRoles(): array {
        return self::$roles;
    }

    // Returns the key of a role by the given friendly name, otherwise null,
    public static function getKey(string $name): ?string {
        $result = array_search($name, self::$roles, true);
        return $result ? $result : null;
    }
    
    // Returns the friendly name of a role by the given key, otherwise null.
    public static function getName(string $key): ?string {
        $result = self::$roles[$key];
        return isset($result) ? $result : null;
    }

    public static function getSponsorKey(): string {
        return array_keys(self::$roles)[0];
    }

    public static function getSponseeKeys(): array {
        return array_keys(array_slice(self::$roles, 1));
    }
}