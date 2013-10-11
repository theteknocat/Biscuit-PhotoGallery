var Gallery = {
	page_title_id: 'page-title',  // Set this to a custom value in your template/view if different by adding Gallery.page_title_id = 'some-id'; in a document ready method
	description_showing: false,
	description_fadeout_timer: null,
	init_sortable: function(type, sorting_url) {
		$('.drag-handle').show();
		$('.drag-handle').button({
			icons: {
				primary: 'ui-icon-arrow-4'
			}
		});
		var sortable_options = {
			action: 'resort',
			handle: '.drag-handle',
			array_name: type+'_sort',
			onUpdate: function() {
				Gallery.show_throbber(__('saving'));
			},
			onFinish: function() {
				Gallery.hide_throbber();
			}
		}
		if (type == 'photo') {
			sortable_options.action = 'resort_photo';
		}
		Biscuit.Crumbs.Sortable.create($('#photo-gallery-content'),sorting_url,sortable_options);
	},
	show_throbber: function(message) {
		Biscuit.Crumbs.ShowCoverThrobber('photo-gallery-content', message);
	},
	hide_throbber: function() {
		$('.drag-handle').removeClass('ui-state-hover');
		Biscuit.Crumbs.HideCoverThrobber('photo-gallery-content');
	},
	description_init: function() {
		$('#photo-description p:last').css({'margin': '0'});
		$('.large-image').live('mouseover',function() {
			clearTimeout(Gallery.description_fadeout_timer);
			if (!Gallery.description_showing) {
				Gallery.description_showing = true;
				$('#photo-description').fadeIn('normal');
			}
		});
		$('.large-image').live('mouseout',function() {
			if (Gallery.description_showing) {
				Gallery.description_fadeout_timer = setTimeout("$('#photo-description').fadeOut('normal', function() { Gallery.description_showing = false; });",250);
			}
		});
		$('#photo-description').live('mouseover',function() {
			clearTimeout(Gallery.description_fadeout_timer);
		});
		
	},
	Nav: {
		is_transitioning: false,
		json_cache: {},
		// IE7< workaround, since it doesn't usually fire the load event on images once cached. What we do is keep track of once an image has been loaded, and therefore cached
		// so we can still make sure it transitions when no load event fires
		image_cached: {},
		init: function() {
			$('.prev-button').click(function() {
				if (!Gallery.Nav.is_transitioning) {
					Gallery.Nav.load_photo($(this).attr('href'), 'prev');
				}
				return false;
			});
			$('.next-button').click(function() {
				if (!Gallery.Nav.is_transitioning) {
					Gallery.Nav.load_photo($(this).attr('href'), 'next');
				}
				return false;
			});
		},
		load_photo: function(url, mode) {
			Gallery.Nav.is_transitioning = true;
			var cache_data = this.get_cache(url);
			if (cache_data) {
				this.update_content(mode, cache_data);
				return;
			}
			$('#photo-gallery-content').append($('<div>')
				.attr('id', 'slide-nav-throbber')
				.css({
					'position': 'absolute',
					'z-index': '10',
					'width': '50px',
					'height': '50px',
					'left': (($('#photo-gallery-content').width()/2)-25)+parseInt($('#photo-gallery-content').css('padding-left'))+'px',
					'top': (($('#photo-gallery-content').height()/2)-25)+parseInt($('#photo-gallery-content').css('padding-top'))+'px',
					'background-image': 'url(/framework/themes/sea_biscuit/images/cover-bg.png)',
					'-moz-border-radius': '8px',
					'-webkit-border-radius': '8px',
					'border-radius': '8px'
				}).append($('<div>')
					.css({
						'width': '50px',
						'height': '50px',
						'background-image': 'url(/framework/themes/sea_biscuit/images/throbber.gif)',
						'background-repeat': 'no-repeat',
						'background-position': 'center center'
					})
				)
			);
			Biscuit.Ajax.Request(url+'?ajax', 'json', {
				type: 'get',
				success: function(data,text_status,xhr) {
					Gallery.Nav.cache_json_data(data);
					Gallery.Nav.update_content(mode,data);
				},
				error: function() {
					Biscuit.Crumbs.Alert("Error loading photo, please contact the system administrator.", "Error");
				}
			});
		},
		cache_json_data: function(data) {
			this.json_cache[data.id] = data;
		},
		get_cache: function(url) {
			var photo_id = url.match(/\/show_photo\/([0-9]+)\//)[1];
			if (this.json_cache[photo_id] != undefined) {
				return this.json_cache[photo_id];
			}
			return false;
		},
		update_content: function(mode, data) {
			$('#photo-gallery-content').css({
				'position': 'relative',
				'overflow': 'hidden',
				'height': ($('#photo-gallery-content img').height()+2)+'px'
			});
			$('#photo-description').remove();
			$('.large-image').wrap($('<div>')
				.attr('id', 'image-container-first')
				.addClass('image-container')
				.css({
					'width': $('#photo-gallery-content').width()+'px',
					'height': $('#photo-gallery-content').height()+'px',
					'position': 'absolute',
					'left': '0px',
					'z-index': '5'
				})
			);
			if (mode == 'prev') {
				var new_left = -$('#photo-gallery-content').width()+'px'
				var first_animate_left_to = $('#photo-gallery-content').width()+'px';
			} else {
				var new_left = $('#photo-gallery-content').width()+'px'
				var first_animate_left_to = -$('#photo-gallery-content').width()+'px';
			}
			$('#photo-gallery-content').append($('<div>')
				.attr('id','image-container-second')
				.addClass('image-container')
				.css({
					'width': $('#photo-gallery-content').width()+'px',
					'height': $('#photo-gallery-content').height()+'px',
					'position': 'absolute',
					'left': new_left,
					'z-index': '6'
				}).append($('<div>')
					.addClass('large-image')
					.css({
						'width': (data.img_width+2)+'px',
						'height': (data.img_height+2)+'px'
					})
				)
			);
			if (data.original_url) {
				$('#image-container-second .large-image').append($('<a>')
					.attr('href', data.original_url)
					.attr('title', data.title)
					.css({
						'width': (data.img_width+2)+'px',
						'height': (data.img_height+2)+'px'
					}).append($('<img>')
						.attr('id', 'gallery-image-'+data.id)
						.attr('src', data.photo_url)
						.attr('width', data.img_width)
						.attr('height', data.img_height)
						.attr('alt', data.title)
						.attr('border', 0)
						.load(function() {
							// IE7< workaround, since it doesn't usually fire the load event on images once cached
							if (!Gallery.Nav.image_cached[data.id]) {
								Gallery.Nav.image_cached[data.id] = true
								Gallery.Nav.transition(first_animate_left_to, data);
							}
						})
					)
				);
			} else {
				$('#image-container-second .large-image').append($('<img>')
					.attr('id', 'gallery-image-'+data.id)
					.attr('src', data.photo_url)
					.attr('width', data.img_width)
					.attr('height', data.img_height)
					.attr('alt', data.title)
					.attr('border', 0)
					.load(function() {
						// IE7< workaround, since it doesn't usually fire the load event on images once cached
						if (!Gallery.Nav.image_cached[data.id]) {
							Gallery.Nav.image_cached[data.id] = true
							Gallery.Nav.transition(first_animate_left_to, data);
						}
					})
				);
			}
			if (data.description) {
				$('#image-container-second .large-image').append($('<div>')
					.attr('id', 'photo-description')
					.css('width', data.img_width+'px')
					.html(data.description)
				);
				$('#photo-description p:last').css({'margin': '0'});
			}
			if (data.photo_edit_url) {
				$('.photo-edit-button').attr('href',data.photo_edit_url);
			}
			if (data.photo_del_url) {
				$('.photo-delete-button').attr('href',data.photo_del_url);
			}
			if (Gallery.Nav.image_cached[data.id]) {
				// IE7< workaround, since it doesn't usually fire the load event on images once cached
				Gallery.Nav.transition(first_animate_left_to, data);
			}
		},
		transition: function(first_animate_left_to, data) {
			document.title = data.full_title;
			// Expect an element with ID page title. If theme uses a different ID, customize this file acording
			if ($('#'+Gallery.page_title_id).length == 0) {
				// Log a notice for developers
				Biscuit.Console.log("No page title element with the ID '"+Gallery.page_title_id+"' was found in the page so the title cannot be updated. Please update your theme to add this ID to the title element, or customize the show_photo.php view file and have it set the correct page title element ID by adding Gallery.page_title_id = 'your-id'; to the document ready method.");
			}
			$('#'+Gallery.page_title_id).text(data.title);
			$('.breadcrumb-last').text(data.title);
			if (data.prev_url) {
				$('.prev-button').show();
				$('.prev-button').attr('href', data.prev_url);
			} else {
				$('.prev-button').hide();
				$('.prev-button').attr('href', '');
			}
			if (data.next_url) {
				$('.next-button').show();
				$('.next-button').attr('href', data.next_url);
			} else {
				$('.next-button').hide();
				$('.next-button').attr('href', '');
			}
			$('.photo-show-controls .admin').text(data.count_text);
			var new_height = data.img_height+2;
			$('#slide-nav-throbber').remove();
			var curr_height = $('#image-container-first').height();
			if (new_height > curr_height) {
				$('#photo-gallery-content').animate({
					'height': new_height+'px'
				},'fast','swing',function() {
					Gallery.Nav.do_slide(first_animate_left_to);
				});
			} else if (new_height < curr_height) {
				this.do_slide(first_animate_left_to, new_height);
			} else {
				this.do_slide(first_animate_left_to);
			}
		},
		do_slide: function(first_animate_left_to, after_height) {
			$('#image-container-first').animate({
				'left': first_animate_left_to
			},'normal','swing');
			$('#image-container-second').animate({
				'left': '0px'
			},'normal','swing',function() {
				Gallery.Nav.is_transitioning = false;
				$('#image-container-first').remove();
				$('.large-image').unwrap();
				$('#photo-gallery-content').css({
					'overflow': 'visible'
				});
				if (after_height !== undefined) {
					$('#photo-gallery-content').animate({
						'height': after_height+'px'
					},'fast','swing');
				}
			});
		}
	}
}
