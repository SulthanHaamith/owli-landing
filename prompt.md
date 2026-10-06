Add this Site24x7 monitoring script to index.html.

Place it right before the closing </head> tag, after all other scripts and meta tags.

Add a comment above it:

<!-- ============================================
     MONITORING: Site24x7 Real User Monitoring (RUM)
     PURPOSE: Performance monitoring for testing.
     Remove this script before going to production if no longer needed.
     ============================================ -->

The script:

<script type="text/javascript">
(function(w,d,s,r,k,h,m){
	if(w.performance && w.performance.timing && w.performance.navigation && !w.s247r) {
		w[r] = w[r] || function(){(w[r].q = w[r].q || []).push(arguments)};
		h=d.createElement('script');h.async=true;h.setAttribute('src',s+k);
		d.getElementsByTagName('head')[0].appendChild(h);
		(m = window.onerror),(window.onerror = function (b, c, d, f, g) {
		m && m(b, c, d, f, g),g || (g = new Error(b)),(w[r].q = w[r].q || []).push(["captureException",g]);})
	}
})(window,document,'//static.site24x7rum.in/beacon/site24x7rum-min.js?appKey=','s247r','ef1e6007c4f9ca249f39321ddf754a62');
</script>

No other changes.
