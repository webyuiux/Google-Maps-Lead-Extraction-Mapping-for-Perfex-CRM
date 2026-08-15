let map, service, geocoder, infowindow;
let markers = [], extracted = [], nextPage = null;
let adminUrl, csrfTokenName, csrfTokenValue;

function initMap() {
    var mapEl = document.getElementById("map");
    if (!mapEl || typeof google === 'undefined') {
        return;
    }

    const defaultLoc = { lat: 18.5204, lng: 73.8567 };
    
    map = new google.maps.Map(mapEl, {
        center: defaultLoc,
        zoom: 12,
        styles: [
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [{ visibility: "off" }]
            }
        ]
    });
    
    geocoder = new google.maps.Geocoder();
    service = new google.maps.places.PlacesService(map);
    infowindow = new google.maps.InfoWindow();
    
    var searchBtn = document.getElementById("searchBtn");
    if (searchBtn) searchBtn.addEventListener("click", runSearch);
    
    var locateBtn = document.getElementById("locateBtn");
    if (locateBtn) locateBtn.addEventListener("click", useCurrentLocation);
    
    var exportExcelBtn = document.getElementById("exportExcelBtn");
    if (exportExcelBtn) exportExcelBtn.addEventListener("click", exportExcel);
    
    var loadMoreBtn = document.getElementById("loadMoreBtn");
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener("click", function() {
            if (nextPage) nextPage();
        });
    }
    
    var clearResultsBtn = document.getElementById("clearResultsBtn");
    if (clearResultsBtn) {
        clearResultsBtn.addEventListener("click", function() {
            if (confirm('Are you sure you want to clear current search results?')) {
                clearAll();
            }
        });
    }
    
    var saveAllBtn = document.getElementById("saveAllBtn");
    if (saveAllBtn) {
        saveAllBtn.addEventListener("click", saveAllBusinesses);
    }
    
    var locationInput = document.getElementById('locationInput');
    if (locationInput && locationInput.value) {
        geocoder.geocode({ address: locationInput.value }, function(results, status) {
            if (status === "OK" && results[0]) {
                map.setCenter(results[0].geometry.location);
            }
        });
    }
    
    // Auto-run search if keyword is pre-filled from URL
    var keywordInput = document.getElementById('keyword');
    if (keywordInput && keywordInput.value && window.location.search.indexOf('keyword=') !== -1) {
        // slight delay to allow map to initialize properly
        setTimeout(function() {
            if (typeof runSearch === 'function') runSearch();
        }, 500);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var auEl = document.getElementById('admin_url');
    if (auEl) adminUrl = auEl.value;
    
    var ctnEl = document.getElementById('csrf_token_name');
    if (ctnEl) csrfTokenName = ctnEl.value;
    
    var ctvEl = document.getElementById('csrf_token_value');
    if (ctvEl) csrfTokenValue = ctvEl.value;
    
    var exportSavedExcelBtn = document.getElementById("exportSavedExcelBtn");
    if (exportSavedExcelBtn) {
        exportSavedExcelBtn.addEventListener("click", exportSavedBusinessesToExcel);
    }
    
    var selectAllExport = document.getElementById("selectAllExport");
    if (selectAllExport) {
        selectAllExport.addEventListener("change", function() {
            document.querySelectorAll('.export-checkbox').forEach(function(cb) {
                cb.checked = selectAllExport.checked;
            });
        });
    }
    
    // Uncheck "Select All" when results are cleared
    var clearResultsBtn = document.getElementById("clearResultsBtn");
    if (clearResultsBtn) {
        clearResultsBtn.addEventListener("click", function() {
            if (selectAllExport) selectAllExport.checked = false;
        });
    }
    
    initRecentSearchesHandlers();
    initSavedBusinessesHandlers();
});

