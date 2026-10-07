<?php
/** お知らせのカード（1件分） */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ll_cat = ll_post_cat( get_post() );
?>
<a class="ncard" href="<?php the_permalink(); ?>">
<div class="nc-img"><?php if ( has_post_thumbnail() ) : ?><?php the_post_thumbnail( 'll-card', array( 'alt' => '', 'loading' => 'lazy' ) ); ?><?php else : ?><span class="nc-noimg nc-logo"><img src="<?php echo esc_url( ll_img( 'logo.png' ) ); ?>" alt=""></span><?php endif; ?></div>
<div class="nc-body">
<div class="nc-meta"><?php if ( $ll_cat ) : ?><span class="ncat ncat-<?php echo esc_attr( ll_cat_class( $ll_cat->slug ) ); ?>"><?php echo esc_html( $ll_cat->name ); ?></span><?php endif; ?><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'Y.m.d' ) ); ?></time></div>
<h3><?php the_title(); ?></h3>
<p><?php echo esc_html( ll_excerpt( get_post(), 110 ) ); ?></p>
</div></a>
