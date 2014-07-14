<?php
wp_nonce_field( 'mcm_details_metabox_save', 'mcm_details_metabox_nonce' );

echo '<div style="width: 90%; float: left">';

	printf( '<p><label>%s<input type="text" name="ap[_member_text]" value="%s" /></label></p>', __( 'Custom Text: ', 'mcm-members' ), esc_attr( genesis_get_custom_field('_member_text') ) );
	printf( '<p><span class="description">%s</span></p>', __( 'Custom text shows on the featured members widget image.', 'mcm-members' ) );

echo '</div><br style="clear: both;" /><br /><br />';

$pattern = '<p><label>%s<br /><input type="text" name="ap[%s]" value="%s" /></label></p>';

echo '<div style="width: 45%; float: left">';

	foreach ( (array) $this->directory_details['col1'] as $label => $key ) {
		printf( $pattern, esc_html( $label ), $key, esc_attr( genesis_get_custom_field( $key ) ) );
	}
	printf( '<p><a class="button" href="%s" onclick="%s">%s</a></p>', '#', 'ap_send_to_editor(\'[directory_details]\')', __( 'Send to text editor', 'mcm-members' ) );

echo '</div>';

echo '<div style="width: 45%; float: left;">';

	foreach ( (array) $this->directory_details['col2'] as $label => $key ) {
		printf( $pattern, esc_html( $label ), $key, esc_attr( genesis_get_custom_field( $key ) ) );
	}

echo '</div><br style="clear: both;" /><br /><br />';

echo '<div style="width: 45%; float: left;">';

	printf( __( '<p><label>Enter Map Embed Code:<br /><textarea name="ap[_member_map]" rows="5" cols="18" style="%s">%s</textarea></label></p>', 'mcm-members' ), 'width: 99%;', htmlentities( genesis_get_custom_field('_member_map') ) );

	printf( '<p><a class="button" href="%s" onclick="%s">%s</a></p>', '#', 'ap_send_to_editor(\'[directory_map]\')', __( 'Send to text editor', 'mcm-members' ) );

echo '</div>';

echo '<div style="width: 45%; float: left;">';

	printf( __( '<p><label>Enter Video Embed Code:<br /><textarea name="ap[_member_video]" rows="5" cols="18" style="%s">%s</textarea></label></p>', 'mcm-members' ), 'width: 99%;', htmlentities( genesis_get_custom_field('_member_video') ) );

	printf( '<p><a class="button" href="%s" onclick="%s">%s</a></p>', '#', 'ap_send_to_editor(\'[directory_video]\')', __( 'Send to text editor', 'mcm-members' ) );

echo '</div><br style="clear: both;" />';