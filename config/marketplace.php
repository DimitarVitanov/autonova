<?php

/*
|--------------------------------------------------------------------------
| AutoNova marketplace options
|--------------------------------------------------------------------------
| Central source of truth for enum labels, filter options, cities and
| commercial packages. Shared to the Vue frontend via Inertia so the UI
| and backend validation never drift apart.
*/

return [

    'currency' => 'EUR',
    'mkd_rate' => 61.5,

    'fuels' => [
        'petrol' => 'Petrol',
        'diesel' => 'Diesel',
        'hybrid' => 'Hybrid',
        'electric' => 'Electric',
        'lpg' => 'LPG',
        'cng' => 'CNG',
    ],

    'transmissions' => [
        'manual' => 'Manual',
        'automatic' => 'Automatic',
        'semi_automatic' => 'Semi-automatic',
    ],

    'drivetrains' => [
        'fwd' => 'Front-wheel drive',
        'rwd' => 'Rear-wheel drive',
        'awd' => 'All-wheel drive',
    ],

    'vat' => [
        'with_vat' => 'Price incl. VAT',
        'without_vat' => 'Price excl. VAT',
        'negotiable' => 'Negotiable',
    ],

    'conditions' => [
        'used' => 'Used',
        'new' => 'New',
    ],

    'seller_types' => [
        'all' => 'All sellers',
        'dealer' => 'Dealer',
        'private' => 'Private',
    ],

    'colors' => [
        'Black', 'White', 'Grey', 'Silver', 'Blue', 'Red', 'Green',
        'Brown', 'Beige', 'Orange', 'Yellow', 'Gold', 'Bordeaux', 'Other',
    ],

    'body_types' => [
        'cars' => ['Sedan', 'Hatchback', 'Estate', 'SUV', 'Coupe', 'Convertible', 'Minivan', 'Pickup', 'Roadster'],
        'motorcycles' => ['Sport', 'Naked', 'Touring', 'Enduro', 'Chopper', 'Scooter', 'Cross', 'Cruiser'],
        'vans' => ['Panel van', 'Combi', 'Chassis cab', 'Pickup', 'Minibus', 'Box'],
        'trucks' => ['Tractor unit', 'Tipper', 'Box truck', 'Flatbed', 'Refrigerated', 'Tanker', 'Chassis'],
        'machinery' => ['Tractor', 'Excavator', 'Loader', 'Forklift', 'Combine', 'Bulldozer', 'Backhoe'],
        'trailers' => ['Curtainsider', 'Tipper', 'Flatbed', 'Car transporter', 'Refrigerated', 'Boat trailer', 'Caravan', 'Camper'],
    ],

    'cities' => [
        'Skopje', 'Bitola', 'Kumanovo', 'Prilep', 'Tetovo', 'Veles', 'Ohrid',
        'Gostivar', 'Štip', 'Strumica', 'Kavadarci', 'Kočani', 'Kičevo', 'Struga',
        'Radoviš', 'Gevgelija', 'Debar', 'Kriva Palanka', 'Sveti Nikole', 'Negotino',
        'Berovo', 'Bogdanci', 'Valandovo', 'Vinica', 'Delčevo', 'Demir Kapija', 'Demir Hisar',
        'Kratovo', 'Kruševo', 'Makedonski Brod', 'Makedonska Kamenica', 'Pehčevo', 'Probištip', 'Resen',
    ],

    'sorts' => [
        'newest' => 'Newest first',
        'price_asc' => 'Price: low to high',
        'price_desc' => 'Price: high to low',
        'mileage_asc' => 'Lowest mileage',
        'year_desc' => 'Newest year',
        'power_desc' => 'Most powerful',
    ],

    'promotions' => [
        [
            'key' => 'none',
            'kicker' => 'Free',
            'name' => 'Standard',
            'price' => '€0',
            'desc' => '30-day active listing, up to 24 photos.',
        ],
        [
            'key' => 'bump',
            'kicker' => 'Bump',
            'name' => 'Bump to top',
            'price' => '€2',
            'desc' => 'Your listing jumps back to the top of the results.',
        ],
        [
            'key' => 'featured',
            'kicker' => 'Featured',
            'name' => 'Featured 7 days',
            'price' => '€8',
            'desc' => '7 days on the homepage and above search results.',
        ],
    ],

    'packages' => [
        [
            'name' => 'START',
            'price' => '€0',
            'per' => 'up to 5 listings',
            'feats' => ['Dealer profile page', 'Buyer messages', 'Basic statistics'],
            'cta' => 'Start free',
            'highlight' => false,
        ],
        [
            'name' => 'PRO',
            'price' => '€79',
            'per' => 'per month · up to 60 listings',
            'feats' => ['Everything in START', 'XML / CSV import', '10 promotion credits', 'Logo & banner on profile'],
            'cta' => 'Get PRO',
            'highlight' => true,
        ],
        [
            'name' => 'MAX',
            'price' => '€159',
            'per' => 'per month · unlimited',
            'feats' => ['Everything in PRO', 'Homepage placement', 'Banner campaigns', 'Custom profile domain'],
            'cta' => 'Contact sales',
            'highlight' => false,
        ],
    ],
];
