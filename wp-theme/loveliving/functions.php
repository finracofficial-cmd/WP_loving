<?php
/**
 * LOVE LIVING theme functions.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LL_VER', '1.0.0' );

require_once get_theme_file_path( 'inc/pages.php' );
require_once get_theme_file_path( 'inc/customizer.php' );
require_once get_theme_file_path( 'inc/contact.php' );
require_once get_theme_file_path( 'inc/setup.php' );

/* ---------- 基本設定 ---------- */
add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'loveliving', get_theme_file_path( 'languages' ) );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_image_size( 'll-card', 800, 500, true );
} );

/* ---------- CSS / JS ---------- */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'll-fonts', 'https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;500;700&family=Noto+Serif+JP:wght@400;600;700&display=swap', array(), null );
	$css = get_theme_file_path( 'assets/css/style.css' );
	wp_enqueue_style( 'll-style', get_theme_file_uri( 'assets/css/style.css' ), array(), (string) filemtime( $css ) );
	$wp_css = get_theme_file_path( 'assets/css/wp.css' );
	wp_enqueue_style( 'll-wp', get_theme_file_uri( 'assets/css/wp.css' ), array( 'll-style' ), (string) filemtime( $wp_css ) );
} );

add_filter( 'wp_resource_hints', function ( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );

/* 不要な出力を整理 */
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'wp_head', 'wp_generator' );

/* ---------- テーマ内画像のURL ---------- */
function ll_img( $file ) {
	return get_theme_file_uri( 'assets/img/' . $file );
}

/* ---------- 投稿：抜粋・カテゴリー ---------- */
function ll_excerpt( $post = null, $len = 120 ) {
	$text = has_excerpt( $post ) ? get_the_excerpt( $post ) : get_post_field( 'post_content', $post );
	$text = trim( preg_replace( '/\s+/u', ' ', wp_strip_all_tags( strip_shortcodes( excerpt_remove_blocks( $text ) ) ) ) );
	return mb_strimwidth( $text, 0, $len, '…', 'UTF-8' );
}

function ll_cat_class( $slug ) {
	$map = array(
		'event-info'   => 'evt',
		'event-report' => 'rep',
		'info'         => 'info',
	);
	return isset( $map[ $slug ] ) ? $map[ $slug ] : 'rep';
}

function ll_post_cat( $post = null ) {
	$cats = get_the_category( $post ? $post->ID : 0 );
	foreach ( $cats as $c ) {
		if ( 'uncategorized' !== $c->slug ) {
			return $c;
		}
	}
	return $cats ? $cats[0] : null;
}

/* お知らせ一覧は 9件ずつ */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() ) {
		return;
	}
	if ( $q->is_home() || $q->is_category() ) {
		$q->set( 'posts_per_page', 9 );
	}
} );

/* 抜粋の末尾 */
add_filter( 'excerpt_more', function () {
	return '…';
} );

/* ---------- 管理画面：デザイン済みページの編集画面に案内を出す ---------- */
add_action( 'admin_notices', function () {
	$screen = get_current_screen();
	if ( ! $screen || 'page' !== $screen->post_type || 'post' !== $screen->base ) {
		return;
	}
	$id   = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore
	$slug = $id ? get_post_field( 'post_name', $id ) : '';
	if ( 'home' === $slug || ( $slug && ll_page_part_exists( $slug ) ) ) {
		echo '<div class="notice notice-info"><p><strong>このページの中身はテーマで作られています。</strong>ここに文章を書いても表示されません。文章の修正はご依頼ください。お支払い方法・口座振替日は「外観 → カスタマイズ → LOVE LIVING の設定」から変更できます。</p></div>';
	}
} );
