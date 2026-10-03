<?php

namespace Database\Seeders;

use App\Models\CarModel;
use App\Models\Category;
use App\Models\Dealer;
use App\Models\Feature;
use App\Models\Make;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Services\DemoImageGenerator;
use App\Services\DemoPhotoLibrary;
use App\Services\ImageService;
use App\Services\VehicleImageBrander;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VehicleSeeder extends Seeder
{
    private DemoImageGenerator $gen;
    private VehicleImageBrander $brander;
    private DemoPhotoLibrary $photos;
    private ImageService $images;
    private array $featureIds;
    private array $descriptions;
    private int $seedCounter = 0;

    public function run(): void
    {
        $this->gen = new DemoImageGenerator();
        $this->brander = new VehicleImageBrander();
        $this->photos = new DemoPhotoLibrary();
        $this->images = app(ImageService::class);
        $this->featureIds = Feature::pluck('id')->all();
        $this->descriptions = [
            'Bought new in North Macedonia, regularly serviced at an authorised dealer. Second owner, no damage, complete documentation. Test drive and diagnostics check welcome.',
            'Excellent condition, garage kept, non-smoker. Full service history available. Financing and registration assistance offered.',
            'Well maintained with recent service. New tyres and brakes. Clean interior, everything works as it should. Serious buyers only.',
            'Imported from Germany with documented mileage. Inspected and ready to register. Trade-in possible.',
            'Company-owned vehicle, always serviced on time. Winter and summer tyres included. Available for immediate handover.',
        ];

        $categories = Category::get()->keyBy('slug');
        $dealers = Dealer::with('user')->get()->keyBy(fn ($d) => $d->slug);
        $privateUsers = User::where('account_type', 'private')->where('role', 'client')->get();

        $this->seedCurated($categories, $dealers, $privateUsers);
        $this->seedGenerated($categories, $dealers, $privateUsers);
    }

    private function seedCurated($categories, $dealers, $privateUsers): void
    {
        $curated = [
            ['cat' => 'cars', 'dealer' => 'auto-centar-vardar', 'make' => 'Volkswagen', 'model' => 'Passat', 'version' => '2.0 TDI Elegance', 'price' => 13900, 'year' => 2019, 'km' => 147000, 'fuel' => 'diesel', 'trans' => 'automatic', 'cc' => 1968, 'hp' => 150, 'drive' => 'fwd', 'body' => 'Estate', 'color' => 'Grey', 'promo' => 'homepage'],
            ['cat' => 'cars', 'dealer' => 'premium-motors', 'make' => 'BMW', 'model' => '3 Series', 'version' => '320d xDrive', 'price' => 21700, 'year' => 2021, 'km' => 92000, 'fuel' => 'diesel', 'trans' => 'automatic', 'cc' => 1995, 'hp' => 190, 'drive' => 'awd', 'body' => 'Sedan', 'color' => 'Black', 'promo' => 'featured'],
            ['cat' => 'cars', 'private' => true, 'make' => 'Opel', 'model' => 'Astra', 'version' => '1.6 CDTI', 'price' => 8450, 'year' => 2017, 'km' => 188000, 'fuel' => 'diesel', 'trans' => 'manual', 'cc' => 1598, 'hp' => 110, 'drive' => 'fwd', 'body' => 'Hatchback', 'color' => 'Blue', 'promo' => 'none'],
            ['cat' => 'cars', 'dealer' => 'auto-centar-vardar', 'make' => 'Volkswagen', 'model' => 'Passat', 'version' => 'GTE Plug-in Hybrid', 'price' => 17300, 'year' => 2020, 'km' => 78000, 'fuel' => 'hybrid', 'trans' => 'automatic', 'cc' => 1395, 'hp' => 218, 'drive' => 'fwd', 'body' => 'Estate', 'color' => 'White', 'promo' => 'featured'],
            ['cat' => 'vans', 'dealer' => 'kombe-centar', 'make' => 'Renault', 'model' => 'Trafic', 'version' => '2.0 dCi L2H1', 'price' => 11200, 'year' => 2018, 'km' => 215000, 'fuel' => 'diesel', 'trans' => 'manual', 'cc' => 1997, 'hp' => 120, 'drive' => 'fwd', 'body' => 'Panel van', 'color' => 'White', 'promo' => 'featured'],
            ['cat' => 'vans', 'dealer' => 'kombe-centar', 'make' => 'Mercedes-Benz', 'model' => 'Sprinter', 'version' => '314 CDI', 'price' => 18900, 'year' => 2019, 'km' => 168000, 'fuel' => 'diesel', 'trans' => 'manual', 'cc' => 2143, 'hp' => 143, 'drive' => 'rwd', 'body' => 'Box', 'color' => 'White', 'promo' => 'none'],
            ['cat' => 'machinery', 'dealer' => 'agro-mehanika', 'make' => 'John Deere', 'model' => '6R', 'version' => '6R 150', 'price' => 68000, 'year' => 2018, 'km' => 4200, 'fuel' => 'diesel', 'trans' => 'automatic', 'cc' => 4500, 'hp' => 150, 'drive' => 'awd', 'body' => 'Tractor', 'color' => 'Green', 'promo' => 'featured'],
            ['cat' => 'motorcycles', 'private' => true, 'make' => 'Yamaha', 'model' => 'MT-07', 'version' => 'ABS', 'price' => 6900, 'year' => 2020, 'km' => 12400, 'fuel' => 'petrol', 'trans' => 'manual', 'cc' => 689, 'hp' => 74, 'drive' => 'rwd', 'body' => 'Naked', 'color' => 'Blue', 'promo' => 'none'],
        ];

        foreach ($curated as $c) {
            $category = $categories[$c['cat']];
            [$seller, $dealer] = isset($c['private'])
                ? [$privateUsers->random(), null]
                : [$dealers[$c['dealer']]->user, $dealers[$c['dealer']]];

            $make = Make::where('category_id', $category->id)->where('name', $c['make'])->first();
            $model = $make ? CarModel::where('make_id', $make->id)->where('name', $c['model'])->first() : null;

            $this->build([
                'category' => $category, 'make' => $make, 'model' => $model, 'seller' => $seller, 'dealer' => $dealer,
                'version' => $c['version'], 'price' => $c['price'], 'year' => $c['year'], 'km' => $c['km'],
                'fuel' => $c['fuel'], 'trans' => $c['trans'], 'cc' => $c['cc'], 'hp' => $c['hp'],
                'drive' => $c['drive'], 'body' => $c['body'], 'color' => $c['color'],
                'status' => 'active', 'promo' => $c['promo'], 'images' => random_int(5, 6),
            ]);
        }
    }

    private function seedGenerated($categories, $dealers, $privateUsers): void
    {
        $plan = ['cars' => 16, 'motorcycles' => 6, 'vans' => 6, 'trucks' => 5, 'machinery' => 5, 'trailers' => 5];
        $bodyTypes = config('marketplace.body_types');
        $colors = config('marketplace.colors');
        $dealerList = $dealers->values();

        foreach ($plan as $slug => $count) {
            $category = $categories[$slug];
            $makes = Make::where('category_id', $category->id)->inRandomOrder()->limit($count + 4)->get();

            for ($i = 0; $i < $count; $i++) {
                $make = $makes->random();
                $model = CarModel::where('make_id', $make->id)->inRandomOrder()->first();

                $isDealer = random_int(1, 100) <= 55;
                [$seller, $dealer] = $isDealer
                    ? (fn ($d) => [$d->user, $d])($dealerList->random())
                    : [$privateUsers->random(), null];

                $specs = $this->randomSpecs($slug);
                $status = $this->weighted(['active' => 74, 'pending' => 12, 'sold' => 8, 'draft' => 6]);
                $promo = $status === 'active'
                    ? $this->weighted(['none' => 70, 'bump' => 16, 'featured' => 14])
                    : 'none';

                $this->build([
                    'category' => $category, 'make' => $make, 'model' => $model, 'seller' => $seller, 'dealer' => $dealer,
                    'version' => $specs['version'], 'price' => $specs['price'], 'year' => $specs['year'], 'km' => $specs['km'],
                    'fuel' => $specs['fuel'], 'trans' => $specs['trans'], 'cc' => $specs['cc'], 'hp' => $specs['hp'],
                    'drive' => $specs['drive'], 'body' => $bodyTypes[$slug][array_rand($bodyTypes[$slug])],
                    'color' => $colors[array_rand($colors)],
                    'status' => $status, 'promo' => $promo, 'images' => random_int(3, 5),
                ]);
            }
        }
    }

    private function build(array $d): void
    {
        /** @var Category $category */
        $category = $d['category'];
        $seller = $d['seller'];
        $dealer = $d['dealer'];
        $makeName = $d['make']?->name ?? 'Vehicle';
        $modelName = $d['model']?->name ?? '';
        $title = trim("{$makeName} {$modelName}");
        $seed = $this->seedCounter++;

        $publishedAt = now()->subDays(random_int(0, 45))->subHours(random_int(0, 23));

        $vehicle = Vehicle::create([
            'user_id' => $seller->id,
            'dealer_id' => $dealer?->id,
            'category_id' => $category->id,
            'make_id' => $d['make']?->id,
            'car_model_id' => $d['model']?->id,
            'title' => $title,
            'slug' => Str::slug($title) . '-' . Str::lower(Str::random(6)),
            'version' => $d['version'],
            'price' => $d['price'],
            'vat' => $dealer ? 'with_vat' : 'negotiable',
            'year' => $d['year'],
            'mileage_km' => $d['km'],
            'fuel' => $d['fuel'],
            'transmission' => $d['trans'],
            'engine_cc' => $d['cc'],
            'power_hp' => $d['hp'],
            'drivetrain' => $d['drive'],
            'body_type' => $d['body'],
            'doors' => in_array($category->slug, ['cars', 'vans']) ? [3, 4, 5][array_rand([3, 4, 5])] : null,
            'seats' => $category->slug === 'cars' ? 5 : ($category->slug === 'vans' ? [2, 3, 6, 9][array_rand([2, 3, 6, 9])] : null),
            'color' => $d['color'],
            'condition' => $d['km'] < 500 ? 'new' : 'used',
            'owners' => random_int(1, 3),
            'registered_until' => str_pad((string) random_int(1, 12), 2, '0', STR_PAD_LEFT) . ' / ' . random_int(2026, 2028),
            'city' => $seller->city ?? config('marketplace.cities')[array_rand(config('marketplace.cities'))],
            'description' => $this->descriptions[array_rand($this->descriptions)],
            'seller_type' => $dealer ? 'dealer' : 'private',
            'contact_phone' => $seller->phone,
            'status' => $d['status'],
            'promotion' => $d['promo'],
            'promoted_until' => in_array($d['promo'], ['featured', 'homepage']) ? now()->addDays(7) : null,
            'is_featured' => in_array($d['promo'], ['featured', 'homepage']),
            'views' => random_int(120, 4200),
            'bumped_at' => $publishedAt,
            'published_at' => $d['status'] === 'active' ? $publishedAt : null,
        ]);

        // Features
        if (! empty($this->featureIds)) {
            $picked = collect($this->featureIds)->shuffle()->take(random_int(6, 12))->all();
            $vehicle->features()->sync($picked);
        }

        // Images — real, branded vehicle photos matched to the listing.
        $count = $d['images'];
        $featured = in_array($d['promo'], ['featured', 'homepage'], true);
        $sources = $this->photos->sources($category->slug, $d['body'], $featured, $count, $seed);

        $variants = $sources === [] ? range(0, $count - 1) : array_keys($sources);
        foreach ($variants as $v) {
            $binary = isset($sources[$v]) && is_file($sources[$v])
                ? $this->brander->brand($sources[$v])
                : $this->gen->generate([
                    'category' => $category->slug,
                    'make' => $makeName,
                    'model' => $modelName,
                    'version' => $d['version'],
                    'year' => (string) $d['year'],
                    'fuel' => config("marketplace.fuels.{$d['fuel']}"),
                    'color' => $d['color'],
                    'seed' => $seed,
                    'variant' => $v,
                ]);

            $stored = $this->images->storeBinary($binary, 'vehicles/' . now()->format('Y/m'));

            VehicleImage::create([
                'vehicle_id' => $vehicle->id,
                'path' => $stored['path'],
                'thumb_path' => $stored['thumb_path'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'sort' => $v,
                'is_cover' => $v === 0,
            ]);
        }
    }

    private function randomSpecs(string $slug): array
    {
        $r = fn ($min, $max, $step = 1) => intdiv(random_int($min, $max), $step) * $step;

        return match ($slug) {
            'motorcycles' => [
                'version' => ['ABS', 'Standard', 'SP', 'GT', ''][array_rand([0, 1, 2, 3, 4])],
                'price' => $r(1500, 18000, 100), 'year' => $r(2008, 2024), 'km' => $r(2000, 60000, 500),
                'fuel' => 'petrol', 'trans' => 'manual', 'cc' => $r(125, 1200, 25), 'hp' => $r(15, 180, 5), 'drive' => 'rwd',
            ],
            'vans' => [
                'version' => ['L1H1', 'L2H2', 'L3H2 Maxi', 'CDI', ''][array_rand([0, 1, 2, 3, 4])],
                'price' => $r(6000, 42000, 100), 'year' => $r(2012, 2024), 'km' => $r(40000, 320000, 1000),
                'fuel' => 'diesel', 'trans' => $this->weighted(['manual' => 70, 'automatic' => 30]), 'cc' => $r(1500, 3000, 100), 'hp' => $r(90, 180, 5), 'drive' => 'fwd',
            ],
            'trucks' => [
                'version' => ['Euro 6', 'Euro 5', '6x2', '4x2', ''][array_rand([0, 1, 2, 3, 4])],
                'price' => $r(15000, 120000, 500), 'year' => $r(2010, 2023), 'km' => $r(200000, 1200000, 5000),
                'fuel' => 'diesel', 'trans' => 'automatic', 'cc' => $r(9000, 13000, 100), 'hp' => $r(320, 560, 10), 'drive' => 'rwd',
            ],
            'machinery' => [
                'version' => ['Powershift', 'CVT', 'Utility', ''][array_rand([0, 1, 2, 3])],
                'price' => $r(12000, 140000, 500), 'year' => $r(2005, 2023), 'km' => $r(500, 12000, 100),
                'fuel' => 'diesel', 'trans' => $this->weighted(['manual' => 40, 'automatic' => 60]), 'cc' => $r(3000, 7000, 100), 'hp' => $r(70, 300, 5), 'drive' => 'awd',
            ],
            'trailers' => [
                'version' => ['Tandem', 'Triple axle', 'Tautliner', ''][array_rand([0, 1, 2, 3])],
                'price' => $r(1200, 45000, 100), 'year' => $r(2008, 2024), 'km' => 0,
                'fuel' => 'diesel', 'trans' => 'manual', 'cc' => null, 'hp' => null, 'drive' => null,
            ],
            default => [ // cars
                'version' => ['1.6 TDI', '2.0 TDI', '1.4 TSI', '1.5 dCi', '1.0 TCe', '2.0 TFSI', '1.6 HDi', 'Hybrid'][array_rand(range(0, 7))],
                'price' => $r(2500, 45000, 100), 'year' => $r(2008, 2024), 'km' => $r(20000, 280000, 1000),
                'fuel' => $this->weighted(['diesel' => 46, 'petrol' => 38, 'hybrid' => 10, 'electric' => 3, 'lpg' => 3]),
                'trans' => $this->weighted(['manual' => 55, 'automatic' => 45]),
                'cc' => $r(1000, 3000, 100), 'hp' => $r(75, 320, 5),
                'drive' => $this->weighted(['fwd' => 70, 'awd' => 20, 'rwd' => 10]),
            ],
        };
    }

    private function weighted(array $weights): string
    {
        $total = array_sum($weights);
        $roll = random_int(1, $total);
        $acc = 0;
        foreach ($weights as $key => $weight) {
            $acc += $weight;
            if ($roll <= $acc) {
                return (string) $key;
            }
        }
        return (string) array_key_first($weights);
    }
}
