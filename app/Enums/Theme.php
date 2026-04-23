<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Theme: string implements HasLabel
{
    case Products = 'Products';
    case Activities = 'Activities';
    case Bookings = 'Bookings';
    case Cars = 'Cars';

    public function getLabel(): string
    {
        return match ($this) {
            self::Products => 'Products Store',
            self::Activities => 'Booking Platform',
            self::Bookings => 'Stays & Accommodations',
            self::Cars => 'Car Rentals',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Products => 'Classic ecommerce layout for selling physical or digital products.',
            self::Activities => 'Flexible booking layout for activities, accommodations, vehicles, tours, and events.',
            self::Bookings => 'Airbnb-style layout for renting apartments, villas, rooms, and short-term stays.',
            self::Cars => 'Vehicle rental layout for cars, motorbikes, and other transport.',
        };
    }
}
