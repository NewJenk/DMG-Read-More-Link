/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * @link https://github.com/WordPress/gutenberg/issues/23810#issuecomment-656128246
 */
import {
    MenuGroup,
    Button,
    TextControl,
    Flex,
    FlexItem,
	Spinner,
	Notice,
	BaseControl
} from '@wordpress/components';

import { useCallback } from 'react';

import { closeSmall } from '@wordpress/icons';

import FetchPosts from './FetchPosts';

import { AsyncModeProvider, useSelect } from '@wordpress/data';

import DisplayPostTitle from './DisplayPostTitle';

import { useImmer } from 'use-immer';

const PostsList = ( {
    attrLabel, // Make sure this prop holds the *name* of the attribute key
    attribute, // This holds the *value* (array of IDs or single ID)
    postType,
    setAttributes,
    multiple = false
} ) => {

	const [siteData, setSiteData] = useImmer({
		content        : [],       // Always initialise array-based state with an empty array.
		totalContent   : 0,
		search         : '',
		searchType     : 'text',   // Tracks if the input is numeric ('id') or not ('text').
		forceTextSearch: false,    // Allows the user to override the ID detection.
	});

    // Handler to remove a post ID from the attribute array
	const handleRemovePost = useCallback((postIdToRemove) => {
		if (multiple) {
			// This handles the case where 'attribute' is an array.
			if (!Array.isArray(attribute)) return;
			const newAttributeValue = attribute.filter(id => id !== postIdToRemove);
			setAttributes({ [attrLabel]: newAttributeValue });
		} else {
			// This handles the case where 'attribute' is a single ID.
			// Removing it means clearing the selection.
			setAttributes({ [attrLabel]: null });
		}
	}, [multiple, attribute, attrLabel, setAttributes]);

	const { restBase, restNamespace, hasResolved } = useSelect(
		(select) => {
			const { getPostType, hasFinishedResolution } = select('core');
			const postTypeData = getPostType(postType);

			return {
				// Get the rest_base from the post type data, if it exists.
				restBase: postTypeData?.rest_base,
				// Get the rest_namespace from the same data object.
				restNamespace: postTypeData?.rest_namespace,
				// Check if the data store has finished trying to get this post type.
				hasResolved: hasFinishedResolution('getPostType', [postType]),
			};
		},
		[postType]
	);

	// We construct the full URL here after all data has been fetched.
	// It will be `null` if any of the necessary pieces are missing.
	const fullRestUrl = (window.wpApiSettings?.root && restNamespace && restBase)
		? `${window.wpApiSettings.root}${restNamespace}/${restBase}`
		: null;


	// If the resolution is still in progress, show a spinner.
	if ( ! hasResolved ) {
		return <Spinner />;
	}

	// If resolution has finished BUT we couldn't construct the full URL,
	// it means a critical piece of information is missing.
	if ( ! fullRestUrl ) {
		return (
			<Notice status="error" isDismissible={false}>
				{sprintf(
					__('Error: Could not determine the full REST API URL for the post type "%s".', 'dmg-rml'),
					postType
				)}
			</Notice>
		);
	}

    return (
        <>
            <div className="dmg-rml-pseudo-input-wrap">
                <div className="dmg-rml-pseudo-input">
					<div
						className="dmg-rml-token-wrap"
						style={{
							padding: '14px 20px 10px 20px',
							background: '#eee',
							marginBottom: '10px',
							borderRadius: '10px'
						}}
					>
						<BaseControl.VisualLabel style={{ margin: 0 }}>
							{__('Selected Post', 'dmg-rml')}
						</BaseControl.VisualLabel>
						{/* --- Section to display selected posts as pills --- */}
						{(() => {
							// This allows the same display logic to work for both single and multiple selections.
							const postIds = multiple
								? (Array.isArray(attribute) ? attribute : []) // If multiple, ensure it's an array.
								: (attribute ? [attribute] : []); // If not multiple, put the single ID in an array if it exists.

							return (
								<Flex
									wrap={true}
									gap={1}
									className="selected-posts-pills-container"
									style={{
										padding: '8px 0'
									}}
								>
									{postIds.length > 0 ? (
										// If posts are selected, map over them to display the pills.
										postIds.map(postId => (
											<FlexItem key={postId}>
												<span className="components-form-token-field__token">
													<span className="components-form-token-field__token-text">
														<AsyncModeProvider value={true}>
															<DisplayPostTitle id={postId} postType={postType} />
														</AsyncModeProvider>
													</span>
													<Button
														icon={closeSmall}
														className="components-button components-form-token-field__remove-token has-icon"
														label={__('Remove', 'dmg-rml')}
														onClick={() => handleRemovePost(postId)}
														aria-label={__('Remove post', 'dmg-rml')}
														style={{ padding: 0 }}
													/>
												</span>
											</FlexItem>
										))
									) : (
										// If no posts are selected, display the placeholder text.
										<FlexItem>
											<span className="components-form-token-field__token">
												<span className="components-form-token-field__token-text empty">
													{__('No post selected', 'dmg-rml')}
												</span>
											</span>
										</FlexItem>
									)}
								</Flex>
							);
						})()}
						{/* --- End of selected posts pills section --- */}
					</div>
					<MenuGroup label="">
						<TextControl
							className="is-cols-auto"
							placeholder={__('Type to search or enter an ID...', 'dmg-rml')}
							label={__('Search', 'dmg-rml')}
							value={siteData.search}
							onChange={(value) => setSiteData(draft => {
								// Updates the search term, determine if it's numeric, and reset any
								// override the user may have previously selected.
								draft.search = value;
								const isNumeric = /^\d+$/.test(value.trim());
								draft.searchType = isNumeric ? 'id' : 'text';
								draft.forceTextSearch = false;
							})}
						/>
						{siteData.searchType === 'id' && !siteData.forceTextSearch && (
							<div className="dmg-rml-search-notice">
								<p className="dmg-rml-search-notice__txt">
									{__('Numeric ID detected. Searching by Post ID.', 'dmg-rml')}
								</p>
								<Button
									isLink
									className="dmg-rml-search-notice__txt"
									onClick={() => setSiteData(draft => {
										draft.forceTextSearch = true;
									})}
								>
									{sprintf(__('Search for "%s" as text instead?', 'dmg-rml'), siteData.search)}
								</Button>
							</div>
						)}
						<FetchPosts
							siteData={siteData}
							setSiteData={setSiteData}
							selectedPosts={attribute}
							attrLabel={attrLabel}
							setAttributes={setAttributes}
							fullRestUrl={fullRestUrl}
							multiple={multiple}
							searchType={siteData.forceTextSearch ? 'text' : siteData.searchType}
							searchTerm={siteData.search}
						/>
					</MenuGroup>
                </div>
            </div>
        </>
    )
}

export default PostsList;