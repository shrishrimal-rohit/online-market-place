<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initial setup for this theme
 *
 * @package   InsightFramework
 */
class Insight_Menu {

	static private $_instance = null;

	/**
	 * The constructor.
	 */
	function __construct() {
		// add custom menu fields to menu
		add_filter( 'wp_setup_nav_menu_item', array( $this, 'rc_scm_add_custom_nav_fields' ) );
		// save menu custom fields
		add_action( 'wp_update_nav_menu_item', array( $this, 'rc_scm_update_custom_nav_fields' ), 10, 3 );
		// edit menu walker
		add_filter( 'wp_edit_nav_menu_walker', array( $this, 'rc_scm_edit_walker' ), 10, 2 );

	} // end constructor

	function rc_scm_add_custom_nav_fields( $menu_item ) {

		$menu_item->subtitle = get_post_meta( $menu_item->ID, '_menu_item_subtitle', true );

		return $menu_item;

	}

	function rc_scm_update_custom_nav_fields( $menu_id, $menu_item_db_id, $args ) {

		// Check if element is properly sent
		if ( is_array( $_REQUEST['menu-item-url-parameters'] ) ) {
			$url_parameters = $_REQUEST['menu-item-url-parameters'][ $menu_item_db_id ];
			update_post_meta( $menu_item_db_id, '_url_parameters', $url_parameters );
		}

	}

	function rc_scm_edit_walker( $walker, $menu_id ) {

		return 'Insight_Walker_Nav_Edit_Menu';

	}
}

class Insight_Walker_Nav_Edit_Menu extends Walker_Nav_Menu {
	/**
	 * @see Walker_Nav_Menu::start_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 */
	function start_lvl( &$output, $depth = 0, $args = array() ) {
	}

	/**
	 * @see Walker_Nav_Menu::end_lvl()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 */
	function end_lvl( &$output, $depth = 0, $args = array() ) {
	}

	/**
	 * @see Walker::start_el()
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $item Menu item data object.
	 * @param int $depth Depth of menu item. Used for padding.
	 * @param object $args
	 */
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $_wp_nav_menu_max_depth;

		$_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

		$indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

