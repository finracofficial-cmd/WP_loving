<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
$ll_slug = get_post_field( 'post_name', get_queried_object_id() );
if ( ll_page_part_exists( $ll_slug ) ) :
	// 専用デザインのページ（about / flow / lciq / price / faq / contact / privacy / law）
	get_template_part( 'templates/page-' . $ll_slug );
else :
	// それ以外のページは、管理画面で書いた本文をそのまま表示
	while ( have_posts() ) :
		the_post();
		get_template_part( 'parts/page-head', null, array( 'title' => get_the_title(), 'en' => '' ) );
		?>
<section class="sec"><div class="wrap narrow"><div class="post-body">
		<?php the_content(); ?>
</div></div></section>
		<?php
	endwhile;
endif;
get_footer();
