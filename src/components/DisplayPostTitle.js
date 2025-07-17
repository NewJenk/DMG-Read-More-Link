/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

import { useEntityRecord } from '@wordpress/core-data';

export default function DisplayPostTitle( { id, postType } ) {

    const post = useEntityRecord( 'postType', '' + postType + '', id );

    if ( post.isResolving ) {

        return __( 'Loading...', 'dmg-rml' );

    }

    // @link https://developer.wordpress.org/news/2023/05/19/useentityrecords-an-easier-way-to-fetch-wordpress-data/
    if ( post.hasResolved ) {

        if  ( post.status === "ERROR" ) {

            return __( 'Post unavailable. Please choose another post.' , 'dmg-rml' )

        }

    }
    
    return post.editedRecord.title;

}