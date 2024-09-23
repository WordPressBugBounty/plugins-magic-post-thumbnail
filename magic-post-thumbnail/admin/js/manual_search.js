const { dispatch, select } = wp.data;
const { createBlock } = wp.blocks;

function addMPTBlockAndOpenModal() {

    /*
    const selectedBlockClientId = select('core/block-editor').getSelectedBlockClientId();

    const indexBlock = wp.data.select('core/block-editor').getBlocks().map(function(block) { 
        return block.clientId == selectedBlockClientId; 
    }).indexOf(true) + 1;*/


    // Create an instance of the mpt/mpt-images block
    const block = createBlock('mpt/mpt-images');

    //dispatch('core/block-editor').insertBlock(block, indexBlock);
    dispatch('core/block-editor').insertBlock(block);

    setTimeout(() => modalToExecute(block), 1);
}

function modalToExecute(block) {
    // Trigger a custom event to open the modal
    const event = new CustomEvent('openMPTModal', { detail: block });
    document.dispatchEvent(event);
}
