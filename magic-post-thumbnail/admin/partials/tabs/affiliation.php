<?php
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
		exit();
}

?>              
<div class="wrap">

    <?php $this->mpt_freemius()->_affiliation_page_render(); ?>

</div>