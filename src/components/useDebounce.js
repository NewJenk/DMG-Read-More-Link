import { useState, useEffect } from 'react';

/**
 * A custom React hook that debounces a value.
 *
 * @param {*} value The value to debounce (e.g., a search term).
 * @param {number} delay The debounce delay in milliseconds.
 * @returns {*} The debounced value.
 */
function useDebounce(value, delay) {
	// State to store the debounced value.
	const [debouncedValue, setDebouncedValue] = useState(value);

	useEffect(
		() => {
			// Set a timer to update the debounced value after the specified delay.
			const handler = setTimeout(() => {
				setDebouncedValue(value);
			}, delay);

			// This is the cleanup function that React runs.
			// It clears the previous timer if the `value` or `delay` changes
			// before the timer has finished. This is the core of debouncing.
			return () => {
				clearTimeout(handler);
			};
		},
		[value, delay] // Only re-run the effect if the value or delay changes.
	);

	return debouncedValue;
}

export default useDebounce;