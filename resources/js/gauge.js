/**
 * How full a gauge is, from 0 (empty) to 1 (full): the part of the target the value has reached.
 * A missing value or a target that is not positive leaves the gauge empty.
 */
export const gaugeFraction = (value, target) => {
    if (!Number.isFinite(value) || !Number.isFinite(target) || target <= 0) {
        return 0;
    }

    return Math.min(1, Math.max(0, value / target));
};
