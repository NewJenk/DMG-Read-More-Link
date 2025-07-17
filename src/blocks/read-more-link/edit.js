import { __ } from '@wordpress/i18n';

import {
	useBlockProps,
	InspectorControls,
	BlockControls
} from '@wordpress/block-editor';

import { PanelBody, PanelRow, BaseControl, Disabled, ToolbarGroup, ToolbarButton } from '@wordpress/components';

import PostsList from '../../components/PostsList';

import DisplayPostTitle from '../../components/DisplayPostTitle';

import { createInterpolateElement } from '@wordpress/element';

import { useDispatch, useSelect } from '@wordpress/data';

import { cog } from '@wordpress/icons';

import './editor.scss';

export default function Edit( {attributes:{postID}, setAttributes, clientId} ) {

	const blockProps = useBlockProps({ className: 'dmg-read-more' });

	const { openGeneralSidebar, closeGeneralSidebar } = useDispatch('core/edit-post');

	// Need to know if the sidebar is open to make it a toggle.
	const isSidebarOpen = useSelect(
		(select) => select('core/edit-post').isEditorSidebarOpened()
	);


	const readMoreElement = createInterpolateElement(
		// The placeholder tag <DisplayPostTitle/> must be a valid component name.
		__('Read More: <DisplayPostTitle/>', 'dmg-rml'),
		{
			// Map the placeholder tag to the actual component.
			DisplayPostTitle: <DisplayPostTitle id={postID} postType={'post'} />,
		}
	);


	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon={cog}
						label={__('Toggle Settings', 'dmg-rml')}
						onClick={() => {
							if (isSidebarOpen) {
								closeGeneralSidebar();
							} else {
								// Open the sidebar to the "Block" tab.
								openGeneralSidebar('edit-post/block');
							}
						}}
						isPressed={isSidebarOpen}
					/>
				</ToolbarGroup>
			</BlockControls>
			<InspectorControls>
				<PanelBody title={__('Choose Post', 'dmg-rml')}>
					<PanelRow>
						<BaseControl className="dmg-rml-wrap">
							<PostsList
								attrLabel={'postID'}
								attribute={postID}
								postType={'post'}
								setAttributes={setAttributes}
								clientId={clientId}
								hasTriggered={true}
							/>
						</BaseControl>
					</PanelRow>
				</PanelBody>
			</InspectorControls>
			<p {...blockProps}>
				{ ! postID ? (
					__('Please select a post from the sidebar.', 'dmg-rml')
				) : (
					// Wrap the output in the <Disabled> component to make it non-interactive
					<Disabled>
						{/* No need for post URL on the edit screen */}
						<a href="#" className="dmg-read-more__link">
							{readMoreElement}
						</a>
					</Disabled>
				)}
			</p>
		</>
	);

}
