<?php
/*
Template Name: Blog List V2
*/

get_header();
if ( ( Insight_Helper::get_post_meta( 'page_layout' ) == 'default' ) || ( Insight_Helper::get_post_meta( 'page_layout' ) == '' ) ) {
	$page_layout = Insight::setting( 'page_layout' );
} else {
	$page_layout = Insight_Helper::get_post_meta( 'page_layout' );
}
?>
<?php Insight::page_title(); ?>
	<div class="container">
		<div id="primary" class="content-area row">
			<?php if ( $page_layout == 'sidebar-content' ) {
				get_sidebar();
			} ?>
			<div id="main"
			     class="main blog-list-v2 <?php echo esc_attr( $page_layout == 'content-sidebar' || $page_layout == 'sidebar-content' ? 'col-md-9' : 'col-md-12' ); ?>"
			     role="main">
				<?php
				global $wp_query;
				$paged     = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				$args      = array(
					'post_type' => 'post',
					'paged'     => $paged
				);
				$the_query = new WP_Query( $args );
				$tmp_query = $wp_query;
				$wp_query  = null;
				$wp_query  = $the_query;
				?>
				<?php if ( $the_query->have_posts() ) {
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						if ( $wp_query->current_post == 0 ) {
							get_template_part( 'components/content', 'list-v1' );
						} else {
							get_template_part( 'components/content', 'list-v2' );
						}
					}
					Insight::paging_nav();
				} else {
					get_template_part( 'components/content', 'none' );
				}
				?>
			</div>
			<?php if ( $page_layout == 'content-sidebar' ) {
				get_sidebar();
			} ?>
		</div>
	</div>
<?php
get_footer();
