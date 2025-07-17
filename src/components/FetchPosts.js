import { __, sprintf } from '@wordpress/i18n';
import { decodeEntities } from '@wordpress/html-entities';
import { Spinner, MenuItem, Button, __experimentalHStack as HStack } from '@wordpress/components';
import { useState, useEffect, useCallback, memo } from 'react';
import { check, chevronLeft, chevronRight } from '@wordpress/icons';

import useDebounce from './useDebounce';

/**
 * A memoised component for a single item in the results list.
 *
 * @param {Object}   props          - The component props.
 * @param {Object}   props.page     - The post object.
 * @param {boolean}  props.isSelected - Whether this item is currently selected.
 * @param {Function} props.onSelect   - Callback function to handle selection.
 * @returns {JSX.Element} The rendered menu item.
 */
const DropdownItem = memo(({ page, isSelected, onSelect }) => {
	return (
		<MenuItem
			role="menuitemcheckbox"
			className="dmg-rml-post-item is-secondary"
			icon={isSelected ? check : null}
			isSelected={isSelected}
			onClick={() => onSelect(page.id)}
		>
			<span>{page.title.rendered}</span>
            <span className="dmg-rml-post-item__sml-text">
                {
                    /* Translators: %d is a placeholder for the numeric Post ID. */
                    sprintf(
                        __( 'ID: %d', 'dmg-rml' ),
                        page.id
                    )
                }
            </span>
		</MenuItem>
	);
});

/**
 * Renders smart pagination controls with ellipses for large page ranges.
 */
const PaginationControls = ({ currentPage, totalPages, onPageChange }) => {
	// Don't render pagination if there's only one page.
	if (totalPages <= 1) {
		return null;
	}

	// Calculate the page numbers to display.
	const getPageNumbers = () => {
		const pageNeighbours = 1; // How many pages to show on each side of the current page.
		const totalNumbers = (pageNeighbours * 2) + 3; // e.g., 1, ..., 4, 5, 6, ..., 100
		const totalBlocks = totalNumbers + 2;

		if ( totalPages > totalBlocks ) {
			const startPage = Math.max(2, currentPage - pageNeighbours);
			const endPage = Math.min(totalPages - 1, currentPage + pageNeighbours);
			let pages = Array.from({ length: (endPage - startPage) + 1 }, (_, i) => startPage + i);

			const hasLeftSpill = startPage > 2;
			const hasRightSpill = (totalPages - endPage) > 1;
			const spillOffset = totalNumbers - (pages.length + 1);

			switch (true) {
				case (hasLeftSpill && !hasRightSpill): {
					const extraPages = Array.from({ length: spillOffset + 1 }, (_, i) => startPage - i - 1);
					pages = ['...', ...extraPages.reverse(), ...pages];
					break;
				}
				case (!hasLeftSpill && hasRightSpill): {
					const extraPages = Array.from({ length: spillOffset + 1 }, (_, i) => endPage + i + 1);
					pages = [...pages, ...extraPages, '...'];
					break;
				}
				case (hasLeftSpill && hasRightSpill):
				default: {
					pages = ['...', ...pages, '...'];
					break;
				}
			}
			return [1, ...pages, totalPages];
		}
		return Array.from({ length: totalPages }, (_, i) => i + 1);
	};

	const pages = getPageNumbers();

	return (
		<HStack justify="center" spacing={2} className="dmg-rml-pag">
			<Button
				icon={chevronLeft}
				variant='secondary'
				label={__('Previous page', 'dmg-rml')}
				onClick={() => onPageChange(currentPage - 1)}
				disabled={currentPage === 1}
			/>

			{pages.map((page, index) => {
				if (page === '...') {
					return (
						<span key={`ellipsis-${index}`} style={{ alignSelf: 'centre' }}>
							...
						</span>
					);
				}
				return (
					<Button
						key={page}
						variant={currentPage === page ? 'primary' : 'tertiary'}
						onClick={() => onPageChange(page)}
					>
						{page}
					</Button>
				);
			})}

			<Button
				icon={chevronRight}
				variant='secondary'
				label={__('Next page', 'dmg-rml')}
				onClick={() => onPageChange(currentPage + 1)}
				disabled={currentPage === totalPages}
			/>
		</HStack>
	);
};


/**
 * A component to fetch and display posts from the REST API with search and pagination.
 *
 */
