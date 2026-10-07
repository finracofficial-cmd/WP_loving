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
		'home'    => array( 'ホーム', 'LOVELIVING恋愛結婚相談所｜東京・足立区の結婚相談所', '東京・足立区の結婚相談所 LOVE LIVING（ラブリビング）。IBJ正規加盟店。無料の恋愛診断LCIQと恋愛講座で、自分を知ってから出会い、結婚してからも続く幸せをサポートします。初婚・再婚、オンラインで全国対応。' ),
		'about'   => array( 'LOVE LIVINGについて', 'LOVE LIVINGについて（代表の想い・会社概要）｜LOVELIVING恋愛結婚相談所', '代表・有馬祐司がロマンス詐欺の経験から結婚相談所を始めた理由と、恋愛ソムリエ®・LCIQ講師としての歩み。東京・足立区の結婚相談所 LOVE LIVING の会社概要です。' ),
		'flow'    => array( '婚活の流れ', '婚活の流れ・入会条件・必要書類｜LOVELIVING恋愛結婚相談所', '無料相談からお見合い、交際、ご成婚までの流れをご案内します。18歳以上・既婚者以外の方がご入会いただけます。独身証明書・住民票などの必要書類もこちら。' ),
		'lciq'    => array( '恋愛診断・恋愛講座', 'LCIQ無料恋愛診断・恋愛講座｜LOVELIVING恋愛結婚相談所', '恋愛偏差値®がわかるLCIQ無料恋愛診断と、初級（無料）・中級・上級の恋愛講座。伝え方・続け方を学んでから出会えます。既婚の方・営業職の方も受講いただけます。' ),
		'price'   => array( '料金プラン', '料金プラン（入会金・月会費・成婚料）｜LOVELIVING恋愛結婚相談所', 'IBJ・JBU・NNRの組み合わせで選べる4つのコース。初期費用・月会費・お見合い料・成婚料をすべて税込で掲載しています。サポート内容はどのコースも無制限です。' ),
		'news'    => array( 'お知らせ・イベント', 'お知らせ・イベント｜LOVELIVING恋愛結婚相談所', '婚活イベントのご案内、開催レポート、キャンペーンなど、東京・足立区の結婚相談所 LOVE LIVING からのお知らせです。' ),
		'faq'     => array( 'よくあるご質問', 'よくあるご質問｜LOVELIVING恋愛結婚相談所', 'はじめての婚活の不安、入会条件、必要書類、活動の進め方、お支払いなど、結婚相談所 LOVE LIVING によくいただくご質問にお答えします。' ),
		'contact' => array( '無料相談・お問い合わせ', '無料相談・お問い合わせ｜LOVELIVING恋愛結婚相談所', '60分の無料相談のお申し込み、恋愛講座やLCIQ取扱店についてのお問い合わせはこちら。対面・オンラインどちらでも承ります。公式LINEからもお気軽にどうぞ。' ),
		'privacy' => array( 'プライバシーポリシー', 'プライバシーポリシー｜LOVELIVING恋愛結婚相談所', 'LOVELIVING恋愛結婚相談所における個人情報の取り扱いについて。' ),
		'law'     => array( '特定商取引法に基づく表記', '特定商取引法に基づく表記｜LOVELIVING恋愛結婚相談所', '事業者情報、料金、お支払い方法、中途解約、クーリング・オフについて。' ),
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

/** SEOプラグイン（All in One SEO など）が有効か */
function ll_seo_plugin_active() {
	return defined( 'AIOSEO_VERSION' ) || function_exists( 'aioseo' ) || defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' );
}

/* ---------- タイトル（SEOプラグインがあるときはプラグインの設定を優先） ---------- */
add_filter( 'pre_get_document_title', function ( $title ) {
	if ( ll_seo_plugin_active() ) {
		return $title;
	}
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

/* ---------- 説明文（meta description。SEOプラグインがあるときは出さない） ---------- */
add_action( 'wp_head', function () {
	if ( ll_seo_plugin_active() ) {
		return;
	}
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
