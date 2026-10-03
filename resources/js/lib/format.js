const grouped = new Intl.NumberFormat('de-DE');

export const num = (n) => grouped.format(Math.round(Number(n) || 0));
export const eur = (n) => '€' + grouped.format(Math.round(Number(n) || 0));
export const mkd = (n) => grouped.format(Math.round(Number(n) || 0)) + ' MKD';
export const km = (n) => grouped.format(Math.round(Number(n) || 0)) + ' km';

export const statusLabel = {
    draft: 'Draft',
    pending: 'Pending review',
    active: 'Active',
    sold: 'Sold',
    rejected: 'Rejected',
    expired: 'Expired',
};

export const statusClass = {
    draft: 'tag-outline',
    pending: 'tag-warning',
    active: 'tag-success',
    sold: 'tag-neutral',
    rejected: 'tag-accent',
    expired: 'tag-neutral',
};