function initRecentSearchesHandlers() {
    document.querySelectorAll('.use-search').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var kwEl = document.getElementById('keyword');
            var locEl = document.getElementById('locationInput');
            if (kwEl && locEl) {
                kwEl.value = this.dataset.keyword;
                locEl.value = this.dataset.location;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    });
    
    document.querySelectorAll('.delete-search').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            deleteSearch(this.dataset.id);
        });
    });
    
    var selectAllSearches = document.getElementById('selectAllSearches');
    if (selectAllSearches) {
        selectAllSearches.addEventListener('change', function() {
            document.querySelectorAll('.search-checkbox').forEach(function(cb) {
                cb.checked = selectAllSearches.checked;
            });
            updateSearchBulkActions();
        });
    }
    
    document.querySelectorAll('.search-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updateSearchBulkActions);
    });
    
    var clearAllSearches = document.getElementById('clearAllSearches');
    if (clearAllSearches) {
        clearAllSearches.addEventListener('click', function() {
            if (confirm('Are you sure you want to clear all search history?')) {
                clearAllSearchHistory();
            }
        });
    }
    
    var deleteSelectedSearches = document.getElementById('deleteSelectedSearches');
    if (deleteSelectedSearches) {
        deleteSelectedSearches.addEventListener('click', function() {
            var selected = getSelectedSearchIds();
            if (selected.length && confirm('Delete ' + selected.length + ' selected search(es)?')) {
                deleteMultipleSearches(selected);
            }
        });
    }
}

function initSavedBusinessesHandlers() {
    // Use event delegation for buttons that might be inside a DataTable
    document.addEventListener('click', function(e) {
        var convertBtn = e.target.closest('.convert-to-lead');
        if (convertBtn) {
            e.preventDefault();
            convertToLead(convertBtn.dataset.id);
            return;
        }
        
        var deleteBtn = e.target.closest('.delete-business');
        if (deleteBtn) {
            e.preventDefault();
            deleteBusiness(deleteBtn.dataset.id);
            return;
        }
    });
    
    var selectAllBusinesses = document.getElementById('selectAllBusinesses');
    if (selectAllBusinesses) {
        selectAllBusinesses.addEventListener('change', function() {
            document.querySelectorAll('.business-checkbox').forEach(function(cb) {
                cb.checked = selectAllBusinesses.checked;
            });
            updateBusinessBulkActions();
        });
    }
    
    document.querySelectorAll('.business-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updateBusinessBulkActions);
    });
    
    var clearAllBusinesses = document.getElementById('clearAllBusinesses');
    if (clearAllBusinesses) {
        clearAllBusinesses.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete all saved businesses?')) {
                clearAllSavedBusinesses();
            }
        });
    }
    
    var deleteSelectedBusinesses = document.getElementById('deleteSelectedBusinesses');
    if (deleteSelectedBusinesses) {
        deleteSelectedBusinesses.addEventListener('click', function() {
            var selected = getSelectedBusinessIds();
            if (selected.length && confirm('Delete ' + selected.length + ' selected business(es)?')) {
                deleteMultipleBusinesses(selected);
            }
        });
    }
    
    var convertSelectedToLeads = document.getElementById('convertSelectedToLeads');
    if (convertSelectedToLeads) {
        convertSelectedToLeads.addEventListener('click', function() {
            var selected = getSelectedBusinessIds();
            if (selected.length && confirm('Convert ' + selected.length + ' business(es) to leads?')) {
                convertMultipleToLeads(selected);
            }
        });
    }
}

function updateSearchBulkActions() {
    var selected = getSelectedSearchIds();
    var bulkActions = document.getElementById('searchBulkActions');
    var countEl = document.getElementById('selectedSearchCount');
    
    if (bulkActions) {
        bulkActions.style.display = selected.length > 0 ? 'flex' : 'none';
    }
    if (countEl) {
        countEl.textContent = selected.length;
    }
}

function updateBusinessBulkActions() {
    var selected = getSelectedBusinessIds();
    var bulkActions = document.getElementById('businessBulkActions');
    var countEl = document.getElementById('selectedBusinessCount');
    
    if (bulkActions) {
        bulkActions.style.display = selected.length > 0 ? 'flex' : 'none';
    }
    if (countEl) {
        countEl.textContent = selected.length;
    }
}

function getSelectedSearchIds() {
    var ids = [];
    document.querySelectorAll('.search-checkbox:checked').forEach(function(cb) {
        ids.push(cb.value);
    });
    return ids;
}

function getSelectedBusinessIds() {
    var ids = [];
    document.querySelectorAll('.business-checkbox:checked').forEach(function(cb) {
        ids.push(cb.value);
    });
    return ids;
}

