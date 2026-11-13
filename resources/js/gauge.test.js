import { describe, expect, it } from 'vitest';
import { gaugeFraction } from './gauge';

describe('gaugeFraction', () => {
    it('gives the part of the target that is reached', () => {
        expect(gaugeFraction(15, 60)).toBe(0.25);
    });

    it('fills the gauge when the value reaches the target', () => {
        expect(gaugeFraction(10, 10)).toBe(1);
    });

    it('does not go past a full gauge when the value exceeds the target', () => {
        expect(gaugeFraction(95, 60)).toBe(1);
    });

    it('leaves the gauge empty for a negative value', () => {
        expect(gaugeFraction(-5, 60)).toBe(0);
    });

    it.each([null, undefined, Number.NaN])('leaves the gauge empty when the value is %s', (value) => {
        expect(gaugeFraction(value, 60)).toBe(0);
    });

    it.each([0, -10, null])('leaves the gauge empty when the target is %s', (target) => {
        expect(gaugeFraction(5, target)).toBe(0);
    });
});
