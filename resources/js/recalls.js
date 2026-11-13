const GENERIC_ERROR = 'The recalls could not be loaded. Try again later.';

export const recallCountLabel = (count) => `${count} safety ${count === 1 ? 'recall' : 'recalls'}`;

/**
 * Recalls are looked up by make, model and year, not by VIN: the message names the vehicle
 * NHTSA was asked about, so it does not read as a statement about this one car.
 */
export const noRecallMessage = ({ year, make, model }) =>
    `NHTSA lists no safety recalls for the ${year} ${make} ${model}.`;

export const recallErrorMessage = (body) => body?.message || GENERIC_ERROR;
