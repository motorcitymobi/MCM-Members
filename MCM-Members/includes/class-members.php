<?php
/**
 * This file contains the MCM_Members class.
 */

/**
 * This class handles the creation of the "Members" post type, and creates a
 * UI to display the Member-specific data on the admin screens.
 *
 */
class MCM_Members {

	public $settings_field = 'mcm_taxonomies';
	public $menu_page = 'register-taxonomies';
	
	/**
	 * Directory details array.
	 */
	public $directory_details;

	/**
	 * Construct Method.
	 */
	function __construct() {
		
		$this->directory_details = apply_filters( 'mcm_directory_details', array(
			'col1' => array( 
			    __( 'Price:', 'mcm-members' )   => '_member_price', 
			    __( 'Address:', 'mcm-members' ) => '_member_address', 
			    __( 'City:', 'mcm-members' )    => '_member_city', 
			    __( 'State:', 'mcm-members' )   => '_member_state', 
			    __( 'ZIP:', 'mcm-members' )     => '_member_zip' 
			), 
			'col2' => array( 
			    __( 'MLS #:', 'mcm-members' )       => '_member_mls', 
			    __( 'Square Feet:', 'mcm-members' ) => '_member_sqft', 
			    __( 'Bedrooms:', 'mcm-members' )    => '_member_bedrooms', 
			    __( 'Bathrooms:', 'mcm-members' )   => '_member_bathrooms', 
			    __( 'Basement:', 'mcm-members' )    => '_member_basement' 
			)
		) );

		add_action( 'init', array( $this, 'create_post_type' ) );

		add_filter( 'manage_edit-member_columns', array( $this, 'columns_filter' ) );
		add_action( 'manage_posts_custom_column', array( $this, 'columns_data' ) );

		add_action( 'admin_menu', array( $this, 'register_meta_boxes' ), 5 );
		add_action( 'save_post', array( $this, 'metabox_save' ), 1, 2 );

		add_shortcode( 'directory_details', array( $this, 'directory_details_shortcode' ) );
		add_shortcode( 'directory_map', array( $this, 'directory_map_shortcode' ) );
		add_shortcode( 'directory_video', array( $this, 'directory_video_shortcode' ) );

		#add_action( 'admin_head', array( $this, 'admin_style' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_js' ) );

	}

	/**
	 * Creates our "Member" post type.
	 */
	function create_post_type() {

		$args = apply_filters( 'mcm_members_post_type_args',
			array(
				'labels' => array(
					'name'               => __( 'Members', 'mcm-members' ),
					'singular_name'      => __( 'Member', 'mcm-members' ),
					'add_new'            => __( 'Add New', 'mcm-members' ),
					'add_new_item'       => __( 'Add New Member', 'mcm-members' ),
					'edit'               => __( 'Edit', 'mcm-members' ),
					'edit_item'          => __( 'Edit Member', 'mcm-members' ),
					'new_item'           => __( 'New Member', 'mcm-members' ),
					'view'               => __( 'View Member', 'mcm-members' ),
					'view_item'          => __( 'View Member', 'mcm-members' ),
					'search_items'       => __( 'Search Members', 'mcm-members' ),
					'not_found'          => __( 'No members found', 'mcm-members' ),
					'not_found_in_trash' => __( 'No members found in Trash', 'mcm-members' )
				),
				'public'        => true,
				'query_var'     => true,
				'menu_position' => 6,
				'menu_icon'     => 'dashicons-admin-home',
				'has_archive'   => true,
				// 'supports'      => array( 'title', 'editor', 'comments', 'thumbnail', 'genesis-seo', 'genesis-layouts', 'genesis-simple-sidebars' ),
				'supports'      => array( 'title', 'editor', 'thumbnail', 'genesis-seo', 'genesis-layouts', 'genesis-simple-sidebars' ),
				// 'rewrite'       => array( 'slug' => 'members' ),
				// 'rewrite'       => array( 'slug' => 'directory' ),
				'rewrite' 		=> array('slug' => 'chamber-directory'),
			
			)
		);

		register_post_type( 'member', $args );

	}

	function register_meta_boxes() {

		add_meta_box( 'member_details_metabox', __( 'Directory Details', 'mcm-members' ), array( &$this, 'member_details_metabox' ), 'member', 'normal', 'high' );

	}

	function member_details_metabox() {
		include( dirname( __FILE__ ) . '/views/member-details-metabox.php' );
	}

