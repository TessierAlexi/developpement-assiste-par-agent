import { describe, expect, it } from 'vitest';
import { noRecallMessage, recallCountLabel, recallErrorMessage } from './recalls';

describe('recallCountLabel', () => {
    it('counts a single recall in the singular', () => {
        expect(recallCountLabel(1)).toBe('1 safety recall');
    });

    it('counts several recalls in the plural', () => {
        expect(recallCountLabel(3)).toBe('3 safety recalls');
    });
});

describe('noRecallMessage', () => {
    it('names the vehicle NHTSA was asked about, since the search is not by VIN', () => {
        expect(noRecallMessage({ year: 2021, make: 'Mazda', model: 'MX-5' })).toBe(
            'NHTSA lists no safety recalls for the 2021 Mazda MX-5.',
        );
    });
});

describe('recallErrorMessage', () => {
    it('shows the message sent by the server', () => {
        expect(recallErrorMessage({ message: 'Too many recall checks. Wait a minute and try again.' })).toBe(
            'Too many recall checks. Wait a minute and try again.',
        );
    });

    it.each([
        ['there is no body', null],
        ['the body has no message', {}],
        ['the message is empty', { message: '' }],
    ])('falls back to a generic message when %s', (_, body) => {
        expect(recallErrorMessage(body)).toBe('The recalls could not be loaded. Try again later.');
    });
});
