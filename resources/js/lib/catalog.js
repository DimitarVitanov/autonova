const cache = new Map();

/**
 * Makes of a category with their models and versions nested —
 * [{id, name, models: [{id, name, versions: [string]}]}].
 * Fetched once per category and shared by every make & model picker on the page.
 */
export function loadCatalog(slug) {
    if (!slug) return Promise.resolve([]);
    if (!cache.has(slug)) {
        cache.set(slug, window.axios.get(route('api.catalog'), { params: { category: slug } })
            .then(({ data }) => data)
            .catch(() => { cache.delete(slug); return []; }));
    }
    return cache.get(slug);
}
