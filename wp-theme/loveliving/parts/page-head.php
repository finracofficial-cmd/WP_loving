<?php
/**
 * ページ上部の見出し帯＋パンくず
 * args: title, en, tag(h1|p), parent([名前, URL]), current
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$a = wp_parse_args( $args, array( 'title' => '', 'en' => '', 'tag' => 'h1', 'parent' => null, 'current' => '' ) );
$tag = 'p' === $a['tag'] ? 'p' : 'h1';
?>
<section class="ph"><div class="wrap"><?php if ( $a['en'] ) : ?><span class="en"><?php echo esc_html( $a['en'] ); ?></span><?php endif; ?><<?php echo $tag; ?> class="ph-title"><?php echo esc_html( $a['title'] ); ?></<?php echo $tag; ?>></div></section>
<div class="crumb"><div class="wrap"><a href="<?php echo esc_url( ll_url( 'home' ) ); ?>">ホーム</a>
<?php if ( $a['parent'] ) : ?> ／ <a href="<?php echo esc_url( $a['parent'][1] ); ?>"><?php echo esc_html( $a['parent'][0] ); ?></a><?php endif; ?>
 ／ <?php echo esc_html( $a['current'] ? $a['current'] : $a['title'] ); ?></div></div>