function runSearch() {
    clearAll();
    
    var keyword = document.getElementById("keyword").value.trim();
    var location = document.getElementById("locationInput").value.trim();
    var radius = parseInt(document.getElementById("searchRadius").value);
    
    if (!keyword) {
        alert("Please enter a business type");
        return;
    }
    
    if (!location) {
        alert("Please enter a location or use current location");
        return;
    }
    
    showLoading();
    
    geocoder.geocode({ address: location }, function(results, status) {
        if (status === "OK" && results[0]) {
            var loc = results[0].geometry.location;
            map.setCenter(loc);
            map.setZoom(14);
            
            service.nearbySearch({
                location: loc,
                radius: radius,
                keyword: keyword
            }, processResults);
            
            saveSearchHistory(keyword, location, radius);
        } else {
            hideLoading();
            alert("Location not found. Please try a different location.");
        }
    });
}

function processResults(results, status, pagination) {
    hideLoading();
    
    if (status === "OK" && results.length) {
        results.forEach(function(result) {
            createMarker(result);
            fetchDetails(result.place_id);
        });
        
        if (pagination && pagination.hasNextPage) {
            nextPage = function() {
                showLoading();
                pagination.nextPage();
            };
            document.getElementById("loadMoreBtn").style.display = "inline-block";
        } else {
            nextPage = null;
            document.getElementById("loadMoreBtn").style.display = "none";
        }
        
        document.getElementById("saveAllBtn").style.display = "inline-block";
        var clearResultsBtn = document.getElementById("clearResultsBtn");
        if (clearResultsBtn) {
            clearResultsBtn.style.display = "inline-block";
        }
    } else {
        updateCount();
    }
}

function fetchDetails(placeId) {
    service.getDetails({
        placeId: placeId,
        fields: ['name', 'formatted_address', 'formatted_phone_number', 'website', 'rating', 'user_ratings_total', 'geometry', 'photos', 'place_id']
    }, function(place, status) {
        if (status === "OK" && place) {
            var business = {
                name: place.name || "",
                address: place.formatted_address || "",
                phone: place.formatted_phone_number || "",
                website: place.website || "",
                rating: place.rating || "",
                total_reviews: place.user_ratings_total || 0,
                place_id: place.place_id,
                latitude: place.geometry ? place.geometry.location.lat() : null,
                longitude: place.geometry ? place.geometry.location.lng() : null,
                photo_url: place.photos && place.photos[0] ? place.photos[0].getUrl({ maxWidth: 200 }) : "",
                business_type: document.getElementById("keyword").value,
                search_location: document.getElementById("locationInput").value
            };
            
            extracted.push(business);
            addResultCard(place, business);
            addPreviewRow(business);
            updateCount();
        }
    });
}

function createMarker(place) {
    if (!place.geometry) return;
    
    var marker = new google.maps.Marker({
        map: map,
        position: place.geometry.location,
        title: place.name,
        animation: google.maps.Animation.DROP
    });
    
    marker.addListener("click", function() {
        var content = '<div style="max-width:200px;">' +
            '<strong>' + place.name + '</strong>' +
            (place.vicinity ? '<br><small>' + place.vicinity + '</small>' : '') +
            '</div>';
        infowindow.setContent(content);
        infowindow.open(map, marker);
    });
    
    markers.push(marker);
}

