# -*- coding: utf-8 -*-
"""確認用ページ（preview/*.html）から WordPress テーマの中身を生成する。

  python3 tools/build_theme.py

- preview/ の各ページの本文（ヘッダーとフッターの間）を templates/ に PHP として書き出す
- 内部リンク・画像パスを WordPress 用に置き換える
- お知らせ欄・お問い合わせフォーム・お支払い方法は動的な PHP に差し替える
- assets/ をテーマにコピーし、配布用 ZIP を dist/ に作る
"""
import io
import os
import re
import shutil
import zipfile

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
PREVIEW = os.path.join(ROOT, "preview")
THEME = os.path.join(ROOT, "wp-theme", "loveliving")
DIST = os.path.join(ROOT, "dist")

PAGES = ["about", "flow", "lciq", "price", "faq", "contact", "privacy", "law"]


def read(name):
    return io.open(os.path.join(PREVIEW, name), encoding="utf-8").read()


def body_of(html):
    a = html.index("</header>") + len("</header>")
    b = html.index('<footer class="ft">')
    return html[a:b].strip("\n")


def php_links(s):
    def href(m):
        slug, frag = m.group(1), m.group(2) or ""
        slug = "home" if slug == "index" else slug
        return 'href="<?php echo esc_url( ll_url( \'%s%s\' ) ); ?>"' % (slug, frag)

    s = re.sub(r'href="([a-z]+)\.html(#[a-z0-9_-]+)?"', href, s)
    s = re.sub(r'(src|srcset)="assets/img/([^"]+)"',
               lambda m: '%s="<?php echo esc_url( ll_img( \'%s\' ) ); ?>"' % (m.group(1), m.group(2)), s)
    assert ".html" not in re.sub(r'https?://[^"]+', "", s), "未変換のリンクがあります"
    assert 'src="assets/' not in s
    return s


def sub1(s, old, new):
    n = s.count(old)
    assert n == 1, (n, old[:60])
    return s.replace(old, new)


PAY = "<?php echo ll_pay_html(); ?>"
DAY = "<?php echo esc_html( ll_opt( 'll_debit_day' ) ); ?>"


def page_specific(slug, s):
    if slug == "price":
        s = sub1(s, "<tr><th>お支払い方法</th><td>現金／銀行振込／口座引き去り</td></tr>",
                 "<tr><th>お支払い方法</th><td>%s</td></tr>" % PAY)
        s = sub1(s, "口座振替（毎月27日）", "口座振替（毎月%s日）" % DAY)
    elif slug == "faq":
        s = sub1(s, "現金・銀行振込・口座引き去りに対応しております。",
                 "<?php echo esc_html( ll_opt( 'll_pay_methods' ) ); ?>に対応しております。"
                 "<?php if ( ll_opt( 'll_pay_note' ) ) { echo esc_html( ll_opt( 'll_pay_note' ) ) . '。'; } ?>")
        s = sub1(s, "月会費は毎月27日の口座振替です。", "月会費は毎月%s日の口座振替です。" % DAY)
    elif slug == "law":
        s = sub1(s, "現金／銀行振込／口座引き去り<br>", PAY + "<br>")
        s = sub1(s, "月会費：毎月27日の口座振替", "月会費：毎月%s日の口座振替" % DAY)
    elif slug == "contact":
        a = s.index("<form ")
        b = s.index("</form>") + len("</form>")
        s = s[:a] + "<?php get_template_part( 'parts/contact-form' ); ?>" + s[b:]
        s = re.sub(r'<p class="trust-note"[^>]*>※これは確認用のサンプルです。[^<]*</p>\n?', "", s)
        s = sub1(s, '<div class="card" style="background:#fff">', '<div class="card" id="form" style="background:#fff">')
    return s


def split_cta(s):
    """末尾の「まずは、恋愛診断をどうぞ」ブロックを共通パーツに置き換える"""
    m = re.search(r'<section class="cta">.*?</section>', s, re.S)
    if not m:
        return s, None
    return s[:m.start()] + "<?php get_template_part( 'parts/cta' ); ?>" + s[m.end():], m.group(0)


HEAD = "<?php\n/* このファイルは tools/build_theme.py で生成しています。文章を直すときは preview を直して再生成してください。 */\nif ( ! defined( 'ABSPATH' ) ) { exit; }\n?>\n"


def write(rel, text):
    path = os.path.join(THEME, rel)
    os.makedirs(os.path.dirname(path), exist_ok=True)
    io.open(path, "w", encoding="utf-8").write(text)


def main():
    cta = None
    for slug in PAGES:
        s = body_of(read(slug + ".html"))
        s = page_specific(slug, s)
        s, c = split_cta(s)
        cta = cta or c
        write("templates/page-%s.php" % slug, HEAD + php_links(s) + "\n")

    # トップページ：お知らせ欄を最新記事の表示に差し替え
    s = body_of(read("index.html"))
    m = re.search(r'<section class="sec"><div class="wrap">\n<div class="sec-h"><span class="en">News &amp; Event</span>.*?</section>', s, re.S)
    assert m, "お知らせ欄が見つかりません"
    s = s[:m.start()] + "<?php get_template_part( 'parts/news-top' ); ?>" + s[m.end():]
    s, c = split_cta(s)
    cta = cta or c
    write("templates/front.php", HEAD + php_links(s) + "\n")

    write("parts/cta.php", HEAD + php_links(cta) + "\n")

    # assets
    dst = os.path.join(THEME, "assets")
    for sub in ("css", "img"):
        src_dir = os.path.join(PREVIEW, "assets", sub)
        os.makedirs(os.path.join(dst, sub), exist_ok=True)
        for f in os.listdir(src_dir):
            if f.startswith("."):
                continue
            if sub == "css" and f != "style.css":
                continue
            shutil.copy2(os.path.join(src_dir, f), os.path.join(dst, sub, f))

    # 配布用 ZIP（フォルダ名 loveliving/ を含める）
    os.makedirs(DIST, exist_ok=True)
    out = os.path.join(DIST, "loveliving-theme.zip")
    with zipfile.ZipFile(out, "w", zipfile.ZIP_DEFLATED) as z:
        for base, dirs, files in os.walk(THEME):
            dirs[:] = sorted(d for d in dirs if not d.startswith("."))
            for f in sorted(files):
                if f.startswith("."):
                    continue
                full = os.path.join(base, f)
                z.write(full, os.path.join("loveliving", os.path.relpath(full, THEME)))
    print("built", out, os.path.getsize(out), "bytes")


if __name__ == "__main__":
    main()
