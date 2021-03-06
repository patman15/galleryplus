/* global OC, $, _, Gallery, SlideShow */
$(document).ready(function () {
	"use strict";
	Gallery.hideSearch();
	Gallery.utility = new Gallery.Utility();
	Gallery.view = new Gallery.View();
	Gallery.token = Gallery.utility.getRequestToken();
	Gallery.ieVersion = Gallery.utility.getIeVersion();

	// The first thing to do is to detect if we're on IE
	if (Gallery.ieVersion === 'unsupportedIe') {
		Gallery.utility.showIeWarning(Gallery.ieVersion);
		Gallery.showEmpty();
	} else {
		if (Gallery.ieVersion === 'oldIe') {
			Gallery.utility.showIeWarning(Gallery.ieVersion);
		}

		// Needed to centre the spinner in some browsers
		Gallery.resetContentHeight();

		// Get the config, the files and initialise the slideshow
		Gallery.showLoading();
		$.getJSON(Gallery.utility.buildGalleryUrl('config', '', {}))
			.then(function (config) {
				Gallery.config = new Gallery.Config(config);
				var currentLocation = window.location.href.split('#')[1] || '';
				Gallery.getFiles(currentLocation).then(function () {
					Gallery.activeSlideShow = new SlideShow();
					$.when(
							Gallery.activeSlideShow.init(
								false,
								null,
								Gallery.config.galleryFeatures
							))
						.then(function () {
							window.onhashchange();
						});

				});
			});

		$(document).click(function () {
			$('.album-info-container').slideUp();
		});

		// This block loads new rows
		$('html, #content-wrapper').scroll(function () {
			Gallery.view.loadVisibleRows(Gallery.albumMap[Gallery.currentAlbum],
				Gallery.currentAlbum);
		});


		var windowWidth = $(window).width();
		var windowHeight = $(window).height();
		$(window).resize(_.throttle(function () {
			var infoContentContainer = $('.album-info-container');
			// This section redraws the photowall and limits the width of dropdowns
			if (windowWidth !== $(window).width()) {
				if ($('#emptycontent').is(':hidden')) {
					Gallery.view.viewAlbum(Gallery.currentAlbum);
				}
				Gallery.view.breadcrumb.setMaxWidth($(window).width() - Gallery.buttonsWidth);
				infoContentContainer.css('max-width', $(window).width());

				windowWidth = $(window).width();
			}
			// This makes sure dropdowns will not be hidden after a window resize
			if (windowHeight !== $(window).height()) {
				Gallery.resetContentHeight();
				infoContentContainer.css('max-height',
					$(window).height() - Gallery.browserToolbarHeight);

				windowHeight = $(window).height();
			}
		}, 250)); // A shorter delay avoids redrawing the view in the middle of a previous request,
				  // but it may kill baby CPUs
	}
});

/**
 * Responsible to refresh the view when we detect a change of location via the browser URL
 */
window.onhashchange = function () {
	"use strict";
	var currentLocation = window.location.href.split('#')[1] || '';
	// The hash location is ALWAYS encoded, despite what the browser shows
	var path = decodeURIComponent(currentLocation);

	// This section tries to determine if the hash location points to a file or a folder
	var albumPath = OC.dirname(path);
	if (Gallery.albumMap[path]) {
		albumPath = path;
	} else if (!Gallery.albumMap[albumPath]) {
		albumPath = '';
	}
	// We need to get new files if we've assessed that we've changed folder
	if (Gallery.currentAlbum !== null && Gallery.currentAlbum !== albumPath) {
		Gallery.getFiles(currentLocation).done(function () {
			Gallery.refresh(path, albumPath);
		});
	} else {
		// When the gallery is first loaded, the files have already been fetched
		Gallery.refresh(path, albumPath);
	}
};
