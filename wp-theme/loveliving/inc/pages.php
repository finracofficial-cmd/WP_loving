<?php
/**
 * 固定ページの一覧・URL・タイトル・説明文
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * スラッグ => [ページ名, 検索結果に出るタイトル, 説明文]
 * テーマ有効化時に、この一覧どおりに固定ページを作成します。
 */
function ll_pages() {
	return array(
		'home'    => array( 'ホーム', 'LOVELIVING恋愛結婚相談所｜東京・足立区の結婚相談所', '東京・足立区の結婚相談所 LOVE LIVING。IBJ正規加盟店。恋愛診断LCIQと恋愛講座で、結婚する幸せと、結婚してからの幸せをサポートします。' ),
		'about'   => array( 'LOVE LIVINGについて', 'LOVE LIVINGについて｜LOVELIVING恋愛結婚相談所', 'LOVE LIVING 代表 有馬祐司のプロフィールと、結婚相談所を始めた理由。会社概要。' ),
		'flow'    => array( '婚活の流れ', '婚活の流れ｜LOVELIVING恋愛結婚相談所', '無料相談からご成婚までの流れ、ご入会の条件、必要書類をご案内します。' ),
		'lciq'    => array( '恋愛診断・恋愛講座', '恋愛診断・恋愛講座｜LOVELIVING恋愛結婚相談所', 'LCIQ無料恋愛診断と、初級・中級・上級の恋愛講座。既婚の方も受講いただけます。' ),
		'price'   => array( '料金プラン', '料金プラン｜LOVELIVING恋愛結婚相談所', 'LOVE LIVINGの料金プラン。4つのコースからお選びいただけます。表示はすべて税込です。' ),
		'news'    => array( 'お知らせ・イベント', 'お知らせ・イベント｜LOVELIVING恋愛結婚相談所', 'LOVE LIVING のイベントのご案内、イベントレポート、お知らせを掲載しています。' ),
		'faq'     => array( 'よくあるご質問', 'よくあるご質問｜LOVELIVING恋愛結婚相談所', 'はじめての婚活の不安、入会条件、必要書類、お支払いなど、よくいただくご質問にお答えします。' ),
		'contact' => array( '無料相談・お問い合わせ', '無料相談・お問い合わせ｜LOVELIVING恋愛結婚相談所', '無料相談のお申し込み、恋愛講座やLCIQ取扱店についてのお問い合わせはこちらから。' ),
		'privacy' => array( 'プライバシーポリシー', 'プライバシーポリシー｜LOVELIVING恋愛結婚相談所', 'LOVE LIVINGの個人情報の取り扱いについて。' ),
		'law'     => array( '特定商取引法に基づく表記', '特定商取引法に基づく表記｜LOVELIVING恋愛結婚相談所', '事業者情報、料金、中途解約、クーリング・オフについて。' ),
	);
}

/** ヘッダーのメニュー（スラッグ => 表示名） */
function ll_nav() {
	return array(
		'home'  => 'ホーム',
		'about' => 'LOVE LIVINGについて',
		'flow'  => '婚活の流れ',
		'lciq'  => '恋愛診断・講座',
		'price' => '料金プラン',
		'news'  => 'お知らせ・イベント',
		'faq'   => 'よくあるご質問',
	);
}

/** スラッグからページのURLを返す（'lciq#partner' のようなアンカー付きも可） */
function ll_url( $slug ) {
	$hash = '';
	if ( false !== strpos( $slug, '#' ) ) {
		list( $slug, $hash ) = explode( '#', $slug, 2 );
		$hash = '#' . $hash;
	}
	if ( 'home' === $slug || '' === $slug ) {
		return home_url( '/' ) . $hash;
	}
	if ( 'news' === $slug ) {
		$id = (int) get_option( 'page_for_posts' );
		if ( $id ) {
			return get_permalink( $id ) . $hash;
		}
	}
	$page = get_page_by_path( $slug );
	if ( $page ) {
		return get_permalink( $page ) . $hash;
	}
	return home_url( '/' . $slug . '/' ) . $hash;
}

/** いま表示しているページのスラッグ（メニューの現在地表示用） */
function ll_current_slug() {
	if ( is_front_page() ) {
		return 'home';
	}
	if ( is_home() || is_singular( 'post' ) || is_category() || is_date() || is_tag() ) {
		return 'news';
	}
	if ( is_page() ) {
		return get_post_field( 'post_name', get_queried_object_id() );
	}
	return '';
}

/* ---------- タイトル ---------- */
add_filter( 'pre_get_document_title', function ( $title ) {
	$pages = ll_pages();
	$slug  = ll_current_slug();
	if ( is_singular( 'post' ) ) {
		return single_post_title( '', false ) . '｜LOVELIVING恋愛結婚相談所';
	}
	if ( is_category() ) {
		return single_cat_title( '', false ) . '｜お知らせ・イベント｜LOVELIVING恋愛結婚相談所';
	}
	if ( ( is_front_page() || is_home() || is_page() ) && isset( $pages[ $slug ] ) ) {
		return $pages[ $slug ][1];
	}
	return $title;
} );

/* ---------- 説明文（meta description） ---------- */
add_action( 'wp_head', function () {
	$desc  = '';
	$pages = ll_pages();
	$slug  = ll_current_slug();
	if ( is_singular( 'post' ) ) {
		$desc = ll_excerpt( get_queried_object(), 240 );
	} elseif ( isset( $pages[ $slug ] ) && ! is_category() ) {
		$desc = $pages[ $slug ][2];
	}
	if ( $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
}, 1 );

/* ---------- 固定ページ：スラッグごとに専用の中身を読み込む ---------- */
function ll_page_part_exists( $slug ) {
	return (bool) locate_template( 'templates/page-' . $slug . '.php' );
}
