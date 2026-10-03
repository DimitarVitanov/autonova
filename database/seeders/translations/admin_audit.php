<?php

/**
 * Admin area + audit of already-translated public pages.
 * Rows: [group, key, english, macedonian]
 */
return [
    // ── Admin navigation ─────────────────────────────────────────────
    ['adm', 'adm.staff_area', 'Staff area', 'Администрација'],
    ['adm', 'adm.overview', 'Overview', 'Преглед'],
    ['adm', 'adm.listings', 'Listings', 'Огласи'],
    ['adm', 'adm.users', 'Users', 'Корисници'],
    ['adm', 'adm.categories', 'Categories', 'Категории'],
    ['adm', 'adm.translations', 'Translations', 'Преводи'],

    // ── Admin dashboard ──────────────────────────────────────────────
    ['adm', 'adm.moderation', 'Moderation', 'Модерација'],
    ['adm', 'adm.moderation_overview', 'Moderation overview', 'Преглед на модерација'],
    ['adm', 'adm.pending_review', 'Pending review', 'Чекаат одобрување'],
    ['adm', 'adm.stat_active', 'Active', 'Активни'],
    ['adm', 'adm.stat_total', 'Total listings', 'Вкупно огласи'],
    ['adm', 'adm.stat_sold', 'Sold', 'Продадени'],
    ['adm', 'adm.stat_views', 'Views', 'Прегледи'],
    ['adm', 'adm.in_queue', 'in queue', 'на чекање'],
    ['adm', 'adm.vehicle', 'Vehicle', 'Возило'],
    ['adm', 'adm.price', 'Price', 'Цена'],
    ['adm', 'adm.submitted', 'Submitted', 'Поднесено'],
    ['adm', 'adm.actions', 'Actions', 'Дејства'],
    ['adm', 'adm.view', 'View', 'Погледни'],
    ['adm', 'adm.approve', 'Approve', 'Одобри'],
    ['adm', 'adm.reject', 'Reject', 'Одбиј'],
    ['adm', 'adm.reject_prompt', 'Reason for rejection?', 'Причина за одбивање?'],
    ['adm', 'adm.queue_clear', 'Queue is clear', 'Редот е празен'],
    ['adm', 'adm.queue_clear_text', 'No listings are waiting for review right now.', 'Во моментов нема огласи што чекаат одобрување.'],

    // ── Admin listings ───────────────────────────────────────────────
    ['adm', 'adm.listings_search_ph', 'Search by title, seller, city…', 'Пребарај по наслов, продавач, град…'],
    ['adm', 'adm.status', 'Status', 'Статус'],
    ['adm', 'adm.feature', 'Feature', 'Издвој'],
    ['adm', 'adm.delete', 'Delete', 'Избриши'],
    ['adm', 'adm.delete_confirm', 'Delete this listing permanently? This cannot be undone.', 'Да се избрише огласот трајно? Ова не може да се врати.'],
    ['adm', 'adm.no_listings', 'No listings', 'Нема огласи'],
    ['adm', 'adm.no_match', 'Nothing matches this filter.', 'Ништо не одговара на овој филтер.'],

    // ── Admin users ──────────────────────────────────────────────────
    ['adm', 'adm.people', 'People', 'Луѓе'],
    ['adm', 'adm.users_search_ph', 'Search by name or email…', 'Пребарај по име или е-пошта…'],
    ['adm', 'adm.all_roles', 'All roles', 'Сите улоги'],
    ['adm', 'adm.user', 'User', 'Корисник'],
    ['adm', 'adm.type', 'Type', 'Тип'],
    ['adm', 'adm.joined', 'Joined', 'Регистриран'],
    ['adm', 'adm.role', 'Role', 'Улога'],
    ['adm', 'adm.active', 'Active', 'Активен'],
    ['adm', 'adm.suspended', 'Suspended', 'Суспендиран'],
    ['adm', 'adm.suspend', 'Suspend', 'Суспендирај'],
    ['adm', 'adm.activate', 'Activate', 'Активирај'],
    ['adm', 'adm.no_users', 'No users', 'Нема корисници'],
    ['adm', 'adm.no_users_text', 'No accounts match this filter.', 'Нема профили што одговараат на овој филтер.'],

    // ── Admin categories ─────────────────────────────────────────────
    ['adm', 'adm.taxonomy', 'Taxonomy', 'Таксономија'],
    ['adm', 'adm.makes_word', 'makes', 'марки'],
    ['adm', 'adm.listings_word', 'listings', 'огласи'],
    ['adm', 'adm.active_word', 'active', 'активни'],
    ['adm', 'adm.name_plural', 'Name (plural)', 'Име (множина)'],
    ['adm', 'adm.hint', 'Hint', 'Краток опис'],
    ['adm', 'adm.save', 'Save', 'Зачувај'],

    // ── Admin translations ───────────────────────────────────────────
    ['adm', 'adm.localisation', 'Localisation', 'Локализација'],
    ['adm', 'adm.translations_sub', 'Edit the Macedonian text shown across the site. Leave a field blank to fall back to the English source.', 'Измени го македонскиот текст што се прикажува низ страницата. Остави го полето празно за да се користи англискиот изворен текст.'],
    ['adm', 'adm.saved', 'Saved ✓', 'Зачувано ✓'],
    ['adm', 'adm.save_changes', 'Save changes', 'Зачувај промени'],

    // ── Audit: public pages ──────────────────────────────────────────
    ['common', 'common.no_photo', 'No photo', 'Нема фотографија'],
    ['nav', 'nav.close_menu', 'Close menu', 'Затвори мени'],
    ['nav', 'nav.menu', 'Menu', 'Мени'],
    ['idx', 'idx.save_prompt', 'Name this saved search', 'Именувај го зачуваното пребарување'],
    ['idx', 'idx.search_title', 'search', 'пребарување'],
    ['flt', 'flt.mileage_ph', 'e.g. 200000', 'пр. 200000'],
    ['show', 'show.photo', 'photo', 'фотографија'],

    // ── Promo popup (keys were already in PromoPopup.vue but never seeded) ──
    ['promo', 'promo.close', 'Close', 'Затвори'],
    ['promo', 'promo.ribbon', 'Launch week', 'Недела на лансирање'],
    ['promo', 'promo.eyebrow', 'For dealers', 'За автосалони'],
    ['promo', 'promo.visual_headline', "Sell faster.\nStand out.", "Продавај побрзо.\nИстакни се."],
    ['promo', 'promo.most_popular', 'Most popular', 'Најпопуларен'],
    ['promo', 'promo.title', 'Put your inventory in front of every buyer', 'Стави го твојот инвентар пред секој купувач'],
    ['promo', 'promo.sub', 'Upgrade to PRO and get homepage placement, unlimited reach and the tools serious dealers use to close deals.', 'Надгради на PRO и добиј место на насловната, неограничен досег и алатките што ги користат сериозните автосалони за да продаваат.'],
    ['promo', 'promo.feat_1', 'Homepage placement & priority ranking', 'Место на насловната и приоритетно рангирање'],
    ['promo', 'promo.feat_2', 'Bulk XML / CSV inventory import', 'Групен увоз на инвентар преку XML / CSV'],
    ['promo', 'promo.feat_3', '10 promotion credits every month', '10 кредити за промоција секој месец'],
    ['promo', 'promo.feat_4', 'Branded profile with logo & banner', 'Брендиран профил со лого и банер'],
    ['promo', 'promo.per', '/ month · up to 60 listings', '/ месец · до 60 огласи'],
    ['promo', 'promo.cta', 'See packages', 'Види пакети'],
    ['promo', 'promo.dismiss', 'Maybe later', 'Можеби подоцна'],
];