const FetchPosts = ({
	siteData,
	setSiteData,
	selectedPosts = [],
	attrLabel,
	setAttributes,
	multiple,
	fullRestUrl,
    searchType,
    searchTerm,
}) => {
	const perPage = 10; // Results per page.

	const [isLoading, setIsLoading]     = useState(false);
	const [error, setError]             = useState(null);
	const [currentPage, setCurrentPage] = useState(1);

	// Debounce search input to prevent firing an API request on every keystroke.
	// This is essential for performance and to avoid hammering the server.
	const debouncedSearchTerm = useDebounce(siteData.search, 400);

	/**
	 * Reset pagination when a new search is initiated.
	 */
	useEffect(() => {
		setCurrentPage(1);
	}, [debouncedSearchTerm]);

	/**
	 * Fetch data from the API.
	 * Fetch data immediately on load.
	 * Build the API request differently depending on whether a search term exists.
	 */
	useEffect(() => {
		const fetchData = async () => {
			setIsLoading(true);
			setError(null);

			const term = debouncedSearchTerm.trim();
			let fetchUri = `${fullRestUrl}?per_page=${perPage}&page=${currentPage}&_fields=id,title`;

			// Build the API query based on the searchType prop provided by the parent.
			if (searchType === 'id' && term) {
				// If the parent determines the search is for an ID, use the 'include' parameter.
				fetchUri += `&include=${term}`;
			} else if (term) {
				// If the parent determines it's a text search, use the 'search' parameter.
				fetchUri += `&search=${encodeURIComponent(term)}&orderby=title&order=asc`;
			} else {
				// If the search term is empty, default to showing the most recent posts.
				fetchUri += `&orderby=date&order=desc`;
			}

			try {
				const response = await fetch(fetchUri);
				if (!response.ok) {
					throw new Error(`HTTP error! Status: ${response.status}`);
				}
				const totalMatching = parseInt(response.headers.get('X-WP-Total'), 10);
				let fetchedPosts = await response.json();
				fetchedPosts = fetchedPosts.map((post) => ({
					...post,
					title: { rendered: decodeEntities(post.title.rendered) },
				}));
				setSiteData(draft => {
					draft.content = fetchedPosts;
					draft.totalContent = totalMatching;
				});
				setIsLoading(false);
			} catch (e) {
				// Recovers from the race condition where a new search is performed from a high page number.
				if (e.message.includes('Status: 400') && currentPage !== 1) {
					console.warn('Invalid page number detected. Resetting to page 1 to recover.');
					setCurrentPage(1);
				} else {
					console.error("Error fetching posts:", e);
					setError(e.message);
					setIsLoading(false);
				}
			}
		};

		if (fullRestUrl) {
			fetchData();
		}
	}, [debouncedSearchTerm, searchType, fullRestUrl, currentPage, setSiteData]);

	/**
	 * Memoised handler for item selection.
	 * Using useCallback prevents this function from being recreated on every render.
	 */
	const handleSelect = useCallback((pageId) => {
		if (multiple) {
			// Ensure `selectedPosts` is treated as an array. If it's
			// null or undefined, use an empty array as a fallback.
			const currentSelection = Array.isArray(selectedPosts) ? selectedPosts : [];

			const isSelected = currentSelection.includes(pageId);
			const newSelection = isSelected
				? currentSelection.filter(id => id !== pageId)
				: [...currentSelection, pageId];
			setAttributes({ [attrLabel]: newSelection });
		} else {
			// This part handles the non-multiple case.
			const isSelected   = selectedPosts === pageId;
			const newSelection = isSelected ? null : pageId;  // null needed as block attribute is integer.

			setAttributes({ [attrLabel]: newSelection });
		}
	}, [multiple, selectedPosts, attrLabel, setAttributes]);

	// Calculate total pages needed for pagination controls.
	const totalPages = Math.ceil(siteData.totalContent / perPage);

	if (error) {
		return <div>{__('Error loading content:', 'dmg-rml')} {error}</div>;
	}

	return (
		<>
			{isLoading ? (
				<Spinner />
			) : (
				<>
					{(siteData.content || []).map((page) => {
						// Whether we are handling a single value (a number) or multiple values (an array).
						const isSelected = multiple
							? selectedPosts.includes(page.id)
							: selectedPosts === page.id;

						return (
							<DropdownItem
								key={page.id}
								page={page}
								onSelect={handleSelect}
								isSelected={isSelected}
							/>
						);
					})}
					<PaginationControls
						currentPage={currentPage}
						totalPages={totalPages}
						onPageChange={setCurrentPage}
					/>
				</>
			)}
		</>
	);
};

export default FetchPosts;