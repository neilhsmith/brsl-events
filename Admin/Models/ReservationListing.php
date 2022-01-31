<?php

declare(strict_types=1);

namespace BRSL\Admin\Models;

if (!defined('ABSPATH')) exit;

class ReservationListing {
    public int $id;
    public int $order;
    public string $firstName;
    public string $lastName;
    public bool $under21;
    public bool $veganMeal;
}