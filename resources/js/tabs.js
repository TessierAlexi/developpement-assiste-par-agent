/**
 * Keyboard navigation of a tab list: which tab gets the focus after a key press.
 * Returns null for keys that do not move between tabs, so the browser handles them.
 */
export const tabIndexAfterKey = (key, currentIndex, tabCount) => {
    switch (key) {
        case 'ArrowRight':
            return (currentIndex + 1) % tabCount;
        case 'ArrowLeft':
            return (currentIndex - 1 + tabCount) % tabCount;
        case 'Home':
            return 0;
        case 'End':
            return tabCount - 1;
        default:
            return null;
    }
};
