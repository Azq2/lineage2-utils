
		<center><a href="http://zhumarin.ru/index.html"><?= date("Y") ?> &copy; Kirill Zhumarin</a></center>
<!-- Yandex.Metrika counter -->
<script type="text/javascript">
var yaParams = <?= json_encode() ?>;
</script>

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
