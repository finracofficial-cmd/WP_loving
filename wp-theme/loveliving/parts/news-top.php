<?php
/** トップページのお知らせ欄（最新3件。記事が0件なら何も表示しない） */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ll_q = new WP_Query( array(
	'post_type'           => 'post',
	'posts_per_page'      => 3,
	'ignore_sticky_posts' => true,
	'no_found_rows'       => true,
) );
if ( ! $ll_q->have_posts() ) {
	return;
}
?>
<section class="sec"><div class="wrap">
<div class="sec-h"><span class="en">News &amp; Event</span><h2>お知らせ・イベント</h2>
<p>イベントのご案内や、開催したイベントの様子をお届けします。</p></div>
<div class="news-grid">
<?php
while ( $ll_q->have_posts() ) :
	$ll_q->the_post();
	get_template_part( 'parts/news-card' );
endwhile;
wp_reset_postdata();
?>
</div>
<p style="text-align:center;margin-top:34px"><a class="btn btn-main" href="<?php echo esc_url( ll_url( 'news' ) ); ?>">お知らせ・イベント一覧へ</a></p>
</div></section>
