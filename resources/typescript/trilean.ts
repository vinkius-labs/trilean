/*
 * Lightweight TypeScript helpers that mirror the PHP ternary API.
 */

/** @typedef {'true'|'false'|'unknown'} TernaryState */

/**
 * @param {unknown} value
 * @returns {TernaryState}
 */
export function ternary(value) {
    if (value === true || value === 'true' || value === 1 || value === '1') {
        return 'true'
    }

    if (value === false || value === 'false' || value === 0 || value === '0') {
        return 'false'
    }

    return 'unknown'
}
/**
 * @template T
 * @param {unknown} value
 * @param {{[K in TernaryState]?: T, any?: (state: TernaryState) => T}} map
 * @returns {T|undefined}
 */
export function ternaryMatch(value, map) {
    const state = ternary(value)

    if (typeof map[state] !== 'undefined') {
        return map[state]
    }

    if (typeof map.any === 'function') {
        return map.any(state)
    }

    return undefined
}

/**
 * @param {unknown[]} values
 * @returns {boolean}
 */
export function allTrue(values) {
    return values.every((value) => ternary(value) === 'true')
}

/**
 * @param {unknown[]} values
 * @returns {boolean}
 */
export function anyTrue(values) {
    return values.some((value) => ternary(value) === 'true')
}

/**
 * @param {unknown[]} values
 */
export function partition(values) {
    return values.reduce(
        (carry, value) => {
            carry[ternary(value)].push(value)
            return carry
        },
        {
            true: [],
            false: [],
            unknown: [],
        },
    )
}
// eslint-disable-next-line @typescript-eslint/no-unused-vars
window.trilean = window.trilean ?? {}

