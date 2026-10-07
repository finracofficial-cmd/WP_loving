// 運用マニュアル（docs/manual/manual.html）を PDF にする： NODE_PATH=$(npm root -g) node tools/build_manual.js
const { chromium } = require('playwright');
(async () => {
  const b = await chromium.launch({executablePath:'/opt/pw-browsers/chromium'});
  const p = await b.newPage();
  await p.goto('file://'+require('path').resolve(__dirname,'../docs/manual/manual.html'),{waitUntil:'load'}); await p.waitForTimeout(500);
  await p.pdf({path:require('path').resolve(__dirname,'../dist')+'/LOVELIVING_ホームページ運用マニュアル.pdf', format:'A4', printBackground:true, displayHeaderFooter:true,
    headerTemplate:'<div></div>',
    footerTemplate:'<div style="width:100%;font-size:8px;color:#888;text-align:center;font-family:IPAPGothic">LOVE LIVING ホームページ運用マニュアル　<span class="pageNumber"></span> / <span class="totalPages"></span></div>',
    margin:{top:'16mm',bottom:'18mm',left:'15mm',right:'15mm'}});
  await b.close();
})();
