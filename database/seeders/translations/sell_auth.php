<?php

/**
 * UI strings for the sell wizard / edit listing, image uploader, the remaining
 * auth screens, the guest layout and the pricing page.
 *
 * Rows: [group, key, english, macedonian]
 */
return [
    // ── Sell wizard (Vehicles/Create) + Edit listing ─────────────────────
    ['sell', 'sell.title', 'Sell your vehicle', 'Продади возило'],
    ['sell', 'sell.step', 'Step', 'Чекор'],
    ['sell', 'sell.of', 'of', 'од'],
    ['sell', 'sell.free_30', 'Free listing for 30 days', 'Бесплатен оглас 30 дена'],
    ['sell', 'sell.step_details', 'Details', 'Детали'],
    ['sell', 'sell.photos', 'Photos', 'Фотографии'],
    ['sell', 'sell.promotion', 'Promotion', 'Промоција'],
    ['sell', 'sell.fix_errors', 'Please fix the following:', 'Те молиме поправи го следново:'],
    ['sell', 'sell.what_selling', 'What are you selling?', 'Што продаваш?'],
    ['sell', 'sell.basic_details', 'Basic details', 'Основни податоци'],
    ['sell', 'sell.select_make', 'Select make', 'Избери марка'],
    ['sell', 'sell.select_model', 'Select model', 'Избери модел'],
    ['sell', 'sell.version_trim', 'Version / trim', 'Верзија / пакет опрема'],
    ['sell', 'sell.version', 'Version', 'Верзија'],
    ['sell', 'sell.version_ph', 'e.g. 2.0 TDI Elegance', 'пр. 2.0 TDI Elegance'],
    ['sell', 'sell.mileage_km', 'Mileage (km)', 'Километража (км)'],
    ['sell', 'sell.engine_cc', 'Engine (cm³)', 'Мотор (см³)'],
    ['sell', 'sell.contact_phone', 'Contact phone', 'Телефон за контакт'],
    ['sell', 'sell.photos_hint', 'The first photo is the cover. Every photo is automatically resized to an identical 1600×1200 WebP so your listing looks clean. Minimum 1 real photo.', 'Првата фотографија е насловна. Секоја фотографија автоматски се прилагодува на иста големина 1600×1200 WebP за огласот да изгледа уредно. Најмалку 1 вистинска фотографија.'],
    ['sell', 'sell.equip_desc', 'Equipment & description', 'Опрема и опис'],
    ['sell', 'sell.desc_ph', 'Describe the condition, service history, extras…', 'Опиши ја состојбата, сервисната историја, додатната опрема…'],
    ['sell', 'sell.back', 'Back', 'Назад'],
    ['sell', 'sell.continue', 'Continue', 'Продолжи'],
    ['sell', 'sell.publishing', 'Publishing…', 'Се објавува…'],
    ['sell', 'sell.publish', 'Publish listing', 'Објави оглас'],
    ['sell', 'sell.edit_prefix', 'Edit', 'Измени'],
    ['sell', 'sell.view_listing', 'View listing', 'Види оглас'],
    ['sell', 'sell.add_more', 'Add more photos (auto-resized to 1600×1200 WebP):', 'Додај уште фотографии (автоматски се прилагодуваат на 1600×1200 WebP):'],
    ['sell', 'sell.cancel', 'Cancel', 'Откажи'],
    ['sell', 'sell.saving', 'Saving…', 'Се зачувува…'],
    ['sell', 'sell.save_changes', 'Save changes', 'Зачувај промени'],

    // ── Image uploader ───────────────────────────────────────────────────
    ['upl', 'upl.only', 'Only', 'Дозволени се само'],
    ['upl', 'upl.max_skipped', 'photos are allowed — the rest were skipped.', 'фотографии — останатите се прескокнати.'],
    ['upl', 'upl.too_small', 'is too small — photos must be at least', 'е премала — фотографиите мора да бидат најмалку'],
    ['upl', 'upl.unreadable', 'could not be read — please use a JPG, PNG or WEBP photo.', 'не може да се прочита — користи JPG, PNG или WEBP фотографија.'],
    ['upl', 'upl.drop', 'Drop photos here or', 'Пушти ги фотографиите овде или'],
    ['upl', 'upl.browse', 'browse', 'избери од уредот'],
    ['upl', 'upl.up_to', 'up to', 'до'],
    ['upl', 'upl.min', 'min', 'мин.'],
    ['upl', 'upl.auto_resized', 'auto-resized to', 'автоматски се прилагодуваат на'],
    ['upl', 'upl.optimising', 'Optimising photos…', 'Се оптимизираат фотографиите…'],
    ['upl', 'upl.preview', 'preview', 'преглед'],
    ['upl', 'upl.cover', 'Cover', 'Насловна'],
    ['upl', 'upl.make_cover', 'Make cover', 'Постави како насловна'],
    ['upl', 'upl.remove', 'Remove', 'Отстрани'],
    ['upl', 'upl.empty', 'No photos added yet — the first photo becomes the cover.', 'Сè уште нема додадени фотографии — првата фотографија станува насловна.'],

    // ── Auth: confirm / forgot / reset / verify + guest layout ───────────
    ['auth', 'auth.confirm_title', 'Confirm Password', 'Потврди лозинка'],
    ['auth', 'auth.confirm_intro', 'This is a secure area of the application. Please confirm your password before continuing.', 'Ова е заштитен дел од апликацијата. Потврди ја лозинката пред да продолжиш.'],
    ['auth', 'auth.confirm', 'Confirm', 'Потврди'],
    ['auth', 'auth.forgot_title', 'Forgot Password', 'Заборавена лозинка'],
    ['auth', 'auth.forgot_intro', 'Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.', 'Ја заборави лозинката? Нема проблем. Внеси ја твојата е-пошта и ќе ти испратиме линк преку кој ќе можеш да избереш нова лозинка.'],
    ['auth', 'auth.send_reset', 'Email Password Reset Link', 'Испрати линк за нова лозинка'],
    ['auth', 'auth.reset_title', 'Reset Password', 'Нова лозинка'],
    ['auth', 'auth.reset_btn', 'Reset Password', 'Смени лозинка'],
    ['auth', 'auth.verify_title', 'Email Verification', 'Потврда на е-пошта'],
    ['auth', 'auth.verify_intro', 'Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn’t receive the email, we will gladly send you another.', 'Ти благодариме за регистрацијата! Пред да почнеш, потврди ја твојата е-пошта со клик на линкот што штотуку ти го испративме. Ако не ја доби пораката, со задоволство ќе ти испратиме нова.'],
    ['auth', 'auth.verify_sent', 'A new verification link has been sent to the email address you provided during registration.', 'Нов линк за потврда е испратен на е-поштата што ја внесе при регистрацијата.'],
    ['auth', 'auth.resend', 'Resend Verification Email', 'Испрати повторно порака за потврда'],
    ['auth', 'auth.guest_foot', 'Vehicle marketplace', 'Пазар за возила'],

    // ── Pricing ──────────────────────────────────────────────────────────
    ['prc', 'prc.head_title', 'Packages & pricing', 'Пакети и цени'],
    ['sell', 'sell.select_version', 'Select version', 'Избери верзија'],
    ['sell', 'sell.version_other', 'Other — type it in', 'Друго — внеси рачно'],
];