function addResultCard(place, business) {
    var div = document.createElement("div");
    div.className = "result-card";
    div.dataset.placeId = business.place_id;
    
    var imgHtml = '';
    if (place.photos && place.photos[0]) {
        imgHtml = '<img src="' + place.photos[0].getUrl({ maxWidth: 200 }) + '" alt="' + escapeHtml(place.name) + '">';
    } else {
        imgHtml = '<div class="no-image"><i class="fa fa-building"></i></div>';
    }
    
    var stars = business.rating ? generateStars(Math.round(business.rating)) : 'N/A';
    
    div.innerHTML = imgHtml +
        '<div class="result-meta">' +
            '<h4>' + escapeHtml(place.name) + '</h4>' +
            '<div class="addr"><i class="fa fa-map-marker"></i> ' + escapeHtml(place.formatted_address || 'N/A') + '</div>' +
            '<div class="phone"><i class="fa fa-phone"></i> ' + escapeHtml(place.formatted_phone_number || 'N/A') + '</div>' +
            '<div class="rating"><span class="stars">' + stars + '</span> ' + (business.rating || 'N/A') + ' (' + business.total_reviews + ' reviews)</div>' +
        '</div>' +
        '<div class="result-actions">' +
            '<button class="btn-save-business" data-place-id="' + business.place_id + '" onclick="saveSingleBusiness(\'' + business.place_id + '\')"><i class="fa fa-save"></i></button>' +
        '</div>';
    
    div.addEventListener('click', function(e) {
        if (e.target.closest('.btn-save-business')) return;
        
        document.querySelectorAll('.result-card').forEach(function(card) {
            card.classList.remove('selected');
        });
        div.classList.add('selected');
        
        if (business.latitude && business.longitude) {
            map.panTo({ lat: business.latitude, lng: business.longitude });
            map.setZoom(17);
        }
    });
    
    document.getElementById("resultsList").appendChild(div);
}

function generateStars(rating) {
    var filled = '';
    var empty = '';
    for (var i = 0; i < rating; i++) {
        filled += '\u2605';
    }
    for (var j = rating; j < 5; j++) {
        empty += '\u2606';
    }
    return filled + empty;
}

function addPreviewRow(business) {
    var tr = document.createElement("tr");
    tr.innerHTML = 
        '<td class="text-center"><input type="checkbox" class="export-checkbox" value="' + escapeHtml(business.place_id) + '"></td>' +
        '<td>' + escapeHtml(business.name) + '</td>' +
        '<td class="hidden-xs">' + escapeHtml(business.address) + '</td>' +
        '<td>' + escapeHtml(business.phone) + '</td>' +
        '<td class="hidden-xs hidden-sm">' + (business.website ? '<a href="' + escapeHtml(business.website) + '" target="_blank">Visit</a>' : 'N/A') + '</td>' +
        '<td><span class="stars">' + (business.rating ? '\u2605 ' + business.rating : 'N/A') + '</span></td>' +
        '<td><button class="btn btn-success btn-xs" onclick="saveSingleBusiness(\'' + business.place_id + '\')"><i class="fa fa-save"></i></button></td>';
    
    document.querySelector("#previewTable tbody").appendChild(tr);
}

function updateCount() {
    document.getElementById("resultCount").innerText = extracted.length + " results";
}

function clearAll() {
    document.getElementById("resultsList").innerHTML = "";
    document.querySelector("#previewTable tbody").innerHTML = "";
    extracted = [];
    markers.forEach(function(marker) {
        marker.setMap(null);
    });
    markers = [];
    nextPage = null;
    document.getElementById("loadMoreBtn").style.display = "none";
    document.getElementById("saveAllBtn").style.display = "none";
    var clearResultsBtn = document.getElementById("clearResultsBtn");
    if (clearResultsBtn) {
        clearResultsBtn.style.display = "none";
    }
    updateCount();
}

function useCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(pos) {
            var lat = pos.coords.latitude;
            var lng = pos.coords.longitude;
            document.getElementById("locationInput").value = lat + "," + lng;
            map.setCenter({ lat: lat, lng: lng });
            map.setZoom(14);
        }, function(error) {
            alert("Unable to get your location. Please enter it manually.");
        });
    } else {
        alert("Geolocation is not supported by your browser.");
    }
}

function exportExcel() {
    if (!extracted.length) {
        alert("No data to export. Please search for businesses first.");
        return;
    }
    
    var selectedIds = [];
    document.querySelectorAll('.export-checkbox:checked').forEach(function(cb) {
        selectedIds.push(cb.value);
    });

    if (selectedIds.length === 0) {
        alert("No records selected for export. Please check at least one checkbox.");
        return;
    }
    
    var dataToExport = extracted.filter(function(item) {
        return selectedIds.includes(item.place_id);
    });
    
    var exportData = dataToExport.map(function(item) {
        return {
            'Name': item.name,
            'Address': item.address,
            'Phone': item.phone,
            'Website': item.website,
            'Rating': item.rating,
            'Reviews': item.total_reviews,
            'Business Type': item.business_type,
            'Search Location': item.search_location
        };
    });
    
    var ws = XLSX.utils.json_to_sheet(exportData);
    var wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Businesses");
    XLSX.writeFile(wb, "google_maps_businesses_" + formatDate(new Date()) + ".xlsx");
}

