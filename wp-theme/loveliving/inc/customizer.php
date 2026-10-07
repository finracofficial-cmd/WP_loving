<?php
/**
 * 外観 → カスタマイズ →「LOVE LIVING の設定」
 * 変更が見込まれる項目だけを、管理画面から書き換えられるようにしています。
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ll_defaults() {
	return array(
		'll_pay_methods' => '現金／銀行振込／口座引き去り',
		'll_pay_note'    => '',
		'll_debit_day'   => '27',
		'll_form_to'     => '',
	);
}

/** 設定値を取り出す */
function ll_opt( $key ) {
	$d = ll_defaults();
	$v = get_theme_mod( $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
	if ( 'll_form_to' === $key && ! is_email( $v ) ) {
		$v = get_option( 'admin_email' );
	}
	return $v;
}

/** お支払い方法（補足があれば改行して続ける） */
function ll_pay_html() {
	$html = esc_html( ll_opt( 'll_pay_methods' ) );
	$note = trim( ll_opt( 'll_pay_note' ) );
	if ( $note ) {
		$html .= '<br>' . esc_html( $note );
	}
	return $html;
}

add_action( 'customize_register', function ( $wp_customize ) {
	$d = ll_defaults();

	$wp_customize->add_section( 'll_settings', array(
		'title'       => 'LOVE LIVING の設定',
		'priority'    => 30,
		'description' => 'お支払い方法・口座振替日・お問い合わせの送信先を変更できます。料金ページ・よくあるご質問・特定商取引法の表記に自動で反映されます。',
	) );

	$wp_customize->add_setting( 'll_pay_methods', array(
		'default'           => $d['ll_pay_methods'],
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'll_pay_methods', array(
		'section'     => 'll_settings',
		'label'       => 'お支払い方法',
		'description' => '例：現金／銀行振込／口座引き去り／クレジットカード',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'll_pay_note', array(
		'default'           => $d['ll_pay_note'],
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'll_pay_note', array(
		'section'     => 'll_settings',
		'label'       => 'お支払い方法の補足（任意）',
		'description' => '例：初期費用・成婚料は、クレジットカード・PayPayでもお支払いいただけます',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'll_debit_day', array(
		'default'           => $d['ll_debit_day'],
		'sanitize_callback' => function ( $v ) {
			$v = absint( $v );
			return ( $v >= 1 && $v <= 31 ) ? (string) $v : '27';
		},
	) );
	$wp_customize->add_control( 'll_debit_day', array(
		'section'     => 'll_settings',
		'label'       => '月会費の口座振替日（毎月○日）',
		'type'        => 'number',
		'input_attrs' => array( 'min' => 1, 'max' => 31 ),
	) );

	$wp_customize->add_setting( 'll_form_to', array(
		'default'           => $d['ll_form_to'],
		'sanitize_callback' => 'sanitize_email',
	) );
	$wp_customize->add_control( 'll_form_to', array(
		'section'     => 'll_settings',
		'label'       => 'お問い合わせの送信先メールアドレス',
		'description' => '空欄のときは「設定 → 一般」の管理者メールアドレスに届きます。',
		'type'        => 'email',
	) );
} );
