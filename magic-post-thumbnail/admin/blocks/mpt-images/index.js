import { __, _x } from '@wordpress/i18n';
import './editor.css';
import jQuery from 'jquery';
import { registerBlockType } from '@wordpress/blocks';
import { Button, Modal, TabPanel, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import {
    useBlockProps,
    BlockControls,
    AlignmentToolbar, 
} from '@wordpress/block-editor';
import background from './img/loader-mpt.gif';
import getBankPaths from './getBankPaths';



registerBlockType('mpt/mpt-images', {
    apiVersion: 3,
    title: 'MPT Images',
    icon: 'format-image',
    category: 'media',
    attributes: {
        alignment: {
            type: 'string',
            default: 'none',
        },
    },
    edit: ( props ) => {

        const { attributes, setAttributes, clientId } = props;

        const [isModalOpen, setIsModalOpen] = useState(false);
        const [searchTerm, setSearchTerm] = useState('');
        const [resultsSearch, setResultsSearch] = useState({});
        const [tabIndex, setTabIndex] = useState(null);
        const [FeaturedImageDiv, setFeaturedImageDiv] = useState(false);
        const [clientIDFeaturedImage, setClientIDFeaturedImage] = useState('');

        const searchForImagesText   = __( 'Search for Images', 'mpt' );
        const closeTheWindowText    = __( 'Close the window', 'mpt' );
        const downlowdingMedia      = __( 'Image is downloading to media, please wait a few seconds.', 'mpt' );
        const searchStr             = __( 'Search', 'mpt' );


        const onChangeAlignment = ( newAlignment ) => {
            setAttributes({
                alignment: newAlignment === undefined ? 'none' : newAlignment,
            });
        };


        async function handleSetFeaturedImage(url_image, alt_image, caption_image, searchTerm, bank, url_thumbnail = null) {
            
            //console.log('Setting as featured image...');

            // Find the div with the 'loader-mpt' class and change its classes
            const loaderDiv = document.querySelector('.loader-mpt');
            
            if (loaderDiv) {
                loaderDiv.classList.add('show');
                loaderDiv.classList.remove('hidden');
            }

            const data = {
                action: 'block_downloading_image',
                url_image: url_image,
                alt_image: alt_image,
                caption_image: caption_image,
                bank: bank,
                search_term: searchTerm,
                nonce: mptAjax.nonce,
            };
            if (url_thumbnail) {
                data.url_thumbnail = url_thumbnail;
            }

            try {
                const response = await jQuery.post(mptAjax.ajax_url, data).promise();
                if (response.success) {
                    //console.log('Image added into media:', response.data); 

                    // Define featured image
                    //const postId = wp.data.select('core/editor').getCurrentPostId();
                    wp.data.dispatch('core/editor').editPost({ featured_media: response.data.id_media });
                    //console.log('set as featured successfully', response.data);

                    // Save the post
                    wp.data.dispatch('core/editor').savePost();

                    // Close the modal
                    setIsModalOpen(false);

                    // Check if this is featured Image Modal & the right Block Id
                    if ( FeaturedImageDiv && ( clientIDFeaturedImage == clientId ) ) {
                        dispatch('core/block-editor').removeBlock(clientId);
                    }


                } else {
                    console.error('Error:', response.data);
                    const errorMsg = response.data?.erreur || response.data?.error || __( 'Image could not be downloaded.', 'mpt' );
                    if ( wp.data.dispatch( 'core/notices' )?.createNotice ) {
                        wp.data.dispatch( 'core/notices' ).createNotice( 'error', errorMsg, { type: 'snackbar' } );
                    }
                }
            } catch (error) {
                console.error('Network Error', error);
                if ( wp.data.dispatch( 'core/notices' )?.createNotice ) {
                    wp.data.dispatch( 'core/notices' ).createNotice( 'error', __( 'Network error. Image could not be downloaded.', 'mpt' ), { type: 'snackbar' } );
                }
            } finally {
                if (loaderDiv) {
                    loaderDiv.classList.add('hidden');
                    loaderDiv.classList.remove('show');
                }
            }
        }

        
        async function handleUseImageClick(url_image, alt_image, caption_image, searchTerm, bank, url_thumbnail = null) {

            // Action to be taken before AJAX call
            //console.log('ACTION BEFORE DOWNLOAD');

            // Find the div with the 'loader-mpt' class and change its classes
            const loaderDiv = document.querySelector('.loader-mpt');
            
            if (loaderDiv) {
                loaderDiv.classList.add('show');
                loaderDiv.classList.remove('hidden');
            }

            const data = {
                action: 'block_downloading_image',
                url_image: url_image,
                alt_image: alt_image,
                caption_image: caption_image,
                bank: bank,
                search_term: searchTerm,
                nonce: mptAjax.nonce,
            };
            if (url_thumbnail) {
                data.url_thumbnail = url_thumbnail;
            }

            try {
                const response = await jQuery.post(mptAjax.ajax_url, data).promise();
                //console.log( response );
                if (response.success) {
                    
                    //console.log('Image successfully downloaded:', response.data);

                    // Insert image block if download successful
                    const { dispatch, select } = wp.data;

                    const selectedBlockClientId = select('core/block-editor').getSelectedBlockClientId();
                    const indexBlock = wp.data.select('core/block-editor').getBlocks().map(function(block) { 
                        return block.clientId == selectedBlockClientId; 
                    }).indexOf(true) + 1;
                    const newBlock = wp.blocks.createBlock('core/image', {
                        //id: response.data.id_media
                        url: response.data.url_media,
                        alt: response.data.alt_image,
                        caption: response.data.caption_image
                    });

                    // Insert the new block just after the currently selected block
                    dispatch('core/block-editor').insertBlock(newBlock, indexBlock);

                    // Delete the currently selected block
                    dispatch('core/block-editor').removeBlock(selectedBlockClientId);

                } else {
                    console.error('Error:', response.data);
                    const errorMsg = response.data?.erreur || response.data?.error || __( 'Image could not be downloaded.', 'mpt' );
                    if ( wp.data.dispatch( 'core/notices' )?.createNotice ) {
                        wp.data.dispatch( 'core/notices' ).createNotice( 'error', errorMsg, { type: 'snackbar' } );
                    }
                }
            } catch (error) {
                console.error('Network Error', error);
                if ( wp.data.dispatch( 'core/notices' )?.createNotice ) {
                    wp.data.dispatch( 'core/notices' ).createNotice( 'error', __( 'Network error. Image could not be downloaded.', 'mpt' ), { type: 'snackbar' } );
                }
            } finally {
                if (loaderDiv) {
                    loaderDiv.classList.add('hidden');
                    loaderDiv.classList.remove('show');
                }
            }
        }

        function toggleModal(shouldSearch = true, blockData = '') {

            setClientIDFeaturedImage(blockData.clientId);

            setFeaturedImageDiv(true);
            setIsModalOpen(!isModalOpen);
        }


        document.addEventListener('openMPTModal', (event) => {
            const blockData = event.detail;
            toggleModal(false, blockData);
        });
        



        function handleSearchChange(value) {
            setSearchTerm(value);
        }

        function onChangeContent(newContent) {
            setContent(newContent);
        }

        async function searchAllTabs(shouldSearch = true) {

            const postId    = wp.data.select('core/editor').getCurrentPostId();
            const post      = wp.data.select('core').getEntityRecord('postType', 'post', postId);

            try {
                // Saves the post only if it has never been saved before
                if (post && post.status === 'auto-draft' ) {
                    await wp.data.dispatch('core/editor').savePost();
                }
        
                Object.entries(mptAjax.choosed_banks).forEach(([key, bank], index) => {
                    handleSearchSubmit(bank, index, shouldSearch);
                });
            } catch (error) {
                console.error('Error while registering post :', error);
            }
        }

        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
            return array;
        }
        
    
        function handleSearchSubmit(bank, index, shouldSearch = true) {

            let bankGenerate = '';

            if( bank === 'openverse' ) {
                bankGenerate = 'cc_search';
            } /* else if( bank === 'Envato Elements' ) { // DISABLED - Envato Elements no longer working
                bankGenerate = 'envato';
            } */ else {
                bankGenerate = bank;
            }

            // Show loader during search results */
            let loaderSearch = document.querySelector(`.loader-searching-${index}`);
            if( loaderSearch ) {
                loaderSearch.classList.remove('hidden');
            }
            
            // Empty previous results
            let restultDiv = document.querySelector(`.results-${index} ul`);
            if (restultDiv) {
                restultDiv.classList.add('hidden');
            }

            const ajaxUrl   = mptAjax.ajax_url;
            const postId    = wp.data.select('core/editor').getCurrentPostId();
            const params    = new URLSearchParams({
                action: 'block_searching_images',
                search: searchTerm,
                bank: bankGenerate,
                index: index,
                id: postId,
                nonce: mptAjax.nonce
            });

            const { apiPath, altPath, captionPath, imgPath, imgPreview, is_premium } = getBankPaths(bank);
            const licensing = mptAjax.licensing_data ? mptAjax.licensing_data : false;

            let displayResults = '';
        
            fetch(`${ajaxUrl}?${params.toString()}`)
                .then(response => response.json())
                .then(data => {

                    let images;
                    let results;
                    let finalUrl;

                    // Only for Pro version
                    if (licensing !== '1' && is_premium ) {
                        images = (
                            <p>{__( 'Available in the Pro version only.', 'mpt' )}</p>
                        );

                        setResultsSearch(prevResults => ({
                            ...prevResults,
                            [index]: images,
                        }));

                    } else if (data.success) {

                        setTabIndex(data.data.index);

                        /*
                        console.log( bankGenerate );
                        console.log( data );
                        console.log( '================' );*/

                        if (data.success && data.data && data.data.results && data.data.results[apiPath]) {

                            if (data.data.results[apiPath].length > 0) {
                                images = renderImages(licensing, apiPath, imgPreview, altPath, captionPath, imgPath, bankGenerate, data);
                                if (licensing !== '1') {
                                    images = (
                                        <>
                                            {images}
                                            {renderUnlockMessage()}
                                        </>
                                    );
                                }
                            } else {
                                images = renderNoResults();
                            }

                            setResultsSearch(prevResults => ({
                                ...prevResults,
                                [index]: images,
                            }));

                        } else {
                            console.error('Unexpected format:', data);
                        }                        
                        //setResultsSearch(images);
                    } else {

                        /*
                        console.log( data );
                        console.log( '================' );*/

                        // Maybe API connecting problem
                        images = (
                            <>
                            <p>
                                {__(
                                    'No results were found for your search. There may be an API issue with your credentials. Please check your credentials on the ',
                                    'mpt'
                                )}
                                <a href={mptAjax.admin_url + 'admin.php?page=magic-post-thumbnail-admin-display&module=source'} target='_blank'>
                                    {__('settings page', 'mpt')}
                                </a>.
                            </p>
                            </>
                          );                          
                        
                        setResultsSearch(prevResults => ({
                            ...prevResults,
                            [index]: images,
                        }));

                        //console.error('Error:', data.data);
                    }

                    // Hiding loader after search results */
                    if( loaderSearch ) {
                        loaderSearch.classList.add('hidden');
                    }

                    // Show again images tab
                    if (restultDiv) {
                        restultDiv.classList.remove('hidden');
                    }

                })
                .catch(error => {
                    console.error('Network Error:', error);
                });

        }

        function renderNoResults() {
            return (
                <>
                    <p>{__( 'No results found for your search.', 'mpt' )}</p>
                </>
            );
        }

        function renderImages(licensing, apiPath, imgPreview, altPath, captionPath, imgPath, bankGenerate, data) {

            const paths = getBankPaths(bankGenerate);
            const thumbnailPath = paths.thumbnailPath;

            let content = (
                <>
                    <ul className="media-grid">
                        {(licensing !== '1' ? data.data.results[apiPath].slice(0, 6) : data.data.results[apiPath]).map((image, index) => {
                            const urlThumbnail = thumbnailPath ? (thumbnailPath.reduce((o, k) => (o || {})[k], image) || null) : null;
                            return (
                            <li 
                                key={index}
                                className="attachment mpt-attachment"
                                tabIndex="0" 
                                role="checkbox" 
                                aria-label="Select image"
                                aria-checked="false"
                            >
                                <div className="attachment-preview js--select-attachment type-image subtype-jpeg landscape">
                                    <div className="thumbnail">
                                        <div className="centered">
                                            <img src={imgPreview.reduce((o, k) => (o || {})[k], image)} draggable="false" />
                                        </div>
                                        <div className="img-result">
                                        { 
                                            FeaturedImageDiv ? (
                                                <div type="button" className="components-button is-primary" onClick={() => handleSetFeaturedImage(imgPath.reduce((o, k) => (o || {})[k], image), altPath.reduce((o, k) => (o || {})[k], image), captionPath.reduce((o, k) => (o || {})[k], image), searchTerm, bankGenerate, urlThumbnail)}>
                                                    {__( 'Set as featured image', 'mpt' )}
                                                </div>
                                            ) : (
                                                <div type="button" className="components-button is-primary" onClick={() => handleUseImageClick(imgPath.reduce((o, k) => (o || {})[k], image), altPath.reduce((o, k) => (o || {})[k], image), captionPath.reduce((o, k) => (o || {})[k], image), searchTerm, bankGenerate, urlThumbnail)}>
                                                    {__( 'Use this image', 'mpt' )}
                                                </div>
                                            )
                                        }
                                        </div>
                                    </div>
                                </div>
                            </li>
                            );
                        })}
                    </ul>
                </>
            );
        
            if (licensing !== '1') {
                const shuffledIndexes = shuffleArray(Array.from({ length: 16 }, (_, index) => index));
        
                content = (
                    <>
                        {content}
                        <p class="unlock-pro">
                            <a href={mptAjax.admin_url+'admin.php?page=magic-post-thumbnail-admin-display-pricing'} target="_blank" title={__( 'PRO VERSION OF MAGIC POST THUMBNAIL', 'mpt' )}>
                                <img src={mptAjax.path_default_img + 'lock.png'} draggable="false" /> {__( 'UNLOCK MORE IMAGES WITH THE PRO VERSION', 'mpt' )}
                            </a>
                        </p>
                        <ul className="media-grid blue-grid">
                            {shuffledIndexes.map((shuffledIndex) => (
                                <li 
                                    key={shuffledIndex}
                                    className="attachment mpt-attachment"
                                    tabIndex={shuffledIndex}
                                >
                                    <div className="attachment-preview landscape">
                                        <div className="thumbnail">
                                            <div className="centered">
                                                <img src={mptAjax.path_default_img + `default-img/default-${shuffledIndex + 1}.jpg`} draggable="false" />
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </>
                );
            }
        
            return content;
        }
        
        
        function renderUnlockMessage() {
            const shuffledIndexes = shuffleArray(Array.from({ length: 16 }, (_, index) => index));
        
            return (
                <>
                    <p className="unlock-pro">
                        <a href={mptAjax.admin_url + 'admin.php?page=magic-post-thumbnail-admin-display-pricing'} target="_blank" title={__( 'PRO VERSION OF MAGIC POST THUMBNAIL', 'mpt' )}>
                            <img src={mptAjax.path_default_img + 'lock.png'} draggable="false" /> {__( 'UNLOCK MORE IMAGES WITH THE PRO VERSION', 'mpt' )}
                        </a>
                    </p>
                    <ul className="media-grid blue-grid">
                        {shuffledIndexes.map((shuffledIndex) => (
                            <li 
                                key={shuffledIndex}
                                className="attachment mpt-attachment"
                                tabIndex={shuffledIndex}
                            >
                                <div className="attachment-preview landscape">
                                    <div className="thumbnail">
                                        <div className="centered">
                                            <img src={mptAjax.path_default_img + `default-img/default-${shuffledIndex + 1}.jpg`} draggable="false" />
                                        </div>
                                    </div>
                                </div>
                            </li>
                        ))}
                    </ul>
                </>
            );
        }
        


        return (
            <div { ...useBlockProps() }>
                <BlockControls>
                    <AlignmentToolbar
                        value={ attributes.alignment }
                        onChange={ onChangeAlignment }
                    />
                </BlockControls>
                <div className="button-center">
                    <Button isPrimary onClick={() => setIsModalOpen(true)}>
                        {isModalOpen ? closeTheWindowText : searchForImagesText}
                    </Button>
                </div>
                {isModalOpen && (
                    <Modal
                        title={ 'Magic Post Thumbnail' }
                        onRequestClose={(event) => {

                            setIsModalOpen(false);

                            setFeaturedImageDiv(false);

                            // Check if this is featured Image Modal & the right Block Id
                            if ( FeaturedImageDiv && ( clientIDFeaturedImage == clientId ) ) {
                                dispatch('core/block-editor').removeBlock(clientId);
                            }
                            
                        }}
                        className="media-modal-content"
                    >
                        <div class="loader-mpt hidden" style={{ backgroundImage: `url(${background})` }}>
                            <p>{downlowdingMedia}</p>
                        </div>

                        <TextControl
                            value={searchTerm}
                            onChange={(value) => setSearchTerm(value)}
                            onKeyPress={(e) => {
                                if (e.key === 'Enter') {
                                    searchAllTabs();
                                }
                            }}
                        />
                        <Button
                            isPrimary
                            onClick={searchAllTabs}
                        >
                            {searchStr}
                        </Button>

                        <TabPanel
                            className="mpt-tab-panel"
                            activeClass="active-tab"
                            tabs={Object.entries(mptAjax.choosed_banks).map(([key, value], index) => ({
                                name: `tab${index}`,
                                title: value.charAt(0).toUpperCase() + value.slice(1),
                                className: `tab-${index}`,
                            }))}
                        >
                            {(tab) => (
                                <div>
                                    {Object.entries(mptAjax.choosed_banks).map(([key, value], index) => (
                                        <div key={index}>
                                            {tab.name === `tab${index}` && (
                                                <>
                                                    <div className={`results-search results-${index}`}>
                                                        <span class={`hidden loader-searching-${index}`} style={{ backgroundImage: `url(${background})` }}></span>
                                                        {resultsSearch[index]}
                                                    </div>
                                                </>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            )}
                        </TabPanel>

                    </Modal>
                )}
            </div>
        );
    },
    save: () => {
        return null;
    },
});