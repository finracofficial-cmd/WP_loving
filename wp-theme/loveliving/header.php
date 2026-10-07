<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ll_cur = ll_current_slug();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="hd"><div class="hd-in">
<a class="hd-logo" href="<?php echo esc_url( ll_url( 'home' ) ); ?>"><img src="<?php echo esc_url( ll_img( 'logo.png' ) ); ?>" alt="LOVE LIVING ラブリビング"></a>
<button class="hd-tgl" type="button" aria-expanded="false" aria-controls="ll-nav" onclick="var n=document.getElementById('ll-nav');n.classList.toggle('on');this.setAttribute('aria-expanded',n.classList.contains('on'))">メニュー</button>
<nav class="hd-nav" id="ll-nav" aria-label="メインメニュー">
<?php foreach ( ll_nav() as $slug => $label ) : ?>
<a href="<?php echo esc_url( ll_url( $slug ) ); ?>"<?php echo $slug === $ll_cur ? ' class="is-current" aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
<?php endforeach; ?>
<a class="hd-cta" href="<?php echo esc_url( ll_url( 'contact' ) ); ?>">無料相談・お問い合わせ</a>
</nav>
</div></header>
<main id="main">
