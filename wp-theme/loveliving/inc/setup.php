<?php
/**
 * テーマを有効化したときの自動セットアップ
 * - 固定ページの作成（すでに同じスラッグのページがあれば作りません）
 * - トップページ／お知らせページの設定
 * - パーマリンクを「投稿名」に
 * - お知らせのカテゴリー（イベント告知／イベントレポート／お知らせ）
 * - 最初の記事（Wedding Carnival in 銀座）
 * - 初期の「Hello world!」「サンプルページ」をゴミ箱へ
 * 何度有効化しても、同じものが二重に作られることはありません。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ll_setup_site() {
	// 固定ページ
	$ids = array();
	foreach ( ll_pages() as $slug => $info ) {
		$page = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $page ) {
			if ( 'publish' !== $page->post_status ) {
				wp_update_post( array( 'ID' => $page->ID, 'post_status' => 'publish' ) );
			}
			$ids[ $slug ] = $page->ID;
			continue;
		}
		$ids[ $slug ] = wp_insert_post( array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'post_title'     => $info[0],
			'post_name'      => $slug,
			'post_content'   => '',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		) );
	}

	// トップページとお知らせページ
	if ( ! empty( $ids['home'] ) && ! empty( $ids['news'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', (int) $ids['home'] );
		update_option( 'page_for_posts', (int) $ids['news'] );
	}
	if ( ! empty( $ids['privacy'] ) ) {
		update_option( 'wp_page_for_privacy_policy', (int) $ids['privacy'] );
	}

	// パーマリンク
	if ( '/%postname%/' !== get_option( 'permalink_structure' ) ) {
		global $wp_rewrite;
		$wp_rewrite->set_permalink_structure( '/%postname%/' );
	}

	// 日付の表示形式・コメント
	update_option( 'date_format', 'Y.m.d' );
	update_option( 'default_comment_status', 'closed' );
	update_option( 'default_ping_status', 'closed' );

	// カテゴリー
	$cats = array(
		'event-info'   => 'イベント告知',
		'event-report' => 'イベントレポート',
		'info'         => 'お知らせ',
	);
	$cat_ids = array();
	foreach ( $cats as $slug => $name ) {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( ! $term ) {
			$r = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
			$cat_ids[ $slug ] = is_wp_error( $r ) ? 0 : (int) $r['term_id'];
		} else {
			$cat_ids[ $slug ] = (int) $term->term_id;
		}
	}
	// 未分類のまま投稿したときは「お知らせ」に入るように
	if ( ! empty( $cat_ids['info'] ) ) {
		update_option( 'default_category', $cat_ids['info'] );
		// 使わない「未分類」は、記事が入っていなければ削除（選択肢に出て迷わないように）
		$uncat = get_term_by( 'slug', 'uncategorized', 'category' );
		if ( $uncat && 0 === (int) $uncat->count && (int) $uncat->term_id !== (int) $cat_ids['info'] ) {
			wp_delete_term( $uncat->term_id, 'category' );
		}
	}

	// 初期のサンプル投稿・ページをゴミ箱へ
	foreach ( array( array( 'hello-world', 'post' ), array( 'sample-page', 'page' ) ) as $s ) {
		$p = get_page_by_path( $s[0], OBJECT, $s[1] );
		if ( $p && 'trash' !== $p->post_status ) {
			wp_trash_post( $p->ID );
		}
	}

	// 最初の記事
	if ( ! get_page_by_path( 'wedding-carnival-ginza', OBJECT, 'post' ) && ! empty( $cat_ids['event-report'] ) ) {
		$content  = "<!-- wp:paragraph -->\n<p>まだご結婚が決まっていない方、とくに女性の方に、ウェディングドレスなどを実際に着ていただき、「結婚したいな」と感じていただくためのイベントです。模擬結婚式や、ビンゴ・抽選会なども行いました。</p>\n<!-- /wp:paragraph -->\n\n";
		$content .= "<!-- wp:paragraph -->\n<p>ブライダルソムリエ主催の、ここ最近でいちばん大きなイベントで、LOVE LIVING も運営としてお手伝いしています。</p>\n<!-- /wp:paragraph -->";
		$post_id = wp_insert_post( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'post_title'     => 'Wedding Carnival in 銀座 を開催しました',
			'post_name'      => 'wedding-carnival-ginza',
			'post_content'   => $content,
			'post_excerpt'   => 'ウェディングドレスの試着、模擬結婚式、ビンゴ・抽選会など、「結婚したいな」と感じていただくためのイベントです。',
			'post_category'  => array( $cat_ids['event-report'] ),
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		) );
		if ( $post_id && ! is_wp_error( $post_id ) ) {
			$att = ll_import_theme_image( 'event-ginza.jpg', $post_id, 'Wedding Carnival in 銀座' );
			if ( $att ) {
				set_post_thumbnail( $post_id, $att );
			}
		}
	}

	flush_rewrite_rules();
	update_option( 'll_setup_version', LL_VER );
}

/** テーマ内の画像をメディアライブラリに取り込む */
function ll_import_theme_image( $file, $post_id, $title ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	$src = get_theme_file_path( 'assets/img/' . $file );
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	$tmp = wp_tempnam( $file );
	if ( ! $tmp || ! copy( $src, $tmp ) ) {
		return 0;
	}
	$id = media_handle_sideload( array( 'name' => $file, 'tmp_name' => $tmp ), $post_id, $title );
	if ( is_wp_error( $id ) ) {
		wp_delete_file( $tmp );
		return 0;
	}
	update_post_meta( $id, '_wp_attachment_image_alt', $title );
	return (int) $id;
}

add_action( 'after_switch_theme', 'll_setup_site' );
