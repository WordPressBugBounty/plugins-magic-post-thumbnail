// getBankPaths.js
const getBankPaths = (bank) => {
    let apiPath, altPath, captionPath, imgPath, imgPreview, is_premium, thumbnailPath = null;

    const bankLower = bank.toLowerCase();

    if(bankLower === 'pixabay') { // Pixabay
        apiPath     = 'hits';
        altPath     = ['tags'];
        captionPath = ['user'];
        imgPath     = ['largeImageURL'];
        imgPreview  = ['webformatURL'];
        is_premium  = false;
    } else if(bankLower === 'unsplash') { // Unsplash
        apiPath     = 'results';
        altPath     = ['alt_description'];
        captionPath = ['user', 'name'];
        imgPath     = ['urls', 'regular']; 
        imgPreview  = ['urls', 'small'];
        is_premium  = true;
    } else if(bankLower === 'pexels') { // Pexels
        apiPath     = 'photos';
        altPath     = ['alt'];
        captionPath = ['photographer'];
        imgPath     = ['src', 'large2x'];
        imgPreview  = ['src', 'tiny'];
        is_premium  = true;
    } else if(bankLower === 'openverse' || bankLower === 'cc_search') { // Openverse / CC Search (same API)
        apiPath     = 'results';
        altPath     = ['title'];
        captionPath = ['creator'];
        imgPath     = ['url'];
        imgPreview  = ['url'];
        is_premium  = false;
        // thumbnail as fallback when full-size url returns 404 (e.g. heavy images blocked by host)
        thumbnailPath = ['thumbnail'];
    } else if(bankLower === 'youtube') { // Youtube Thumbs
        apiPath     = 'items';
        altPath     = ['snippet', 'title'];
        captionPath = [];
        imgPath     = ['snippet', 'thumbnails', 'high', 'url'];
        imgPreview  = ['snippet', 'thumbnails', 'medium', 'url'];
        is_premium  = true;
    } else if(bankLower === 'flickr') { // Flickr
        apiPath     = 'photos';
        altPath     = [];
        captionPath = [];
        imgPath     = ['url'];
        imgPreview  = ['url'];
        is_premium  = false;
    } /* else if(bankLower === 'envato') { // Envato Elements - DISABLED (no longer working)
        apiPath     = ['items'];
        altPath     = ['description'];
        captionPath = ['contributor_username'];
        imgPath     = ['humane_id'];
        imgPreview  = ['cover_image_urls', 'tn316x211'];
        is_premium  = true;
    } */ else { // other ?
        apiPath     = 'hits';
        captionPath = ['caption'];
        imgPath     = ['largeImageURL'];
        imgPreview  = ['webformatURL'];
        is_premium  = true;
    }

    return { apiPath, altPath, captionPath, imgPath, imgPreview, is_premium, thumbnailPath };
};

export default getBankPaths;