function exportSavedBusinessesToExcel() {
    showLoading();
    
    fetch(adminUrl + 'google_maps_extractor/export_businesses', {
        method: 'GET'
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        hideLoading();
        
        if (!data || data.length === 0) {
            alert("No saved businesses to export.");
            return;
        }
        
        var exportData = data.map(function(item) {
            return {
                'Name': item.name,
                'Address': item.address,
                'Phone': item.phone,
                'Website': item.website,
                'Rating': item.rating,
                'Reviews': item.total_reviews,
                'Business Type': item.business_type,
                'Search Location': item.search_location,
                'Extracted At': item.extracted_at,
                'Converted to Lead': item.is_converted_to_lead == 1 ? 'Yes' : 'No'
            };
        });
        
        var ws = XLSX.utils.json_to_sheet(exportData);
        var wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, "Saved Businesses");
        XLSX.writeFile(wb, "saved_businesses_" + formatDate(new Date()) + ".xlsx");
        
        if (typeof alert_float === 'function') {
            alert_float('success', 'Exported ' + data.length + ' businesses to Excel');
        }
    })
    .catch(function(error) {
        hideLoading();
        console.error('Error:', error);
        alert("Failed to export businesses. Please try again.");
    });
}

function formatDate(date) {
    var year = date.getFullYear();
    var month = String(date.getMonth() + 1).padStart(2, '0');
    var day = String(date.getDate()).padStart(2, '0');
    return year + month + day;
}

function saveSingleBusiness(placeId) {
    var business = extracted.find(function(b) {
        return b.place_id === placeId;
    });
    
    if (!business) return;
    
    var formData = new FormData();
    formData.append(csrfTokenName, csrfTokenValue);
    
    Object.keys(business).forEach(function(key) {
        formData.append(key, business[key] || '');
    });
    
    fetch(adminUrl + 'google_maps_extractor/save_business', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            var btn = document.querySelector('.btn-save-business[data-place-id="' + placeId + '"]');
            if (btn) {
                btn.classList.add('saved');
                btn.innerHTML = '<i class="fa fa-check"></i>';
                btn.disabled = true;
            }
            if (typeof alert_float === 'function') {
                alert_float('success', data.message);
            }
            
            if (data.business && typeof appendBusinessToTable === 'function') {
                appendBusinessToTable(data.business, data.can_convert, data.can_delete);
            }
            
            if (typeof leadsTable !== 'undefined' && leadsTable && leadsTable.ajax) {
                leadsTable.ajax.reload(null, false);
            } else if (typeof $ !== 'undefined' && $.fn.DataTable && $.fn.DataTable.isDataTable('#leads-table')) {
                $('#leads-table').DataTable().ajax.reload(null, false);
            }
        } else {
            if (typeof alert_float === 'function') {
                alert_float('danger', data.message);
            }
        }
    })
    .catch(function(error) {
        console.error('Error:', error);
        if (typeof alert_float === 'function') {
            alert_float('danger', 'Failed to save business');
        }
    });
}

function saveAllBusinesses() {
    if (!extracted.length) return;
    
    showLoading();
    
    var saved = 0;
    var errors = 0;
    var total = extracted.length;
    
    extracted.forEach(function(business, index) {
        var formData = new FormData();
        formData.append(csrfTokenName, csrfTokenValue);
        
        Object.keys(business).forEach(function(key) {
            formData.append(key, business[key] || '');
        });
        
        fetch(adminUrl + 'google_maps_extractor/save_business', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success) {
                saved++;
            } else {
                errors++;
            }
            
            if (saved + errors === total) {
                hideLoading();
                if (typeof alert_float === 'function') {
                    alert_float('success', 'Saved ' + saved + ' businesses');
                    if (errors > 0) {
                        alert_float('warning', errors + ' businesses could not be saved');
                    }
                }
            }
        })
        .catch(function() {
            errors++;
            if (saved + errors === total) {
                hideLoading();
            }
        });
    });
}