	function metabox_save( $post_id, $post ) {

		if ( ! isset( $_POST['mcm_details_metabox_nonce'] ) || ! isset( $_POST['ap'] ) )
			return;

		/** Verify the nonce */
	    if ( ! wp_verify_nonce( $_POST['mcm_details_metabox_nonce'], 'mcm_details_metabox_save' ) )
	        return;

		/** Run only on members post type save */
		if ( 'member' != $post->post_type )
			return;

	    /** Don't try to save the data under autosave, ajax, or future post */
	    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
	    if ( defined( 'DOING_CRON' ) && DOING_CRON ) return;

	    /** Check permissions */
	    if ( ! current_user_can( 'edit_post', $post_id ) )
	        return;

	    $directory_details = $_POST['ap'];

	    /** Store the custom fields */
	    foreach ( (array) $directory_details as $key => $value ) {

	        /** Save/Update/Delete */
	        if ( $value ) {
	            update_post_meta($post->ID, $key, $value);
	        } else {
	            delete_post_meta($post->ID, $key);
	        }

	    }

 		//* extra check for price that can create a sortable value
 		if ( isset( $directory_details['_member_price'] ) && ! empty( $directory_details['_member_price'] ) ) {

 			$price_sortable	= preg_replace( '/[^0-9\.]/', '', $directory_details['_member_price'] );
 			update_post_meta( $post_id, '_member_price_sortable', floatval( $price_sortable ) );

 		} else {
 			delete_post_meta( $post_id, '_member_price_sortable' );
 		}

	}

	/**
	 * Filter the columns in the "Members" screen, define our own.
	 */
	function columns_filter ( $columns ) {

		$columns = array(
			'cb'                 => '<input type="checkbox" />',
			'member_thumbnail'  => __( 'Thumbnail', 'mcm-members' ),
			'title'              => __( 'Member Title', 'mcm-members' ),
			'member_details'    => __( 'Details', 'mcm-members' ),
			'member_features'   => __( 'Features', 'mcm-members' ),
			'member_categories' => __( 'Categories', 'mcm-members' )
		);

		return $columns;

	}

	/**
	 * Filter the data that shows up in the columns in the "Members" screen, define our own.
	 */
	function columns_data( $column ) {

		global $post, $wp_taxonomies;

		switch( $column ) {
			case "member_thumbnail":
				printf( '<p>%s</p>', genesis_get_image( array( 'size' => 'thumbnail' ) ) );
				break;
			case "member_details":
				foreach ( (array) $this->directory_details['col1'] as $label => $key ) {
					printf( '<b>%s</b> %s<br />', esc_html( $label ), esc_html( get_post_meta($post->ID, $key, true) ) );
				}
				foreach ( (array) $this->directory_details['col2'] as $label => $key ) {
					printf( '<b>%s</b> %s<br />', esc_html( $label ), esc_html( get_post_meta($post->ID, $key, true) ) );
				}
				break;
			case "member_features":
				echo get_the_term_list( $post->ID, 'features', '', ', ', '' );
				break;
			case "member_categories":
				foreach ( (array) get_option( $this->settings_field ) as $key => $data ) {
					printf( '<b>%s:</b> %s<br />', esc_html( $data['labels']['singular_name'] ), get_the_term_list( $post->ID, $key, '', ', ', '' ) );
				}
				break;
		}

	}

	function directory_details_shortcode( $atts ) {

		global $post;

		$output = '';

		$output .= '<div class="directory-details">';

		$output .= '<div class="directory-details-col1 one-half first">';
			foreach ( (array) $this->directory_details['col1'] as $label => $key ) {
				$output .= sprintf( '<b>%s</b> %s<br />', esc_html( $label ), esc_html( get_post_meta($post->ID, $key, true) ) );	
			}
		$output .= '</div><div class="directory-details-col2 one-half">';
			foreach ( (array) $this->directory_details['col2'] as $label => $key ) {
				$output .= sprintf( '<b>%s</b> %s<br />', esc_html( $label ), esc_html( get_post_meta($post->ID, $key, true) ) );	
			}
		$output .= '</div><div class="clear">';
			$output .= sprintf( '<p><b>%s</b><br /> %s</p></div>', __( 'Additional Features:', 'mcm-members' ), get_the_term_list( $post->ID, 'features', '', ', ', '' ) );

		$output .= '</div>';

		return $output;

	}

	function directory_map_shortcode( $atts ) {

		return genesis_get_custom_field( '_member_map' );

	}

	function directory_video_shortcode( $atts ) {

		return genesis_get_custom_field( '_member_video' );

	}

	function admin_js() {

		wp_enqueue_script( 'accesspress-admin-js', APL_URL . 'includes/js/admin.js', array(), APL_VERSION, true );

	}

}