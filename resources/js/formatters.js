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

const oneDecimalFormatter = new Intl.NumberFormat('en-CA', {
    minimumFractionDigits: 1,
    maximumFractionDigits: 1,
});

export const formatConsumption = (litresPer100Km) =>
    isMissing(litresPer100Km) ? MISSING : `${oneDecimalFormatter.format(litresPer100Km)} L/100 km`;

export const formatDisplacement = (litres) =>
    isMissing(litres) ? MISSING : `${oneDecimalFormatter.format(litres)} L`;

export const carTitle = (car) => `${car.year} ${car.make} ${car.model}`;

const dateFormatter = new Intl.DateTimeFormat('en-CA', { dateStyle: 'medium' });

export const formatDate = (isoDate) => {
    if (isMissing(isoDate)) {
        return MISSING;
    }

    const [year, month, day] = isoDate.split('-').map(Number);

    return dateFormatter.format(new Date(year, month - 1, day));
};

const dateTimeFormatter = new Intl.DateTimeFormat('en-CA', { dateStyle: 'medium', timeStyle: 'short' });

export const formatDateTime = (isoTimestamp) =>
    isMissing(isoTimestamp) ? MISSING : dateTimeFormatter.format(new Date(isoTimestamp));