function appendBusinessToTable(b, canConvert, canDelete) {
    var tbody = document.querySelector('#savedBusinessesTable tbody');
    if (!tbody) return;
    
    if (tbody.querySelector('tr[data-id="' + b.id + '"]')) return;

    var tr = document.createElement('tr');
    tr.dataset.id = b.id;

    var html = '<td class="td-checkbox"><input type="checkbox" class="business-checkbox" value="' + b.id + '"></td>';
    html += '<td>' + escapeHtml(b.name || '') + '</td>';
    html += '<td class="hidden-xs">' + escapeHtml(b.address || '') + '</td>';
    
    html += '<td>';
    if (b.phone) {
        html += '<a href="tel:' + escapeHtml(b.phone) + '" class="phone-link">' + escapeHtml(b.phone) + '</a>';
    }
    html += '</td>';

    html += '<td class="hidden-xs hidden-sm">';
    if (b.website) {
        html += '<a href="' + escapeHtml(b.website) + '" target="_blank" class="website-link"><i class="fa fa-external-link"></i></a>';
    }
    html += '</td>';

    html += '<td class="hidden-xs">' + (b.rating || '') + '</td>';
    html += '<td>';
    if (canConvert) {
        html += '<a href="#" class="label label-default convert-to-lead" data-id="' + b.id + '" title="Convert to Lead">Not Converted</a>';
    } else {
        html += '<span class="label label-default">Not Converted</span>';
    }
    html += '</td>';

    html += '<td class="action-buttons">';
    if (canConvert) {
        html += '<button class="btn btn-success btn-xs convert-to-lead" data-id="' + b.id + '" title="Convert to Lead"><i class="fa fa-exchange"></i></button> ';
    }
    if (canDelete) {
        html += '<button class="btn btn-danger btn-xs delete-business" data-id="' + b.id + '" title="Delete"><i class="fa fa-trash"></i></button>';
    }
    html += '</td>';

    tr.innerHTML = html;

    var checkbox = tr.querySelector('.business-checkbox');
    if (checkbox) {
        checkbox.addEventListener('change', updateBusinessBulkActions);
    }

    tbody.insertBefore(tr, tbody.firstChild);
}

function saveSearchHistory(keyword, location, radius) {
    var formData = new FormData();
    formData.append(csrfTokenName, csrfTokenValue);
    formData.append('keyword', keyword);
    formData.append('location', location);
    formData.append('radius', radius);
    formData.append('results_count', 0);
    
    fetch(adminUrl + 'google_maps_extractor/save_search', {
        method: 'POST',
        body: formData
    });
}

function deleteSearch(id) {
    if (!confirm('Delete this search from history?')) return;
    
    var formData = new FormData();
    formData.append(csrfTokenName, csrfTokenValue);
    
    fetch(adminUrl + 'google_maps_extractor/delete_search/' + id, {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        if (data.success) {
            if (typeof alert_float === 'function') {
                alert_float('success', data.message);
            }
            location.reload();
        } else {
            if (typeof alert_float === 'function') {
                alert_float('danger', data.message);
            }
        }
    });
}

function deleteMultipleSearches(ids) {
    showLoading();
    
    var formData = new FormData();
    formData.append(csrfTokenName, csrfTokenValue);
    formData.append('ids', JSON.stringify(ids));
    
    fetch(adminUrl + 'google_maps_extractor/delete_searches', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        hideLoading();
        if (data.success) {
            if (typeof alert_float === 'function') {
                alert_float('success', data.message);
            }
            location.reload();
        } else {
            if (typeof alert_float === 'function') {
                alert_float('danger', data.message);
            }
        }
    })
    .catch(function() {
        hideLoading();
        if (typeof alert_float === 'function') {
            alert_float('danger', 'An error occurred');
        }
    });
}

function clearAllSearchHistory() {
    showLoading();
    
    var formData = new FormData();
    formData.append(csrfTokenName, csrfTokenValue);
    
    fetch(adminUrl + 'google_maps_extractor/clear_searches', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        hideLoading();
        if (data.success) {
            if (typeof alert_float === 'function') {
                alert_float('success', data.message);
            }
            location.reload();
        } else {
            if (typeof alert_float === 'function') {
                alert_float('danger', data.message);
            }
        }
    })
    .catch(function() {
        hideLoading();
        if (typeof alert_float === 'function') {
            alert_float('danger', 'An error occurred');
        }
    });
}

