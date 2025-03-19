jQuery(document).ready(function($) {
    let searchTimeout;
    let spotifyToken = '';

    // Get Spotify token using client credentials flow
    function getSpotifyToken() {
        return $.ajax({
            url: 'https://accounts.spotify.com/api/token',
            type: 'POST',
            headers: {
                'Authorization': 'Basic ' + btoa(spotifyConfig.clientId + ':' + spotifyConfig.clientSecret)
            },
            data: {
                'grant_type': 'client_credentials'
            }
        }).then(function(data) {
            spotifyToken = data.access_token;
        });
    }

    function initializeSearch() {
        // Get a new token before initializing search
        getSpotifyToken().then(function() {
            const searchField = $('.spotify-search-field');
            const searchButton = $('.spotify-search-button');
            const searchResults = $('.spotify-search-results');
            const searchType = $('.elementor-control-search_type select');

            function performSearch() {
                const query = searchField.val().trim();
                const type = searchType.val();

                if (!query) {
                    searchResults.empty();
                    return;
                }

                searchResults.html('<div class="spotify-search-loading">' + spotifyConfig.i18n.searching + '</div>');

                // Construct the Spotify Web API search URL
                const searchUrl = `https://api.spotify.com/v1/search?q=${encodeURIComponent(query)}&type=${type}&limit=5`;

                // Make the request to Spotify Web API
                $.ajax({
                    url: searchUrl,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + spotifyToken
                    },
                    success: function(data) {
                        displaySearchResults(data, type);
                    },
                    error: function(xhr) {
                        if (xhr.status === 401) {
                            // Token expired, get a new one and retry
                            getSpotifyToken().then(performSearch);
                        } else {
                            searchResults.html('<div class="spotify-search-error">' + spotifyConfig.i18n.error + '</div>');
                        }
                    }
                });
            }

            function displaySearchResults(data, type) {
                const items = data[type + 's'].items;
                
                if (!items.length) {
                    searchResults.html('<div class="spotify-search-no-results">' + spotifyConfig.i18n.noResults + '</div>');
                    return;
                }

                let html = '<div class="spotify-search-list">';
                
                items.forEach(function(item) {
                    const image = item.images ? item.images[0] : (item.album ? item.album.images[0] : null);
                    const imageUrl = image ? image.url : '';
                    const title = item.name;
                    const subtitle = type === 'track' ? item.artists[0].name : 
                                   (type === 'album' ? item.artists[0].name : '');
                    const spotifyUrl = item.external_urls.spotify;

                    html += `
                        <div class="spotify-search-item" data-url="${spotifyUrl}">
                            ${imageUrl ? `
                                <div class="spotify-search-item-image">
                                    <img src="${imageUrl}" alt="${title}">
                                </div>
                            ` : ''}
                            <div class="spotify-search-item-info">
                                <div class="spotify-search-item-title">${title}</div>
                                ${subtitle ? `<div class="spotify-search-item-subtitle">${subtitle}</div>` : ''}
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                searchResults.html(html);

                // Handle result selection
                searchResults.find('.spotify-search-item').on('click', function() {
                    const spotifyUrl = $(this).data('url');
                    const urlInput = $('.elementor-control-spotify_url input');
                    
                    // Update the hidden URL control
                    urlInput.val(spotifyUrl).trigger('input');
                    
                    // Clear search results and field
                    searchResults.empty();
                    searchField.val('');
                    
                    // Trigger Elementor preview update
                    elementor.reloadPreview();
                });
            }

            // Handle search input with debounce
            searchField.on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 500);
            });

            // Handle search button click
            searchButton.on('click', performSearch);

            // Handle enter key
            searchField.on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    performSearch();
                }
            });

            // Handle content type change
            searchType.on('change', function() {
                if (searchField.val().trim()) {
                    performSearch();
                }
            });
        });
    }

    // Initialize search when editor panel is opened for this widget
    elementor.hooks.addAction('panel/open_editor/widget/spotify-embed', function() {
        setTimeout(initializeSearch, 100);
    });
}); 