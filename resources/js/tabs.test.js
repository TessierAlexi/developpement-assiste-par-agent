import { describe, expect, it } from 'vitest';
import { tabIndexAfterKey } from './tabs';

describe('tabIndexAfterKey', () => {
    it('moves to the next tab with the right arrow', () => {
        expect(tabIndexAfterKey('ArrowRight', 2, 7)).toBe(3);
    });

    it('moves to the previous tab with the left arrow', () => {
        expect(tabIndexAfterKey('ArrowLeft', 2, 7)).toBe(1);
    });

    it('wraps from the last tab to the first', () => {
        expect(tabIndexAfterKey('ArrowRight', 6, 7)).toBe(0);
    });

    it('wraps from the first tab to the last', () => {
        expect(tabIndexAfterKey('ArrowLeft', 0, 7)).toBe(6);
    });

    it('jumps to the first tab with Home and to the last with End', () => {
        expect(tabIndexAfterKey('Home', 4, 7)).toBe(0);
        expect(tabIndexAfterKey('End', 4, 7)).toBe(6);
    });

    it.each(['Enter', 'Tab', 'a', 'ArrowDown'])('leaves the %s key alone', (key) => {
        expect(tabIndexAfterKey(key, 2, 7)).toBeNull();
    });
});
