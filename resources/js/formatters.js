const priceFormatter = new Intl.NumberFormat('en-CA', {
    style: 'currency',
    currency: 'CAD',
    maximumFractionDigits: 0,
});

const numberFormatter = new Intl.NumberFormat('en-CA');

const MISSING = '—';

const isMissing = (value) => value == null || String(value).trim() === '';

export const formatText = (value) => (isMissing(value) ? MISSING : value);

export const formatPrice = (dollars) => (isMissing(dollars) ? MISSING : priceFormatter.format(dollars));

export const formatMileage = (kilometres) =>
    isMissing(kilometres) ? MISSING : `${numberFormatter.format(kilometres)} km`;

export const carTitle = (car) => `${car.year} ${car.make} ${car.model}`;

const dateFormatter = new Intl.DateTimeFormat('en-CA', { dateStyle: 'medium' });

export const formatDate = (isoDate) => {
    if (isMissing(isoDate)) {
        return MISSING;
    }

    const [year, month, day] = isoDate.split('-').map(Number);

    return dateFormatter.format(new Date(year, month - 1, day));
};
