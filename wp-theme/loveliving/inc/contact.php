<?php
/**
 * お問い合わせフォーム（プラグイン不要）
 * 送信内容は「LOVE LIVING の設定」の送信先へメールで届き、お客様には自動返信が届きます。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ll_form_choices() {
	return array(
		'want'   => array( '無料相談を申し込みたい', '恋愛講座について知りたい', 'LCIQ取扱店について知りたい', 'その他' ),
		'how'    => array( 'メール', '電話', '公式LINE' ),
		'time'   => array( '午前', '午後', '夕方以降', 'いつでも' ),
		'gender' => array( '男性', '女性' ),
	);
}

/** フォームの送信結果メッセージ */
function ll_form_notice() {
	if ( isset( $_GET['sent'] ) ) {
		return '<div class="ll-notice ll-ok" role="status"><strong>送信が完了しました。</strong><br>ご入力いただいたメールアドレスに、確認のメールをお送りしました。内容を確認のうえ、代表の有馬よりご連絡いたします。</div>';
	}
	if ( isset( $_GET['ll_err'] ) ) {
		$msg = array(
			'required' => '必須の項目が入力されていません。お手数ですが、もう一度ご入力ください。',
			'email'    => 'メールアドレスの形式が正しくありません。',
			'expired'  => '画面を開いてから時間が経ったため、送信できませんでした。お手数ですが、もう一度ご入力ください。',
			'mail'     => '送信に失敗しました。お手数ですが、お電話または公式LINEからご連絡ください。',
		);
		$key = sanitize_key( wp_unslash( $_GET['ll_err'] ) );
		$txt = isset( $msg[ $key ] ) ? $msg[ $key ] : $msg['required'];
		return '<div class="ll-notice ll-ng" role="alert">' . esc_html( $txt ) . '</div>';
	}
	return '';
}

/** 戻り先URL */
function ll_form_back( $args ) {
	$ref = wp_get_referer();
	$url = $ref ? $ref : ll_url( 'contact' );
	$url = remove_query_arg( array( 'sent', 'll_err' ), $url );
	$url = preg_replace( '/#.*$/', '', $url );
	return add_query_arg( $args, $url ) . '#form';
}

function ll_handle_contact() {
	$p = wp_unslash( $_POST );

	// ボット対策：隠し項目に入力がある／送信が速すぎる場合は、成功したように見せて破棄
	$ts = isset( $p['ll_ts'] ) ? (int) $p['ll_ts'] : 0;
	if ( ! empty( $p['ll_website'] ) || ( $ts && ( time() - $ts ) < 3 ) ) {
		wp_safe_redirect( ll_form_back( array( 'sent' => 1 ) ) );
		exit;
	}
	if ( ! isset( $p['ll_nonce'] ) || ! wp_verify_nonce( $p['ll_nonce'], 'll_contact' ) ) {
		wp_safe_redirect( ll_form_back( array( 'll_err' => 'expired' ) ) );
		exit;
	}

	$c = ll_form_choices();
	$f = array(
		'name'    => sanitize_text_field( isset( $p['name'] ) ? $p['name'] : '' ),
		'kana'    => sanitize_text_field( isset( $p['kana'] ) ? $p['kana'] : '' ),
		'age'     => sanitize_text_field( isset( $p['age'] ) ? $p['age'] : '' ),
		'gender'  => in_array( isset( $p['gender'] ) ? $p['gender'] : '', $c['gender'], true ) ? $p['gender'] : '',
		'email'   => sanitize_email( isset( $p['email'] ) ? $p['email'] : '' ),
		'tel'     => sanitize_text_field( isset( $p['tel'] ) ? $p['tel'] : '' ),
		'want'    => array_values( array_intersect( (array) ( isset( $p['want'] ) ? $p['want'] : array() ), $c['want'] ) ),
		'how'     => in_array( isset( $p['how'] ) ? $p['how'] : '', $c['how'], true ) ? $p['how'] : '',
		'time'    => array_values( array_intersect( (array) ( isset( $p['time'] ) ? $p['time'] : array() ), $c['time'] ) ),
		'message' => sanitize_textarea_field( isset( $p['message'] ) ? $p['message'] : '' ),
		'saw_hp'  => ! empty( $p['saw_hp'] ),
		'agree'   => ! empty( $p['agree'] ),
	);

	if ( '' === $f['name'] || '' === $f['age'] || '' === $f['gender'] || ! $f['want'] || '' === $f['how'] || ! $f['agree'] || empty( $p['email'] ) ) {
		wp_safe_redirect( ll_form_back( array( 'll_err' => 'required' ) ) );
		exit;
	}
	if ( ! is_email( $f['email'] ) ) {
		wp_safe_redirect( ll_form_back( array( 'll_err' => 'email' ) ) );
		exit;
	}

	$body  = "お名前：{$f['name']}\n";
	$body .= "ふりがな：{$f['kana']}\n";
	$body .= "年齢：{$f['age']}\n";
	$body .= "性別：{$f['gender']}\n";
	$body .= "メールアドレス：{$f['email']}\n";
	$body .= "電話番号：{$f['tel']}\n";
	$body .= 'ご希望の内容：' . implode( '、', $f['want'] ) . "\n";
	$body .= "ご希望のご連絡方法：{$f['how']}\n";
	$body .= 'ご連絡しやすい時間帯：' . implode( '、', $f['time'] ) . "\n";
	$body .= 'ホームページを見た：' . ( $f['saw_hp'] ? 'はい（キャンペーン対象）' : '―' ) . "\n";
	$body .= "\n■ご相談内容\n{$f['message']}\n";

	$to      = ll_opt( 'll_form_to' );
	$subject = '【LOVE LIVING】お問い合わせがありました（' . $f['name'] . ' 様）';
	$headers = array( 'Reply-To: ' . $f['name'] . ' <' . $f['email'] . '>' );
	$ok      = wp_mail( $to, $subject, $body . "\n---\n送信日時：" . wp_date( 'Y年n月j日 H:i' ) . "\n送信元：" . home_url( '/' ) . "\n", $headers );

	if ( ! $ok ) {
		wp_safe_redirect( ll_form_back( array( 'll_err' => 'mail' ) ) );
		exit;
	}

	$reply  = "{$f['name']} 様\n\n";
	$reply .= "LOVELIVING恋愛結婚相談所にお問い合わせいただき、ありがとうございます。\n";
	$reply .= "以下の内容で受け付けました。内容を確認のうえ、代表の有馬よりご連絡いたします。\n\n";
	$reply .= "――――――――――――――――\n" . $body . "――――――――――――――――\n\n";
	$reply .= "※このメールは送信専用です。ご返信いただいてもお答えできませんので、ご了承ください。\n";
	$reply .= "※お心当たりのない場合は、お手数ですがこのメールを破棄してください。\n\n";
	$reply .= "LOVELIVING恋愛結婚相談所\nTEL 070-2277-1151（10:00〜22:00 年中無休）\n" . home_url( '/' ) . "\n";
	wp_mail( $f['email'], '【LOVE LIVING】お問い合わせを受け付けました', $reply );

	wp_safe_redirect( ll_form_back( array( 'sent' => 1 ) ) );
	exit;
}
add_action( 'admin_post_nopriv_ll_contact', 'll_handle_contact' );
add_action( 'admin_post_ll_contact', 'll_handle_contact' );

/** 送信者名を「LOVE LIVING」に */
add_filter( 'wp_mail_from_name', function ( $name ) {
	return 'WordPress' === $name ? 'LOVE LIVING' : $name;
} );
