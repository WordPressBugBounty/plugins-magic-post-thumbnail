const { dispatch, select } = wp.data;
const { createBlock } = wp.blocks;

function mptOpenDocumentSidebar() {
	var run = function() {
		try {
			if ( dispatch( 'core/block-editor' ) && dispatch( 'core/block-editor' ).clearSelectedBlock ) {
				dispatch( 'core/block-editor' ).clearSelectedBlock();
			}
			if ( dispatch( 'core/edit-post' ) && dispatch( 'core/edit-post' ).openGeneralSidebar ) {
				dispatch( 'core/edit-post' ).openGeneralSidebar( 'edit-post/document' );
			}
			if ( dispatch( 'core/interface' ) && dispatch( 'core/interface' ).enableComplementaryArea ) {
				dispatch( 'core/interface' ).enableComplementaryArea( 'core/edit-post', 'edit-post/document' );
				dispatch( 'core/interface' ).enableComplementaryArea( 'core', 'edit-post/document' );
			}
		} catch ( err ) {}
	};
	run();
	window.setTimeout( run, 50 );
	window.setTimeout( run, 200 );
}

function addMPTBlockAndOpenModal() {
	const block = createBlock( 'mpt/mpt-images' );
	dispatch( 'core/block-editor' ).insertBlock( block, undefined, undefined, false );
	setTimeout( function() {
		modalToExecute( block );
		mptOpenDocumentSidebar();
	}, 1 );
}

function modalToExecute( block ) {
	const event = new CustomEvent( 'openMPTModal', { detail: block } );
	document.dispatchEvent( event );
}
