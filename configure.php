<?php
require_once( 'config.php' );
require_once( 'functions.php' );
require_once( 'decode-url.php' );
require_once( 'template.php' );

?>
<html>
 <head>
<?php emitHtmlHead( $title . " &mdash; Configure" ); ?>
 </head>
 <body>
<?php emitHeader( $title . " &mdash; Configure" ); ?>
  <div class="body">
<?php
require_once( 'show-devices.php' );
?>
  </div>
<?php emitFooter(); ?>
 </body>
</html>