		ob_start();
		$item_id      = esc_attr( $item->ID );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);

		$original_title = '';
		if ( 'taxonomy' == $item->type ) {
			$original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
			if ( is_wp_error( $original_title ) ) {
				$original_title = false;
			}
		} elseif ( 'post_type' == $item->type ) {
			$original_object = get_post( $item->object_id );
			$original_title  = $original_object->post_title;
		}

		$classes = array(
			'menu-item menu-item-depth-' . $depth,
			'menu-item-' . esc_attr( $item->object ),
			'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive' ),
		);

		$title = $item->title;

		if ( ! empty( $item->_invalid ) ) {
			$classes[] = 'menu-item-invalid';
			/* translators: %s: title of menu item which is invalid */
			$title = sprintf( esc_html__( '%s (Invalid)', 'tm-organie' ), $item->title );
		} elseif ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
			$classes[] = 'pending';
			/* translators: %s: title of menu item in draft status */
			$title = sprintf( esc_html__( '%s (Pending)', 'tm-organie' ), $item->title );
		}

		$title = empty( $item->label ) ? $title : $item->label;

		?>
	<li id="menu-item-<?php echo esc_attr( $item_id ); ?>" class="<?php echo implode( ' ', $classes ); ?>">
		<div class="menu-item-bar">
			<div class="menu-item-handle">
				<span class="item-title"><span class="menu-item-title"><?php echo esc_html( $title ); ?></span> <span
						class="is-submenu" <?php echo esc_attr( $item_id ); ?>><?php esc_html_e( 'sub item', 'tm-organie' ); ?></span></span>
				<span class="item-controls">
                        <span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
                        <span class="item-order hide-if-js">
                            <a href="<?php
                            echo wp_nonce_url(
	                            add_query_arg(
		                            array(
			                            'action'    => 'move-up-menu-item',
			                            'menu-item' => $item_id,
		                            ),
		                            remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
	                            ),
	                            'move-menu_item'
                            );
                            ?>" class="item-move-up"
                               aria-label="<?php esc_attr_e( 'Move up', 'tm-organie' ) ?>">&#8593;</a>
                            |
                            <a href="<?php
                            echo wp_nonce_url(
	                            add_query_arg(
		                            array(
			                            'action'    => 'move-down-menu-item',
			                            'menu-item' => $item_id,
		                            ),
		                            remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
	                            ),
	                            'move-menu_item'
                            );
                            ?>" class="item-move-down" aria-label="<?php esc_attr_e( 'Move down', 'tm-organie' ) ?>">&#8595;</a>
                        </span>
                        <a class="item-edit" id="edit-<?php echo esc_attr( $item_id ); ?>" href="<?php
                        echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
                        ?>"
                           aria-label="<?php esc_attr_e( 'Edit menu item', 'tm-organie' ); ?>"><?php esc_html_e( 'Edit', 'tm-organie' ); ?></a>
                    </span>
			</div>
		</div>

		<div class="menu-item-settings wp-clearfix" id="menu-item-settings-<?php echo esc_attr( $item_id ); ?>">
			<?php if ( 'custom' == $item->type ) : ?>
				<p class="field-url description description-wide">
					<label for="edit-menu-item-url-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'URL', 'tm-organie' ); ?><br/>
						<input type="text" id="edit-menu-item-url-<?php echo esc_attr( $item_id ); ?>"
						       class="widefat code edit-menu-item-url"
						       name="menu-item-url[<?php echo esc_attr( $item_id ); ?>]"
						       value="<?php echo esc_attr( $item->url ); ?>"/>
					</label>
				</p>
			<?php endif; ?>
			<p class="description description-wide">
				<label for="edit-menu-item-title-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Navigation Label', 'tm-organie' ); ?><br/>
					<input type="text" id="edit-menu-item-title-<?php echo esc_attr( $item_id ); ?>"
					       class="widefat edit-menu-item-title"
					       name="menu-item-title[<?php echo esc_attr( $item_id ); ?>]"
					       value="<?php echo esc_attr( $item->title ); ?>"/>
				</label>
			</p>
			<p class="field-title-attribute field-attr-title description description-wide">
				<label for="edit-menu-item-attr-title-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Title Attribute', 'tm-organie' ); ?><br/>
					<input type="text" id="edit-menu-item-attr-title-<?php echo esc_attr( $item_id ); ?>"
					       class="widefat edit-menu-item-attr-title"
					       name="menu-item-attr-title[<?php echo esc_attr( $item_id ); ?>]"
					       value="<?php echo esc_attr( $item->post_excerpt ); ?>"/>
				</label>
			</p>
			<p class="field-link-target description">
				<label for="edit-menu-item-target-<?php echo esc_attr( $item_id ); ?>">
					<input type="checkbox" id="edit-menu-item-target-<?php echo esc_attr( $item_id ); ?>"
					       value="_blank"
					       name="menu-item-target[<?php echo esc_attr( $item_id ); ?>]"<?php checked( $item->target, '_blank' ); ?> />
					<?php esc_html_e( 'Open link in a new tab', 'tm-organie' ); ?>
				</label>
			</p>
			<p class="field-css-classes description description-thin">
				<label for="edit-menu-item-classes-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'CSS Classes (optional)', 'tm-organie' ); ?><br/>
					<input type="text" id="edit-menu-item-classes-<?php echo esc_attr( $item_id ); ?>"
					       class="widefat code edit-menu-item-classes"
					       name="menu-item-classes[<?php echo esc_attr( $item_id ); ?>]"
					       value="<?php echo esc_attr( implode( ' ', $item->classes ) ); ?>"/>
				</label>
			</p>
			<p class="field-xfn description description-thin">
				<label for="edit-menu-item-xfn-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Link Relationship (XFN)', 'tm-organie' ); ?><br/>
					<input type="text" id="edit-menu-item-xfn-<?php echo esc_attr( $item_id ); ?>"
					       class="widefat code edit-menu-item-xfn"
					       name="menu-item-xfn[<?php echo esc_attr( $item_id ); ?>]"
					       value="<?php echo esc_attr( $item->xfn ); ?>"/>
				</label>
			</p>
			<p class="field-description description description-wide">
				<label for="edit-menu-item-description-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'Description', 'tm-organie' ); ?><br/>
					<textarea id="edit-menu-item-description-<?php echo esc_attr( $item_id ); ?>"
					          class="widefat edit-menu-item-description" rows="3" cols="20"
					          name="menu-item-description[<?php echo esc_attr( $item_id ); ?>]"><?php echo esc_html( $item->description ); // textarea_escaped ?></textarea>
					<span
						class="description"><?php esc_html_e( 'The description will be displayed in the menu if the current theme supports it.', 'tm-organie' ); ?></span>
				</label>
			</p>

			<p class="field-menu-item-url-parameters field-attr-title description description-wide">
				<label for="edit-menu-item-url-parameters-<?php echo esc_attr( $item_id ); ?>">
					<?php esc_html_e( 'URL Parameters (for Organie theme)', 'tm-organie' ); ?><br/>
					<input type="text" id="edit-menu-item-url-parameters-<?php echo esc_attr( $item_id ); ?>"
					       class="widefat edit-menu-item-url-parameters"
					       name="menu-item-url-parameters[<?php echo esc_attr( $item_id ); ?>]"
					       value="<?php echo esc_url( get_post_meta( $item_id, '_url_parameters', true ) ); ?>"/>
				</label>
			</p>

			<p class="field-move hide-if-no-js description description-wide">
				<label>
					<span><?php esc_html_e( 'Move', 'tm-organie' ); ?></span>
					<a href="#" class="menus-move menus-move-up"
					   data-dir="up"><?php esc_html_e( 'Up one', 'tm-organie' ); ?></a>
					<a href="#" class="menus-move menus-move-down"
					   data-dir="down"><?php esc_html_e( 'Down one', 'tm-organie' ); ?></a>
					<a href="#" class="menus-move menus-move-left" data-dir="left"></a>
					<a href="#" class="menus-move menus-move-right" data-dir="right"></a>
					<a href="#" class="menus-move menus-move-top"
					   data-dir="top"><?php esc_html_e( 'To the top', 'tm-organie' ); ?></a>
				</label>
			</p>

			<div class="menu-item-actions description-wide submitbox">
				<?php if ( 'custom' != $item->type && $original_title !== false ) : ?>
					<p class="link-to-original">
						<?php printf( esc_html__( 'Original: %s', 'tm-organie' ), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
					</p>
				<?php endif; ?>
				<a class="item-delete submitdelete deletion" id="delete-<?php echo esc_attr( $item_id ); ?>"
				   href="<?php
				   echo wp_nonce_url(
					   add_query_arg(
						   array(
							   'action'    => 'delete-menu-item',
							   'menu-item' => $item_id,
						   ),
						   remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
					   ),
					   'delete-menu_item_' . $item_id
				   ); ?>"><?php esc_html_e( 'Remove', 'tm-organie' ); ?></a> <span class="meta-sep"> | </span> <a
					class="item-cancel submitcancel" id="cancel-<?php echo esc_attr( $item_id ); ?>"
					href="<?php echo esc_url(
						add_query_arg(
							array(
								'edit-menu-item' => $item_id,
								'cancel'         => time()
							), remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
						)
					);
					?>#menu-item-settings-<?php echo esc_attr( $item_id ); ?>"><?php esc_html_e( 'Cancel', 'tm-organie' ); ?></a>
			</div>

			<input class="menu-item-data-db-id" type="hidden"
			       name="menu-item-db-id[<?php echo esc_attr( $item_id ); ?>]"
			       value="<?php echo esc_attr( $item_id ); ?>"/>
			<input class="menu-item-data-object-id" type="hidden"
			       name="menu-item-object-id[<?php echo esc_attr( $item_id ); ?>]"
			       value="<?php echo esc_attr( $item->object_id ); ?>"/>
			<input class="menu-item-data-object" type="hidden"
			       name="menu-item-object[<?php echo esc_attr( $item_id ); ?>]"
			       value="<?php echo esc_attr( $item->object ); ?>"/>
			<input class="menu-item-data-parent-id" type="hidden"
			       name="menu-item-parent-id[<?php echo esc_attr( $item_id ); ?>]"
			       value="<?php echo esc_attr( $item->menu_item_parent ); ?>"/>
			<input class="menu-item-data-position" type="hidden"
			       name="menu-item-position[<?php echo esc_attr( $item_id ); ?>]"
			       value="<?php echo esc_attr( $item->menu_order ); ?>"/>
			<input class="menu-item-data-type" type="hidden"
			       name="menu-item-type[<?php echo esc_attr( $item_id ); ?>]"
			       value="<?php echo esc_attr( $item->type ); ?>"/>
		</div><!-- .menu-item-settings-->
		<ul class="menu-item-transport"></ul>
		<?php
		$output .= ob_get_clean();
	}
}