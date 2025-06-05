jQuery(document).ready(function($) {
    let searchTimeout;
    let spotifyToken = '';
    let isSearching = false;

    // Get Spotify token using client credentials flow
    function getSpotifyToken() {
        console.log('Getting Spotify token...');
        console.log('Client ID:', spotifyConfig.clientId);
        console.log('Client Secret exists:', !!spotifyConfig.clientSecret);

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
            console.log('Token received successfully');
            spotifyToken = data.access_token;
            return spotifyToken;
        }).catch(function(error) {
            console.error('Failed to get Spotify token:', error);
            throw new Error('Authentication failed');
        });
    }

    function initializeSearch() {
        console.log('Initializing search...');
        
        // Get a new token before initializing search
        getSpotifyToken().then(function() {
            console.log('Token obtained, setting up search handlers');
            
            const searchField = $('.elementor-control-search_field input');
            const searchResults = $('.elementor-control-search_results .spotify-search-results');
            const searchType = $('.elementor-control-search_type select');

            console.log('Search elements found:', {
                searchField: searchField.length > 0,
                searchResults: searchResults.length > 0,
                searchType: searchType.length > 0
            });

            function performSearch() {
                if (isSearching) {
                    console.log('Search already in progress, skipping');
                    return;
                }

                const query = searchField.val().trim();
                const type = searchType.val();

                console.log('Performing search:', { query, type });

                if (!query) {
                    searchResults.empty();
                    return;
                }

                isSearching = true;
                searchField.prop('disabled', true);
                searchResults.html('<div class="spotify-search-loading">' + spotifyConfig.i18n.searching + '</div>');

                // Construct the Spotify Web API search URL
                const searchUrl = `https://api.spotify.com/v1/search?q=${encodeURIComponent(query)}&type=${type}&limit=5`;
                console.log('Search URL:', searchUrl);

                // Make the request to Spotify Web API
                $.ajax({
                    url: searchUrl,
                    type: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + spotifyToken
                    },
                    success: function(data) {
                        console.log('Search results received:', data);
                        displaySearchResults(data, type);
                    },
                    error: function(xhr) {
                        console.error('Search failed:', xhr);
                        if (xhr.status === 401) {
                            // Token expired, get a new one and retry
                            console.log('Token expired, getting new token');
                            getSpotifyToken().then(performSearch).catch(function() {
                                searchResults.html('<div class="spotify-search-error">' + spotifyConfig.i18n.error + '</div>');
                            });
                        } else {
                            searchResults.html('<div class="spotify-search-error">' + spotifyConfig.i18n.error + '</div>');
                        }
                    },
                    complete: function() {
                        isSearching = false;
                        searchField.prop('disabled', false);
                    }
                });
            }

            function displaySearchResults(data, type) {
                console.log('Displaying results for type:', type);
                const items = data[type + 's'].items;
                
                if (!items.length) {
                    console.log('No results found');
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
                    
                    console.log('Selected item:', spotifyUrl);
                    
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
                console.log('Search input changed');
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 500);
            });

            // Handle enter key
            searchField.on('keypress', function(e) {
                if (e.which === 13) {
                    console.log('Enter key pressed');
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    performSearch();
                }
            });

            // Handle content type change
            searchType.on('change', function() {
                console.log('Content type changed:', $(this).val());
                if (searchField.val().trim()) {
                    clearTimeout(searchTimeout);
                    performSearch();
                }
            });
        }).catch(function(error) {
            console.error('Failed to initialize search:', error);
            const searchResults = $('.elementor-control-search_results .spotify-search-results');
            searchResults.html('<div class="spotify-search-error">' + spotifyConfig.i18n.error + '</div>');
        });
    }

    // Initialize search when editor panel is opened for this widget
    elementor.hooks.addAction('panel/open_editor/widget/spotify-embed', function() {
        console.log('Widget panel opened');
        setTimeout(initializeSearch, 100);
    });
}); 