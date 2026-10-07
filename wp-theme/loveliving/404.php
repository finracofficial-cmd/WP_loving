<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
get_header();
get_template_part( 'parts/page-head', null, array( 'title' => 'ページが見つかりません', 'en' => 'Not Found' ) );
?>
<section class="sec"><div class="wrap narrow" style="text-align:center">
<p class="lead">お探しのページは、移動または削除された可能性があります。</p>
<p style="margin-top:28px"><a class="btn btn-main" href="<?php echo esc_url( ll_url( 'home' ) ); ?>">トップページへ</a></p>
</div></section>
<?php
get_footer();
