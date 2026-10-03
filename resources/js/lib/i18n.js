import { usePage } from '@inertiajs/vue3';

/**
 * Translate a UI key. Values come from the DB (page.props.i18n.strings) and are
 * editable by admins. Falls back to the English text passed in code, so any
 * un-seeded key still renders sensibly.
 *
 *   t('nav.vehicles', 'Vehicles')
 */
export function t(key, fallback = '') {
    const strings = usePage().props?.i18n?.strings || {};
    const value = strings[key];
    return value !== undefined && value !== null && value !== '' ? value : (fallback || key);
}
