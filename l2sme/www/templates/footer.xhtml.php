<?php if ($noindex): ?>
		<center>
			<a href="http://zhumarin.ru"><?= date("Y") ?> &copy; Kirill Zhumarin</a><br />
			<script type="text/javascript" src="http://mobtop.ru/c/91077.js"></script>
			<noscript><a href="http://mobtop.ru/in/91077"><img src="http://mobtop.ru/91077.gif" alt="MobTop.Ru - рейтинг мобильных сайтов"/></a></noscript>
		</center>
<?php else: ?>
	<center style="z-index:999999999999999;bottom:0;left:0;right:0;position:absolute">
		<script type="text/javascript" src="http://mobtop.ru/c/91076.js"></script>
		<noscript><a href="http://mobtop.ru/in/91076"><img src="http://mobtop.ru/91076.gif" alt="MobTop.Ru - рейтинг мобильных сайтов"/></a></noscript>
	</center>
<?php endif; ?>

<!-- Yandex.Metrika counter -->
<script type="text/javascript">
(function (d, w, c) {
    (w[c] = w[c] || []).push(function() {
        try {
            w.yaCounter25609397 = new Ya.Metrika({
				id:25609397,
				clickmap:true,
				accurateTrackBounce:true,
				<?= $noindex ? 'ut:"noindex"' : '' ?>
			});
        } catch(e) { }
    });

    var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function () { n.parentNode.insertBefore(s, n); };
    s.type = "text/javascript";
    s.async = true;
    s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

    if (w.opera == "[object Opera]") {
        d.addEventListener("DOMContentLoaded", f, false);
    } else { f(); }
})(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="//mc.yandex.ru/watch/25609397<?= $noindex ? '?ut=noindex' : '' ?>" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter -->
	</body>
</html>
