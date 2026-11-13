import { describe, expect, it } from 'vitest';
import { formatMileage, formatPrice, formatText } from './formatters';

describe('formatPrice', () => {
    it('formats a price in whole Canadian dollars', () => {
        expect(formatPrice(24995)).toBe('$24,995');
    });

    it.each([null, undefined, ''])('shows a dash instead of $0 when the price is %s', (price) => {
        expect(formatPrice(price)).toBe('—');
    });
});

describe('formatMileage', () => {
    it('formats a mileage in kilometres', () => {
        expect(formatMileage(41200)).toBe('41,200 km');
    });

    it('keeps a real zero for a brand new car', () => {
        expect(formatMileage(0)).toBe('0 km');
    });

    it.each([null, undefined])('shows a dash when the mileage is %s', (mileage) => {
        expect(formatMileage(mileage)).toBe('—');
    });
});

describe('formatText', () => {
    it('keeps a value that is present', () => {
        expect(formatText('Soul Red Crystal')).toBe('Soul Red Crystal');
    });

    it.each([null, undefined, '', '   '])('shows a dash instead of "%s"', (value) => {
        expect(formatText(value)).toBe('—');
    });
});
