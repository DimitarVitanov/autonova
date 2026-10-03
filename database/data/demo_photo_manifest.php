<?php

/**
 * Curated set of REAL vehicle photographs used to seed premium demo listings.
 *
 * Sources are free for commercial use with no attribution required:
 *   - Unsplash  (src 'u')  → https://images.unsplash.com/photo-{id}
 *   - Pexels    (src 'p')  → https://images.pexels.com/photos/{id}/pexels-photo-{id}.jpeg
 *
 * Every entry is downloaded once by `php artisan demo:photos` into
 * storage/app/demo-photos/{category}/{key}.jpg, then the VehicleSeeder overlays
 * the AutoNova brand (App\Services\VehicleImageBrander) and normalises it.
 *
 * `body`   — body types this photo can plausibly represent (for cars, matched to
 *            the listing's body_type; empty = usable for any body type).
 * `exotic` — reserved for featured / homepage-promoted listings so supercars only
 *            show up as aspirational hero shots, never on an everyday listing.
 */

return [
    'cars' => [
        ['key' => 'panamera',    'src' => 'u', 'id' => '1503376780353-7e6692767b70', 'body' => ['Sedan', 'Coupe'],       'exotic' => false],
        ['key' => 'm5',          'src' => 'u', 'id' => '1555215695-3004980ad54e',     'body' => ['Sedan'],                'exotic' => false],
        ['key' => 'tesla3',      'src' => 'u', 'id' => '1560958089-b8a1929cea89',     'body' => ['Sedan', 'SUV'],         'exotic' => false],
        ['key' => 'audi-rs',     'src' => 'u', 'id' => '1606664515524-ed2f786a0bd6',  'body' => ['Sedan', 'Estate'],      'exotic' => false],
        ['key' => 'm4-blue',     'src' => 'u', 'id' => '1502877338535-766e1452684a',  'body' => ['Coupe', 'Sedan'],       'exotic' => false],
        ['key' => 'm4-grey',     'src' => 'u', 'id' => '1580273916550-e323be2ae537',  'body' => ['Coupe', 'Sedan'],       'exotic' => false],
        ['key' => 'm2',          'src' => 'u', 'id' => '1541443131876-44b03de101c5',  'body' => ['Coupe', 'Hatchback'],   'exotic' => false],
        ['key' => 'camaro',      'src' => 'u', 'id' => '1552519507-da3b142c6e3d',     'body' => ['Coupe'],                'exotic' => false],
        ['key' => 'mustang-blk', 'src' => 'u', 'id' => '1494976388531-d1058494cdd8',  'body' => ['Coupe'],                'exotic' => false],
        ['key' => 'mustang-2',   'src' => 'u', 'id' => '1547744152-14d985cb937f',     'body' => ['Coupe'],                'exotic' => false],
        ['key' => 'gtr',         'src' => 'u', 'id' => '1568605117036-5fe5e7bab0b7',  'body' => ['Coupe'],                'exotic' => false],
        ['key' => 'amg-gt',      'src' => 'u', 'id' => '1553440569-bcc63803a83d',     'body' => ['Coupe', 'Roadster'],    'exotic' => false],
        ['key' => 'red-rear',    'src' => 'u', 'id' => '1493238792000-8113da705763',  'body' => ['Coupe', 'Hatchback'],   'exotic' => false],
        ['key' => 'red-night',   'src' => 'u', 'id' => '1517672651691-24622a91b550',  'body' => ['Coupe'],                'exotic' => false],
        ['key' => 'convertible', 'src' => 'u', 'id' => '1494905998402-395d579af36f',  'body' => ['Convertible', 'Roadster', 'Coupe'], 'exotic' => false],
        ['key' => 'expedition',  'src' => 'u', 'id' => '1533473359331-0135ef1b58bf',  'body' => ['SUV', 'Pickup', 'Minivan'], 'exotic' => false],
        // Exotics — featured listings only
        ['key' => 'huracan-wht', 'src' => 'u', 'id' => '1544829099-b9a0c07fad1a',     'body' => ['Coupe', 'Roadster'],    'exotic' => true],
        ['key' => 'huracan-ylw', 'src' => 'u', 'id' => '1511919884226-fd3cad34687c',  'body' => ['Coupe', 'Roadster'],    'exotic' => true],
        ['key' => 'aventador',   'src' => 'u', 'id' => '1621135802920-133df287f89c',  'body' => ['Coupe'],                'exotic' => true],
        ['key' => 'ferrari',     'src' => 'u', 'id' => '1583121274602-3e2820c69888',  'body' => ['Coupe'],                'exotic' => true],
        ['key' => 'mclaren',     'src' => 'u', 'id' => '1542362567-b07e54358753',     'body' => ['Coupe'],                'exotic' => true],
    ],

    'motorcycles' => [
        ['key' => 'harley',      'src' => 'u', 'id' => '1558981285-6f0c94958bb6',     'body' => [], 'exotic' => false],
        ['key' => 'ducati',      'src' => 'u', 'id' => '1568772585407-9361f9bf3a87',  'body' => [], 'exotic' => false],
        ['key' => 'ktm-rc',      'src' => 'u', 'id' => '1449426468159-d96dbf08f19f',  'body' => [], 'exotic' => false],
        ['key' => 'ktm-duke',    'src' => 'u', 'id' => '1591637333184-19aa84b3e01f',  'body' => [], 'exotic' => false],
        ['key' => 'yamaha-r6',   'src' => 'u', 'id' => '1609630875171-b1321377ee65',  'body' => [], 'exotic' => false],
        ['key' => 'kawasaki',    'src' => 'u', 'id' => '1580310614729-ccd69652491d',  'body' => [], 'exotic' => false],
        ['key' => 'enfield',     'src' => 'u', 'id' => '1622185135505-2d795003994a',  'body' => [], 'exotic' => false],
        ['key' => 'rider',       'src' => 'u', 'id' => '1558981806-ec527fa84c39',     'body' => [], 'exotic' => false],
    ],

    'vans' => [
        ['key' => 'transporter', 'src' => 'p', 'id' => '11139386', 'body' => [], 'exotic' => false],
        ['key' => 'sprinter',    'src' => 'p', 'id' => '6169668',  'body' => [], 'exotic' => false],
        ['key' => 'postal',      'src' => 'p', 'id' => '4744769',  'body' => [], 'exotic' => false],
        ['key' => 'vw-bus',      'src' => 'p', 'id' => '18687549', 'body' => [], 'exotic' => false],
    ],

    'trucks' => [
        ['key' => 'kenworth',    'src' => 'p', 'id' => '27099095', 'body' => [], 'exotic' => false],
        ['key' => 'daf',         'src' => 'p', 'id' => '14437601', 'body' => [], 'exotic' => false],
        ['key' => 'volvo',       'src' => 'p', 'id' => '2199293',  'body' => [], 'exotic' => false],
        ['key' => 'containers',  'src' => 'p', 'id' => '12196579', 'body' => [], 'exotic' => false],
        ['key' => 'scania',      'src' => 'u', 'id' => '1601584115197-04ecc0da31d7', 'body' => [], 'exotic' => false],
        ['key' => 'truck-rear',  'src' => 'u', 'id' => '1519003722824-194d4455a60c', 'body' => [], 'exotic' => false],
    ],

    'machinery' => [
        ['key' => 'jcb',         'src' => 'p', 'id' => '3998410',  'body' => [], 'exotic' => false],
        ['key' => 'backhoe',     'src' => 'p', 'id' => '5125782',  'body' => [], 'exotic' => false],
        ['key' => 'excavator',   'src' => 'p', 'id' => '36957845', 'body' => [], 'exotic' => false],
        ['key' => 'loader',      'src' => 'p', 'id' => '129544',   'body' => [], 'exotic' => false],
        ['key' => 'jd-6r',       'src' => 'p', 'id' => '12495793', 'body' => [], 'exotic' => false],
        ['key' => 'jd-vintage',  'src' => 'p', 'id' => '28408325', 'body' => [], 'exotic' => false],
        ['key' => 'jd-baling',   'src' => 'p', 'id' => '12612082', 'body' => [], 'exotic' => false],
    ],

    'trailers' => [
        ['key' => 'box-trailer', 'src' => 'p', 'id' => '25284586', 'body' => [], 'exotic' => false],
        ['key' => 'container',   'src' => 'p', 'id' => '12196579', 'body' => [], 'exotic' => false],
        ['key' => 'camper',      'src' => 'p', 'id' => '31792414', 'body' => [], 'exotic' => false],
        ['key' => 'kenworth-tr', 'src' => 'p', 'id' => '27099095', 'body' => [], 'exotic' => false],
        ['key' => 'volvo-tr',    'src' => 'p', 'id' => '2199293',  'body' => [], 'exotic' => false],
    ],
];
