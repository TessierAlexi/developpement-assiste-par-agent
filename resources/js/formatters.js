const priceFormatter = new Intl.NumberFormat('en-CA', {
    style: 'currency',
    currency: 'CAD',
    maximumFractionDigits: 0,
});

const numberFormatter = new Intl.NumberFormat('en-CA');

export const formatPrice = (dollars) => priceFormatter.format(dollars);

export const formatMileage = (kilometres) => `${numberFormatter.format(kilometres)} km`;

export const carTitle = (car) => `${car.year} ${car.make} ${car.model}`;

const dateFormatter = new Intl.DateTimeFormat('en-CA', { dateStyle: 'medium' });

export const formatDate = (isoDate) => {
    const [year, month, day] = isoDate.split('-').map(Number);

    return dateFormatter.format(new Date(year, month - 1, day));
};