function convertToLead(id) {
    if (!confirm('Convert this business to a lead?')) return;
    
    showLoading();
    
    var formData = new FormData();
    formData.append(csrfTokenName, csrfTokenValue);
    
    fetch(adminUrl + 'google_maps_extractor/convert_to_lead/' + id, {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        hideLoading();
        if (data.success) {
            if (typeof alert_float === 'function') {
                alert_float('success', data.message);
            }
            location.reload();
        } else {
            if (typeof alert_float === 'function') {
                alert_float('danger', data.message);
            }
        }
    })
    .catch(function() {
        hideLoading();
        if (typeof alert_float === 'function') {
            alert_float('danger', 'An error occurred');
        }
    });
}

function convertMultipleToLeads(ids) {
    showLoading();
    
    var formData = new FormData();
    formData.append(csrfTokenName, csrfTokenValue);
    formData.append('ids', JSON.stringify(ids));
    
    fetch(adminUrl + 'google_maps_extractor/convert_multiple_to_leads', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        hideLoading();
        if (data.success) {
            if (typeof alert_float === 'function') {
                alert_float('success', data.message);
            }
            location.reload();
        } else {
            if (typeof alert_float === 'function') {
                alert_float('danger', data.message);
            }
        }
    })
    .catch(function() {
        hideLoading();
        if (typeof alert_float === 'function') {
            alert_float('danger', 'An error occurred');
        }
    });
}

function deleteBusiness(id) {
    if (!confirm('Are you sure you want to delete this business?')) return;
    
    showLoading();
    
    var formData = new FormData();
    formData.append(csrfTokenName, csrfTokenValue);
    
    fetch(adminUrl + 'google_maps_extractor/delete_business/' + id, {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        hideLoading();
        if (data.success) {
            if (typeof alert_float === 'function') {
                alert_float('success', data.message);
            }
            location.reload();
        } else {
            if (typeof alert_float === 'function') {
                alert_float('danger', data.message);
            }
        }
    })
    .catch(function() {
        hideLoading();
        if (typeof alert_float === 'function') {
            alert_float('danger', 'An error occurred');
        }
    });
}

function deleteMultipleBusinesses(ids) {
    showLoading();
    
    var formData = new FormData();
    formData.append(csrfTokenName, csrfTokenValue);
    formData.append('ids', JSON.stringify(ids));
    
    fetch(adminUrl + 'google_maps_extractor/delete_businesses', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        hideLoading();
        if (data.success) {
            if (typeof alert_float === 'function') {
                alert_float('success', data.message);
            }
            location.reload();
        } else {
            if (typeof alert_float === 'function') {
                alert_float('danger', data.message);
            }
        }
    })
    .catch(function() {
        hideLoading();
        if (typeof alert_float === 'function') {
            alert_float('danger', 'An error occurred');
        }
    });
}

function clearAllSavedBusinesses() {
    showLoading();
    
    var formData = new FormData();
    formData.append(csrfTokenName, csrfTokenValue);
    
    fetch(adminUrl + 'google_maps_extractor/clear_businesses', {
        method: 'POST',
        body: formData
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        hideLoading();
        if (data.success) {
            if (typeof alert_float === 'function') {
                alert_float('success', data.message);
            }
            location.reload();
        } else {
            if (typeof alert_float === 'function') {
                alert_float('danger', data.message);
            }
        }
    })
    .catch(function() {
        hideLoading();
        if (typeof alert_float === 'function') {
            alert_float('danger', 'An error occurred');
        }
    });
}

function showLoading() {
    var overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
    } else {
        var newOverlay = document.createElement('div');
        newOverlay.id = 'loadingOverlay';
        newOverlay.className = 'loading-overlay';
        newOverlay.innerHTML = '<div class="loading-spinner"></div>';
        document.body.appendChild(newOverlay);
    }
}

function hideLoading() {
    var overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

function escapeHtml(text) {
    if (!text) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(text));
    return div.innerHTML;
}
