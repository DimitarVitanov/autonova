<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Translation;
use Illuminate\Database\Seeder;

/**
 * Seeds editable UI strings (English source + Macedonian) into the translations
 * table. Admins tune the Macedonian from the admin panel. Also localises the
 * Category records to Macedonian (they render straight from the DB).
 *
 * Idempotent: only fills missing MK so admin edits are never overwritten.
 */
class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        // Per-area string files live in database/seeders/translations/*.php.
        $strings = $this->strings();
        foreach (glob(__DIR__ . '/translations/*.php') as $file) {
            $strings = array_merge($strings, require $file);
        }

        foreach ($strings as [$group, $key, $en, $mk]) {
            $row = Translation::firstOrNew(['key' => $key]);
            $row->group = $group;
            $row->en = $en;
            if ($row->mk === null || $row->mk === '') {
                $row->mk = $mk;
            }
            $row->save();
        }

        $this->localiseCategories();
    }

    private function localiseCategories(): void
    {
        $cats = [
            'cars' => ['Автомобил', 'Автомобили', 'Патнички возила'],
            'motorcycles' => ['Мотоцикл', 'Мотоцикли', 'Мотори и скутери'],
            'vans' => ['Комбе', 'Комбиња', 'До 3.5 т'],
            'trucks' => ['Камион', 'Камиони', 'Товарни возила'],
            'machinery' => ['Машина', 'Механизација', 'Земјоделска и градежна'],
            'trailers' => ['Приколка', 'Приколки', 'Приколки и кампери'],
        ];

        foreach ($cats as $slug => [$name, $plural, $hint]) {
            Category::where('slug', $slug)->update([
                'name' => $name,
                'name_plural' => $plural,
                'hint' => $hint,
            ]);
        }
    }

    /** @return array<int, array{0:string,1:string,2:string,3:string}> [group, key, en, mk] */
    private function strings(): array
    {
        return [
            // ── Navigation ───────────────────────────────────────────────
            ['nav', 'nav.vehicles', 'Vehicles', 'Возила'],
            ['nav', 'nav.dealers', 'Dealers', 'Автосалони'],
            ['nav', 'nav.packages', 'Packages', 'Пакети'],
            ['nav', 'nav.saved', 'Saved', 'Зачувани'],
            ['nav', 'nav.inbox', 'Inbox', 'Пораки'],
            ['nav', 'nav.sell', 'Sell your car', 'Продади возило'],
            ['nav', 'nav.signin', 'Sign in', 'Најави се'],
            ['nav', 'nav.dashboard', 'Dashboard', 'Контролна табла'],
            ['nav', 'nav.saved_vehicles', 'Saved vehicles', 'Зачувани возила'],
            ['nav', 'nav.messages', 'Messages', 'Пораки'],
            ['nav', 'nav.profile', 'Profile', 'Профил'],
            ['nav', 'nav.admin', 'Admin panel', 'Админ панел'],
            ['nav', 'nav.logout', 'Log out', 'Одјави се'],
            ['nav', 'nav.role_dealer', 'Dealer', 'Автосалон'],
            ['nav', 'nav.role_private', 'Private', 'Приватен'],

            // ── Category names (also switch with the language) ───────────
            ['cat', 'cat.cars', 'Cars', 'Автомобили'],
            ['cat', 'cat.motorcycles', 'Motorcycles', 'Мотоцикли'],
            ['cat', 'cat.vans', 'Vans', 'Комбиња'],
            ['cat', 'cat.trucks', 'Trucks', 'Камиони'],
            ['cat', 'cat.machinery', 'Machinery', 'Механизација'],
            ['cat', 'cat.trailers', 'Trailers', 'Приколки'],

            // ── Common ───────────────────────────────────────────────────
            ['common', 'common.search', 'Search', 'Пребарај'],
            ['common', 'common.all_macedonia', 'All Macedonia', 'Цела Македонија'],
            ['common', 'common.any_make', 'Any make', 'Сите марки'],
            ['common', 'common.all_prefix', 'All', 'Сите'],
            ['common', 'common.vehicles_word', 'vehicles', 'возила'],
            ['common', 'common.dealer', 'Dealer', 'Автосалон'],
            ['common', 'common.private_seller', 'Private seller', 'Приватен продавач'],
            ['common', 'common.verified', 'Verified', 'Проверен'],

            // ── Hero / search ────────────────────────────────────────────
            ['hero', 'hero.eyebrow', 'MK · Vehicle marketplace · Est. 2026', 'МК · Пазар за возила · Осн. 2026'],
            ['hero', 'hero.title_1', 'Find your next', 'Најди го твоето следно'],
            ['hero', 'hero.title_2', 'vehicle.', 'возило.'],
            ['hero', 'hero.sub', 'Cars, motorcycles, vans, trucks, machinery and trailers — from verified dealers and private sellers. Real photos, no duplicate listings.', 'Автомобили, мотоцикли, комбиња, камиони, механизација и приколки — од проверени автосалони и приватни продавачи. Вистински фотографии, без дупликат огласи.'],
            ['hero', 'hero.stat_vehicles', 'Vehicles listed', 'Возила во понуда'],
            ['hero', 'hero.stat_dealers', 'Verified dealers', 'Проверени автосалони'],
            ['hero', 'hero.stat_new', 'New today', 'Ново денес'],
            ['hero', 'hero.pick', 'Pick of the week', 'Избор на неделата'],
            ['hero', 'hero.bodytype_label', 'Or jump straight to a body type', 'Или избери директно тип возило'],
            ['hero', 'search.category', 'Category', 'Категорија'],
            ['hero', 'search.make', 'Make', 'Марка'],
            ['hero', 'search.price', 'Max price (EUR)', 'Макс. цена (ЕУР)'],
            ['hero', 'search.city', 'City', 'Град'],
            ['hero', 'search.price_ph', 'e.g. 15 000', 'пр. 15 000'],

            // ── Home sections ────────────────────────────────────────────
            ['home', 'home.featured_kicker', 'Handpicked', 'Одбрано'],
            ['home', 'home.featured_title', 'Featured vehicles', 'Издвоени возила'],
            ['home', 'home.cats_kicker', 'Every kind of vehicle', 'Секаков вид возило'],
            ['home', 'home.cats_title', 'Browse by category', 'Разгледај по категорија'],
            ['home', 'home.brands_kicker', 'The badges you know', 'Знаковите што ги знаеш'],
            ['home', 'home.brands_title', 'Popular brands', 'Популарни марки'],
            ['home', 'home.all_brands', 'All brands', 'Сите марки'],
            ['home', 'home.cta_kicker', 'For dealerships', 'За автосалони'],
            ['home', 'home.cta_title', 'Put your whole inventory in one place.', 'Стави го целиот инвентар на едно место.'],
            ['home', 'home.cta_text', 'Dealer profile, XML/CSV import, per-listing analytics and leads straight to your inbox.', 'Профил на автосалон, XML/CSV увоз, аналитика по оглас и пораки директно во сандачето.'],
            ['home', 'home.cta_packages', 'See packages', 'Види пакети'],
            ['home', 'home.cta_dealers', 'Browse dealers', 'Разгледај автосалони'],
            ['home', 'home.new_kicker', 'Just added', 'Штотуку додадено'],
            ['home', 'home.new_title', 'Fresh listings', 'Свежи огласи'],
            ['home', 'home.browse_newest', 'Browse newest', 'Разгледај најнови'],
            ['home', 'home.dealers_kicker', 'Trusted sellers', 'Доверливи продавачи'],
            ['home', 'home.dealers_title', 'Verified dealers', 'Проверени автосалони'],
            ['home', 'home.all_dealers', 'All dealers', 'Сите автосалони'],

            // ── Footer ───────────────────────────────────────────────────
            ['footer', 'footer.tag', 'Macedonia’s marketplace for vehicles. Verified dealers, real photos, no duplicate listings.', 'Пазар за возила во Македонија. Проверени автосалони, вистински фотографии, без дупликат огласи.'],
            ['footer', 'footer.categories', 'Categories', 'Категории'],
            ['footer', 'footer.for_dealers', 'For dealers', 'За дилери'],
            ['footer', 'footer.packages', 'Packages', 'Пакети'],
            ['footer', 'footer.inventory', 'Inventory', 'Инвентар'],
            ['footer', 'footer.dealer_directory', 'Dealer directory', 'Именик на автосалони'],
            ['footer', 'footer.company', 'Company', 'Компанија'],
            ['footer', 'footer.about', 'About AutoNova', 'За AutoNova'],
            ['footer', 'footer.terms', 'Terms & privacy', 'Услови и приватност'],
            ['footer', 'footer.built', 'Built for the Balkans', 'Направено за Балканот'],

            // ── Vehicle card ─────────────────────────────────────────────
            ['card', 'card.top', 'Top', 'Топ'],
            ['card', 'card.featured', 'Featured', 'Издвоено'],
            ['card', 'card.photos', 'photos', 'фотографии'],
            ['card', 'fav.save', 'Save', 'Зачувај'],
            ['card', 'fav.saved', 'Saved', 'Зачувано'],
            ['unit', 'unit.km', 'km', 'км'],
            ['unit', 'unit.hp', 'hp', 'кс'],
            ['unit', 'unit.cc', 'cm³', 'см³'],

            // ── Enum values (fuel / transmission / drivetrain / condition / VAT) ──
            ['enum', 'enum.Petrol', 'Petrol', 'Бензин'],
            ['enum', 'enum.Diesel', 'Diesel', 'Дизел'],
            ['enum', 'enum.Hybrid', 'Hybrid', 'Хибрид'],
            ['enum', 'enum.Electric', 'Electric', 'Електричен'],
            ['enum', 'enum.LPG', 'LPG', 'ТНГ'],
            ['enum', 'enum.CNG', 'CNG', 'КПГ'],
            ['enum', 'enum.Manual', 'Manual', 'Рачен'],
            ['enum', 'enum.Automatic', 'Automatic', 'Автоматски'],
            ['enum', 'enum.Semi-automatic', 'Semi-automatic', 'Полуавтоматски'],
            ['enum', 'enum.Front-wheel drive', 'Front-wheel drive', 'Преден погон'],
            ['enum', 'enum.Rear-wheel drive', 'Rear-wheel drive', 'Заден погон'],
            ['enum', 'enum.All-wheel drive', 'All-wheel drive', 'Погон на сите тркала'],
            ['enum', 'enum.Used', 'Used', 'Половно'],
            ['enum', 'enum.New', 'New', 'Ново'],
            ['enum', 'enum.Price incl. VAT', 'Price incl. VAT', 'Цена со ДДВ'],
            ['enum', 'enum.Price excl. VAT', 'Price excl. VAT', 'Цена без ДДВ'],
            ['enum', 'enum.Negotiable', 'Negotiable', 'По договор'],

            // ── Sort options ─────────────────────────────────────────────
            ['sort', 'sort.newest', 'Newest first', 'Најнови прво'],
            ['sort', 'sort.price_asc', 'Price: low to high', 'Цена: ниска кон висока'],
            ['sort', 'sort.price_desc', 'Price: high to low', 'Цена: висока кон ниска'],
            ['sort', 'sort.mileage_asc', 'Lowest mileage', 'Најмала километража'],
            ['sort', 'sort.year_desc', 'Newest year', 'Најнова година'],
            ['sort', 'sort.power_desc', 'Most powerful', 'Најмоќни'],

            // ── Browse / search ──────────────────────────────────────────
            ['common', 'common.home', 'Home', 'Дома'],
            ['idx', 'idx.all_vehicles', 'All vehicles', 'Сите возила'],
            ['idx', 'idx.search_ph', 'Search make, model or keyword…', 'Пребарај марка, модел или клучен збор…'],
            ['idx', 'idx.filters', 'Filters', 'Филтри'],
            ['idx', 'idx.grid', 'Grid', 'Мрежа'],
            ['idx', 'idx.list', 'List', 'Листа'],
            ['idx', 'idx.empty_title', 'No vehicles match your filters', 'Нема возила што одговараат на филтрите'],
            ['idx', 'idx.empty_text', 'Try widening the price range, removing a filter, or a different category.', 'Обидете се со поширок опсег на цена, отстранете филтер или изберете друга категорија.'],

            // ── Filter sidebar ───────────────────────────────────────────
            ['flt', 'flt.clear_all', 'Clear all', 'Исчисти сè'],
            ['flt', 'flt.all_categories', 'All categories', 'Сите категории'],
            ['flt', 'flt.select_cat_first', 'Select category first', 'Прво избери категорија'],
            ['flt', 'flt.model', 'Model', 'Модел'],
            ['flt', 'flt.any_model', 'Any model', 'Сите модели'],
            ['flt', 'flt.price', 'Price (EUR)', 'Цена (ЕУР)'],
            ['flt', 'flt.from', 'from', 'од'],
            ['flt', 'flt.to', 'to', 'до'],
            ['flt', 'flt.year', 'Year', 'Година'],
            ['flt', 'flt.mileage_max', 'Mileage up to (km)', 'Километража до (км)'],
            ['flt', 'flt.fuel', 'Fuel', 'Гориво'],
            ['flt', 'flt.transmission', 'Transmission', 'Менувач'],
            ['flt', 'flt.any', 'Any', 'Сите'],
            ['flt', 'flt.body_type', 'Body type', 'Каросерија'],
            ['flt', 'flt.drivetrain', 'Drivetrain', 'Погон'],
            ['flt', 'flt.power', 'Power (hp)', 'Моќност (кс)'],
            ['flt', 'flt.seller', 'Seller', 'Продавач'],
            ['flt', 'flt.dealer', 'Dealer', 'Автосалон'],
            ['flt', 'flt.private', 'Private', 'Приватен'],
            ['flt', 'flt.equipment', 'Equipment', 'Опрема'],
            ['flt', 'flt.show_all', 'Show all', 'Прикажи ги сите'],
            ['flt', 'flt.show_less', 'Show less', 'Прикажи помалку'],
            ['flt', 'flt.apply', 'Apply filters', 'Примени филтри'],
            ['flt', 'flt.save_title', 'Save this search', 'Зачувај го пребарувањето'],
            ['flt', 'flt.save_text', 'Get notified about new listings that match.', 'Добивај известувања за нови огласи што одговараат.'],
            ['flt', 'flt.save_btn', 'Save search', 'Зачувај пребарување'],

            // ── Vehicle detail ───────────────────────────────────────────
            ['show', 'show.manage', 'You manage this listing', 'Вие управувате со овој оглас'],
            ['show', 'show.edit', 'Edit listing', 'Измени оглас'],
            ['show', 'show.specs', 'Specifications', 'Спецификации'],
            ['show', 'show.equipment', 'Equipment', 'Опрема'],
            ['show', 'show.description', 'Description', 'Опис'],
            ['show', 'show.featured_listing', 'Featured listing', 'Издвоен оглас'],
            ['show', 'show.show_phone', 'Show phone number', 'Прикажи телефон'],
            ['show', 'show.no_phone', 'No phone provided', 'Нема телефон'],
            ['show', 'show.message_seller', 'Message the seller', 'Порака до продавачот'],
            ['show', 'show.send_message', 'Send message', 'Испрати порака'],
            ['show', 'show.signin_message', 'Sign in to message', 'Најави се за порака'],
            ['show', 'show.seller', 'Seller', 'Продавач'],
            ['show', 'show.verified_dealer', 'Verified dealer', 'Проверен автосалон'],
            ['show', 'show.views', 'views', 'прегледи'],
            ['show', 'show.listed', 'Listed', 'Објавено'],
            ['show', 'show.similar', 'Similar vehicles', 'Слични возила'],
            ['show', 'show.contact_prefix', 'Hi, is the', 'Здраво, дали'],
            ['show', 'show.contact_suffix', 'still available?', 'е сè уште достапно?'],
            ['spec', 'spec.Year', 'Year', 'Година'],
            ['spec', 'spec.Mileage', 'Mileage', 'Километража'],
            ['spec', 'spec.Fuel', 'Fuel', 'Гориво'],
            ['spec', 'spec.Transmission', 'Transmission', 'Менувач'],
            ['spec', 'spec.Engine', 'Engine', 'Мотор'],
            ['spec', 'spec.Power', 'Power', 'Моќност'],
            ['spec', 'spec.Body type', 'Body type', 'Каросерија'],
            ['spec', 'spec.Drivetrain', 'Drivetrain', 'Погон'],
            ['spec', 'spec.Doors', 'Doors', 'Врати'],
            ['spec', 'spec.Seats', 'Seats', 'Седишта'],
            ['spec', 'spec.Colour', 'Colour', 'Боја'],
            ['spec', 'spec.Condition', 'Condition', 'Состојба'],
            ['spec', 'spec.Owners', 'Owners', 'Сопственици'],
            ['spec', 'spec.Registered until', 'Registered until', 'Регистрирано до'],

            // ── Dealers ──────────────────────────────────────────────────
            ['dlr', 'dlr.kicker', 'Directory · Verified sellers', 'Именик · Проверени продавачи'],
            ['dlr', 'dlr.sub', 'Browse verified dealerships across Macedonia. Real inventory, real photos, direct contact — no duplicate listings.', 'Разгледај проверени автосалони низ Македонија. Вистински инвентар, вистински фотографии, директен контакт — без дупликат огласи.'],
            ['dlr', 'dlr.find', 'Find a dealer', 'Најди автосалон'],
            ['dlr', 'dlr.search_ph', 'Search by name or city', 'Пребарај по име или град'],
            ['dlr', 'dlr.all', 'All dealers', 'Сите автосалони'],
            ['dlr', 'dlr.count', 'dealers', 'автосалони'],
            ['dlr', 'dlr.empty', 'No dealers match your search.', 'Нема автосалони што одговараат.'],
            ['dlr', 'dlr.est', 'Est.', 'Осн.'],
            ['dlr', 'dsh.follow', 'Follow', 'Следи'],
            ['dlr', 'dsh.contact', 'Contact', 'Контакт'],
            ['dlr', 'dsh.since', 'Since', 'Од'],
            ['dlr', 'dsh.stat_active', 'vehicles for sale', 'возила на продажба'],
            ['dlr', 'dsh.stat_sold', 'sold', 'продадени'],
            ['dlr', 'dsh.stat_rating', 'avg. rating', 'просечна оцена'],
            ['dlr', 'dsh.stat_years', 'years on market', 'години на пазар'],
            ['dlr', 'dsh.for_sale', 'Vehicles for sale', 'Возила на продажба'],
            ['dlr', 'dsh.sort', 'Sort', 'Подреди'],
            ['dlr', 'dsh.empty', 'This dealer has no active listings right now.', 'Овој автосалон нема активни огласи во моментов.'],
            ['dlr', 'dsh.about', 'About', 'За нас'],
            ['dlr', 'dsh.phone', 'Phone', 'Телефон'],
            ['dlr', 'dsh.hours', 'Hours', 'Работно време'],
            ['dlr', 'dsh.website', 'Website', 'Веб-страница'],
            ['dlr', 'dsh.address', 'Address', 'Адреса'],
            ['dlr', 'dsh.map', 'Map — location', 'Мапа — локација'],

            // ── Pricing ──────────────────────────────────────────────────
            ['prc', 'prc.kicker', 'For dealers', 'За дилери'],
            ['prc', 'prc.title', 'Your whole inventory in one place.', 'Целиот твој инвентар на едно место.'],
            ['prc', 'prc.sub', 'Simple monthly plans for dealerships of every size. Start free, upgrade when your stock grows — no long contracts, cancel anytime.', 'Едноставни месечни планови за автосалони од секоја големина. Почни бесплатно, надгради кога ќе порасне понудата — без долги договори, откажи во секое време.'],
            ['prc', 'prc.popular', 'Popular', 'Популарно'],
            ['prc', 'prc.promos_kicker', 'Boost a single listing', 'Истакни поединечен оглас'],
            ['prc', 'prc.promos_title', 'Promote a listing: Bump, Featured, Homepage.', 'Промовирај оглас: Подигни, Издвоено, Насловна.'],

            // ── Auth ─────────────────────────────────────────────────────
            ['auth', 'auth.account', 'Account', 'Профил'],
            ['auth', 'auth.signin_sub', 'One account for private sellers and dealers.', 'Еден профил за приватни продавачи и автосалони.'],
            ['auth', 'auth.email', 'Email', 'Е-пошта'],
            ['auth', 'auth.password', 'Password', 'Лозинка'],
            ['auth', 'auth.remember', 'Remember me', 'Запомни ме'],
            ['auth', 'auth.forgot', 'Forgot password?', 'Заборавена лозинка?'],
            ['auth', 'auth.new_here', 'New here?', 'Нов си овде?'],
            ['auth', 'auth.register', 'Register', 'Регистрирај се'],
            ['auth', 'auth.demo', 'Demo accounts · password: password', 'Демо профили · лозинка: password'],
            ['auth', 'auth.promo_kicker', 'Why an account', 'Зошто профил'],
            ['auth', 'auth.promo_title', 'Saved searches, alerts and messages in one place.', 'Зачувани пребарувања, известувања и пораки на едно место.'],
            ['auth', 'auth.promo_text', 'Follow the vehicles you care about, get notified when the price drops, and message sellers directly — all from a single AutoNova account.', 'Следи ги возилата што те интересираат, добивај известување кога паѓа цената и пиши им директно на продавачите — сè од еден AutoNova профил.'],
            ['auth', 'auth.reg_kicker', 'Register', 'Регистрација'],
            ['auth', 'auth.create_title', 'Create your account', 'Создади профил'],
            ['auth', 'auth.reg_sub', 'Sell privately or run your dealership — one place for everything.', 'Продавај приватно или води автосалон — сè на едно место.'],
            ['auth', 'auth.account_type', 'Account type', 'Тип на профил'],
            ['auth', 'auth.name', 'Name', 'Име'],
            ['auth', 'auth.dealership_name', 'Dealership name', 'Име на автосалон'],
            ['auth', 'auth.phone', 'Phone', 'Телефон'],
            ['auth', 'auth.select_city', 'Select city', 'Избери град'],
            ['auth', 'auth.confirm_password', 'Confirm password', 'Потврди лозинка'],
            ['auth', 'auth.create_account', 'Create account', 'Создади профил'],
            ['auth', 'auth.have_account', 'Already have an account?', 'Веќе имаш профил?'],
            ['auth', 'auth.reg_promo_title', 'Dealers get a profile, inventory and per-listing analytics.', 'Автосалоните добиваат профил, инвентар и аналитика по оглас.'],
            ['auth', 'auth.reg_promo_text', 'Import your stock, keep every listing in one dashboard, and see exactly how each vehicle performs. Private sellers keep it simple — list a car in minutes.', 'Увези го инвентарот, чувај ги сите огласи на едно место и види точно како се однесува секое возило. Приватните продавачи го имаат едноставно — објави возило за неколку минути.'],

            // ── Body / sub types ─────────────────────────────────────────
            ['body', 'body.Hatchback', 'Hatchback', 'Хечбек'],
            ['body', 'body.SUV', 'SUV', 'Џип (SUV)'],
            ['body', 'body.Sedan', 'Sedan', 'Седан'],
            ['body', 'body.Estate', 'Estate', 'Караван'],
            ['body', 'body.Coupe', 'Coupe', 'Купе'],
            ['body', 'body.Convertible', 'Convertible', 'Кабриолет'],
            ['body', 'body.Minivan', 'Minivan', 'Миниван'],
            ['body', 'body.Pickup', 'Pickup', 'Пикап'],
            ['body', 'body.Sport', 'Sport', 'Спорт'],
            ['body', 'body.Naked', 'Naked', 'Нејкед'],
            ['body', 'body.Cruiser', 'Cruiser', 'Крузер'],
            ['body', 'body.Touring', 'Touring', 'Туринг'],
            ['body', 'body.Enduro', 'Enduro', 'Ендуро'],
            ['body', 'body.Cross', 'Cross', 'Крос'],
            ['body', 'body.Scooter', 'Scooter', 'Скутер'],
            ['body', 'body.Chopper', 'Chopper', 'Чопер'],
            ['body', 'body.Panel van', 'Panel van', 'Комбе'],
            ['body', 'body.Combi', 'Combi', 'Комби'],
            ['body', 'body.Minibus', 'Minibus', 'Минибус'],
            ['body', 'body.Box', 'Box', 'Сандак'],
            ['body', 'body.Chassis cab', 'Chassis cab', 'Шасија со кабина'],
            ['body', 'body.Tractor unit', 'Tractor unit', 'Влекач'],
            ['body', 'body.Box truck', 'Box truck', 'Сандак камион'],
            ['body', 'body.Tipper', 'Tipper', 'Кипер'],
            ['body', 'body.Flatbed', 'Flatbed', 'Платформа'],
            ['body', 'body.Refrigerated', 'Refrigerated', 'Ладилник'],
            ['body', 'body.Chassis', 'Chassis', 'Шасија'],
            ['body', 'body.Tractor', 'Tractor', 'Трактор'],
            ['body', 'body.Excavator', 'Excavator', 'Багер'],
            ['body', 'body.Loader', 'Loader', 'Утоварувач'],
            ['body', 'body.Forklift', 'Forklift', 'Виљушкар'],
            ['body', 'body.Combine', 'Combine', 'Комбајн'],
            ['body', 'body.Bulldozer', 'Bulldozer', 'Булдожер'],
            ['body', 'body.Curtainsider', 'Curtainsider', 'Церада'],
            ['body', 'body.Car transporter', 'Car transporter', 'Автотранспортер'],
            ['body', 'body.Caravan', 'Caravan', 'Караван'],
            ['body', 'body.Camper', 'Camper', 'Кампер'],
        ];
    }
}
