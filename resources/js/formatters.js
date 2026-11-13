const priceFormatter = new Intl.NumberFormat('en-CA', {
    style: 'currency',
    currency: 'CAD',
    maximumFractionDigits: 0,
});

const numberFormatter = new Intl.NumberFormat('en-CA');

export const formatPrice = (dollars) => priceFormatter.format(dollars);

export const formatMileage = (kilometres) => `${numberFormatter.format(kilometres)} km`;

export const carTitle = (car) => `${car.year} ${car.make} ${car.model}`;
