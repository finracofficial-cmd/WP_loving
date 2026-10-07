<?php
/** お知らせ・イベント一覧（すべて／カテゴリー別） */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$ll_is_cat = is_category();
$ll_title  = 'お知らせ・イベント';
get_template_part( 'parts/page-head', null, array(
	'title'   => $ll_title,
	'en'      => 'News & Event',
	'tag'     => $ll_is_cat ? 'p' : 'h1',
	'parent'  => $ll_is_cat ? array( $ll_title, ll_url( 'news' ) ) : null,
	'current' => $ll_is_cat ? single_cat_title( '', false ) : '',
) );
$ll_tabs = array( '' => 'すべて', 'event-info' => 'イベント告知', 'event-report' => 'イベントレポート', 'info' => 'お知らせ' );
$ll_now  = $ll_is_cat ? get_queried_object()->slug : '';
?>
<section class="sec"><div class="wrap">
<?php if ( $ll_is_cat ) : ?><h1 class="screen-reader-text"><?php echo esc_html( single_cat_title( '', false ) ); ?></h1><?php endif; ?>
<div class="ntabs">
<?php
foreach ( $ll_tabs as $slug => $label ) :
	if ( '' === $slug ) {
		$href = ll_url( 'news' );
	} else {
		$term = get_term_by( 'slug', $slug, 'category' );
		if ( ! $term ) {
			continue;
		}
		$href = get_category_link( $term );
	}
	?>
<a href="<?php echo esc_url( $href ); ?>"<?php echo $slug === $ll_now ? ' class="on" aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?></a>
<?php endforeach; ?>
</div>
<?php if ( have_posts() ) : ?>
<div class="news-grid">
	<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'parts/news-card' );
	endwhile;
	?>
</div>
	<?php
	$ll_pg = paginate_links( array(
		'type'      => 'array',
		'prev_text' => '前へ',
		'next_text' => '次へ',
		'mid_size'  => 1,
	) );
	if ( $ll_pg ) {
		echo '<nav class="pager" aria-label="ページ送り">' . implode( '', $ll_pg ) . '</nav>'; // phpcs:ignore
	}
	?>
<?php else : ?>
<p class="lead" style="text-align:center">ただいま、掲載している記事はありません。<br>イベントのご案内は、公式LINE・Instagramでもお知らせしています。</p>
<?php endif; ?>
</div></section>
<?php get_template_part( 'parts/cta' ); ?>
