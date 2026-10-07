<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
get_template_part( 'parts/page-head', null, array( 'title' => 'お知らせ・イベント', 'en' => 'News & Event', 'tag' => 'p', 'parent' => array( 'お知らせ・イベント', ll_url( 'news' ) ), 'current' => single_post_title( '', false ) ) );
while ( have_posts() ) :
	the_post();
	$ll_cat = ll_post_cat( get_post() );
	?>
<section class="sec"><div class="wrap narrow">
<article <?php post_class( 'post' ); ?>>
<div class="nc-meta">
	<?php if ( $ll_cat ) : ?>
<a class="ncat ncat-<?php echo esc_attr( ll_cat_class( $ll_cat->slug ) ); ?>" href="<?php echo esc_url( get_category_link( $ll_cat ) ); ?>"><?php echo esc_html( $ll_cat->name ); ?></a>
	<?php endif; ?>
<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time>
</div>
<h1 class="post-h"><?php the_title(); ?></h1>
	<?php if ( has_post_thumbnail() ) : ?>
		<?php the_post_thumbnail( 'large', array( 'class' => 'post-main' ) ); ?>
	<?php endif; ?>
<div class="post-body">
	<?php the_content(); ?>
</div>
</article>
<div class="post-nav"><a href="<?php echo esc_url( ll_url( 'news' ) ); ?>">← お知らせ・イベント一覧へ戻る</a></div>
</div></section>
	<?php
endwhile;
get_template_part( 'parts/cta' );
get_footer();
