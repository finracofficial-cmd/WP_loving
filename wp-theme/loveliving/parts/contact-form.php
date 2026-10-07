<?php
/** お問い合わせフォーム（送信処理は inc/contact.php） */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ll_c   = ll_form_choices();
$ll_req = '<span class="req">必須</span>';
echo ll_form_notice(); // phpcs:ignore -- 固定文言のみ
?>
<form class="ll-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
<input type="hidden" name="action" value="ll_contact">
<input type="hidden" name="ll_ts" value="<?php echo esc_attr( time() ); ?>">
<?php wp_nonce_field( 'll_contact', 'll_nonce' ); ?>
<div class="ll-hp" aria-hidden="true"><label>ウェブサイト<input type="text" name="ll_website" tabindex="-1" autocomplete="off"></label></div>
<div class="ll-grid">
<label>お名前 <?php echo $ll_req; // phpcs:ignore ?><input name="name" required autocomplete="name" placeholder="山田 太郎"></label>
<label>ふりがな<input name="kana" placeholder="やまだ たろう"></label>
<div class="ll-2col">
<label>年齢 <?php echo $ll_req; // phpcs:ignore ?><input name="age" required inputmode="numeric" placeholder="35"></label>
<label>性別 <?php echo $ll_req; // phpcs:ignore ?><select name="gender" required><option value="">選択してください</option>
<?php foreach ( $ll_c['gender'] as $v ) : ?><option><?php echo esc_html( $v ); ?></option><?php endforeach; ?>
</select></label></div>
<label>メールアドレス <?php echo $ll_req; // phpcs:ignore ?><input type="email" name="email" required autocomplete="email" placeholder="example@mail.com"></label>
<label>電話番号<input type="tel" name="tel" autocomplete="tel" placeholder="090-0000-0000"></label>
<fieldset class="ll-want"><legend>ご希望の内容 <?php echo $ll_req; // phpcs:ignore ?></legend>
<?php foreach ( $ll_c['want'] as $v ) : ?><label class="ll-chk"><input type="checkbox" name="want[]" value="<?php echo esc_attr( $v ); ?>"> <?php echo esc_html( $v ); ?></label><?php endforeach; ?>
</fieldset>
<fieldset><legend>ご希望のご連絡方法 <?php echo $ll_req; // phpcs:ignore ?></legend>
<?php foreach ( $ll_c['how'] as $i => $v ) : ?><label class="ll-inl"><input type="radio" name="how" value="<?php echo esc_attr( $v ); ?>"<?php echo 0 === $i ? ' required' : ''; ?>> <?php echo esc_html( $v ); ?></label><?php endforeach; ?>
</fieldset>
<fieldset><legend>ご連絡しやすい時間帯</legend>
<?php foreach ( $ll_c['time'] as $v ) : ?><label class="ll-inl"><input type="checkbox" name="time[]" value="<?php echo esc_attr( $v ); ?>"> <?php echo esc_html( $v ); ?></label><?php endforeach; ?>
</fieldset>
<label>ご相談内容<textarea name="message" rows="5" placeholder="どんなことでも構いません。"></textarea></label>
<label class="ll-hp-saw"><input type="checkbox" name="saw_hp" value="1"> <strong>ホームページを見た</strong>（キャンペーン対象になります）</label>
<label class="ll-agree"><input type="checkbox" name="agree" value="1" required> <a href="<?php echo esc_url( ll_url( 'privacy' ) ); ?>" target="_blank" rel="noopener">プライバシーポリシー</a>に同意します <?php echo $ll_req; // phpcs:ignore ?></label>
<button class="btn btn-main ll-submit" type="submit">送信する</button>
</div>
</form>
<script>
(function(){var f=document.querySelector('.ll-form');if(!f)return;f.addEventListener('submit',function(e){var w=f.querySelectorAll('input[name="want[]"]:checked').length;if(!w){e.preventDefault();alert('「ご希望の内容」を1つ以上お選びください。');f.querySelector('.ll-want').scrollIntoView({behavior:'smooth',block:'center'});return;}var b=f.querySelector('.ll-submit');b.disabled=true;b.textContent='送信しています…';});})();
</script>
