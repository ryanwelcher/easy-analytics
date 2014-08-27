<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', '<?php echo esc_js( $settings['tracking_id'] ); ?>', 'auto');
  <?php if( isset( $settings['enhanced_link_attribution'] ) && 'yes' === $settings['enhanced_link_attribution'] ) : ?>
  ga('require', 'linkid', 'linkid.js');
  <?php endif; ?>
  ga('send', 'pageview');
</script>