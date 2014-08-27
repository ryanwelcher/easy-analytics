<script type="text/javascript">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', '<?php echo esc_js( $settings['tracking_id'] ); ?>']);
	<?php
	//domain name
	if( isset( $settings['domain_name'] ) && !empty( $settings['domain_name'] ) ) : ?>
	_gaq.push(['_setDomainName', '<?php echo esc_js( $settings['domain_name'] ); ?>']);
	<?php endif;  ?>
	<?php
	//enhanced link attributation
	if( isset( $settings['enhanced_link_attribution'] ) && 'yes' === $settings['enhanced_link_attribution'] ) : ?>
	var pluginUrl = '//www.google-analytics.com/plugins/ga/inpage_linkid.js';
	_gaq.push(['_require', 'inpage_linkid', pluginUrl]);
	<?php endif; ?>
	_gaq.push(['_trackPageview']);
	(function() {
		 var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		 ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		 var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
</script>