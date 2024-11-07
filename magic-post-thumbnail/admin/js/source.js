jQuery(function($) {

    // Drag & Drop Banks
    $(".chosen_api .radio-list").sortable({
        stop: function() {
            $('.ordered-checkbox').each(function(index) {
                $(this).attr('data-order', index + 1);
            });
        }
    });
    $(".chosen_api .radio-list").disableSelection();

});






/* BANKS TESTING APIS */


// Pixabay
document.addEventListener("DOMContentLoaded", function() {
    const btnPixabay    = document.getElementById("btnPixabay");
    const resultPixabay = document.getElementById("resultPixabay");
    const apiKeyInput   = document.querySelector('input[name="MPT_plugin_banks_settings[pixabay][apikey]"]');
    const imagePixabay  = document.querySelector("#resultPixabay img");

    btnPixabay.addEventListener("click", function() {

        imagePixabay.classList.remove("hidden");

        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=test_apis&apibank=pixabay&nonce=" + apisTestingAjax.nonce + "&apikey=" + apiKeyInput.value
        })
        .then(response => response.json())
        .then(data => {
            const parsedData = JSON.parse(data.data);

            if (data.success && parsedData.hits && parsedData.hits.length > 0) {
                resultPixabay.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultPixabay.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })
        .catch(error => {
            resultPixabay.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + ' or ' + apisTestingAjax.error_testing + '</span>';
        });
    });
});





// Dall-e
document.addEventListener("DOMContentLoaded", function() {
    const btnDalle          = document.getElementById("btnDalle");
    const resultDalle       = document.getElementById("resultDalle");
    const apiKeyInputDalle  = document.querySelector('input[name="MPT_plugin_banks_settings[dallev1][apikey]"]');
    const imageDalle        = document.querySelector("#resultDalle img");

    btnDalle.addEventListener("click", function() {

        imageDalle.classList.remove("hidden");

        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=test_apis&apibank=dalle&nonce=" + apisTestingAjax.nonce + "&apikey=" + apiKeyInputDalle.value
        })
        .then(response => response.json())
        .then(data => {
            const parsedData = JSON.parse(data.data);
            if (data.success && parsedData.data && parsedData.data.length > 0) {
                resultDalle.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultDalle.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })
        .catch(error => {
            resultDalle.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});





// Stability AI
document.addEventListener("DOMContentLoaded", function() {
    const btnStability          = document.getElementById("btnStability");
    const resultStability       = document.getElementById("resultStability");
    const apiKeyInputStability  = document.querySelector('input[name="MPT_plugin_banks_settings[stability][apikey]"]');
    const imageStability        = document.querySelector("#resultStability img");

    btnStability.addEventListener("click", function() {

        imageStability.classList.remove("hidden");

        //  Sending the request to check the API Stability AI key
        fetch("https://api.stability.ai/v1/user/account", {
            method: "GET",
            headers: {
                "Authorization": "Bearer " + apiKeyInputStability.value
            }
        })
        .then(response => {
            if (response.status === 200) {
                resultStability.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else if (response.status === 401) {
                resultStability.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            } else {
                resultStability.innerHTML = '<span class="text-warning">Erreur inattendue : ' + response.status + '</span>';
            }
            return response.json();
        })
        .catch(error => {
            resultStability.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});




// Youtube
document.addEventListener("DOMContentLoaded", function() {
    const btnYoutube    = document.getElementById("btnYoutube");
    const resultYoutube = document.getElementById("resultYoutube");
    const apiKeyInput   = document.querySelector('input[name="MPT_plugin_banks_settings[youtube][apikey]"]');
    const imageYt       = document.querySelector("#resultYoutube img");

    btnYoutube.addEventListener("click", function() {

        imageYt.classList.remove("hidden");

        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=test_apis&apibank=youtube&nonce=" + apisTestingAjax.nonce + "&apikey=" + apiKeyInput.value
        })
        .then(response => response.json())
        .then(data => {
            const parsedData = JSON.parse(data.data);
            if (data.success && parsedData.items && parsedData.items.length > 0) {
                resultYoutube.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultYoutube.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })
        .catch(error => {
            resultYoutube.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});





// Unsplash
document.addEventListener("DOMContentLoaded", function() {
    const btnUnsplash       = document.getElementById("btnUnsplash");
    const resultUnsplash    = document.getElementById("resultUnsplash");
    const apiKeyInput       = document.querySelector('input[name="MPT_plugin_banks_settings[unsplash][apikey]"]');
    const imageUnsplash     = document.querySelector("#resultUnsplash img");

    btnUnsplash.addEventListener("click", function() {

        imageUnsplash.classList.remove("hidden");

        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=test_apis&apibank=unsplash&nonce=" + apisTestingAjax.nonce + "&apikey=" + apiKeyInput.value
        })
        .then(response => response.json())
        .then(data => {
            const parsedData = JSON.parse(data.data);
            if (data.success && parsedData.results && parsedData.results.length > 0) {
                resultUnsplash.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultUnsplash.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })
        .catch(error => {
            resultUnsplash.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});





// Pexels
document.addEventListener("DOMContentLoaded", function() {
    const btnPexels         = document.getElementById("btnPexels");
    const resultPexels      = document.getElementById("resultPexels");
    const apiKeyInput       = document.querySelector('input[name="MPT_plugin_banks_settings[pexels][apikey]"]');
    const imagePexels       = document.querySelector("#resultPexels img");

    btnPexels.addEventListener("click", function() {

        imagePexels.classList.remove("hidden");

        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=test_apis&apibank=pexels&nonce=" + apisTestingAjax.nonce + "&apikey=" + apiKeyInput.value
        })
        .then(response => response.json())
        .then(data => {
            const parsedData = JSON.parse(data.data);
            if (data.success && parsedData.photos && parsedData.photos.length > 0) {
                resultPexels.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultPexels.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })
        .catch(error => {
            resultPexels.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});





// Envato Elements
document.addEventListener("DOMContentLoaded", function() {
    const btnEnvato         = document.getElementById("btnEnvato");
    const resultEnvato      = document.getElementById("resultEnvato");
    const envatoTokenInput  = document.querySelector('input[name="MPT_plugin_banks_settings[envato][envato_token]"]');
    const imageEnvato       = document.querySelector("#resultEnvato img");

    btnEnvato.addEventListener("click", function() {

        imageEnvato.classList.remove("hidden");
        
        fetch(apisTestingAjax.ajaxurl, {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: "action=test_apis&apibank=envato&nonce=" + apisTestingAjax.nonce + "&apikey=" + envatoTokenInput.value
        })
        .then(response => response.json())
        .then(data => {
            const parsedData = JSON.parse(data.data);

            if (data.success && ( 'paid' === parsedData.subscription_status )  ) {
                resultEnvato.innerHTML = '<span class="text-success">' + apisTestingAjax.successful_testing + '</span>';
            } else {
                resultEnvato.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_key + '</span>';
            }
        })        
        .catch(error => {
            resultEnvato.innerHTML = '<span class="text-warning">' + apisTestingAjax.error_testing + '</span>';
        });
    });
});