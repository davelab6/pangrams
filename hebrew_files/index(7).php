// סקריפט המשמש את תבנית המצגת

// Written by [[he:User:קובי כרמל]]

// version 3.3

'use strict';

if(!window.SS_DEBUG) {

	var SlideShow = function(object, fullScreen){

		var self = this;

		this.buttons = {

			options: {
				html: true,
				delayIn: 500,
				fade: true,
				delayOut: 0
			},

			attr: {
				'zoomin': self.local.showFullScreen,
				'seek-end': self.local.firstImage,
				'seek-next': self.local.prevImage,
				'triangle-1-w': self.local.play,
				'seek-prev': self.local.nextImage,
				'seek-start': self.local.lastImage,
				'help': self.local.help
			},

			create: function(){

				var toolbar = object.find('.gallery-slideshow-toolbar');

				for(var b in self.buttons.attr) {
					if(b == 'zoomin' && fullScreen)
						continue;
					var className = 'ui-icon-' + b,
						button = $('<button>', {'class': className})
							.button({label: self.buttons.attr[b], text: false, icons: {primary: className}})
							.tipsy(self.buttons.options)
							.click(function(e){
								self.animate($(this), e.isTrigger);
							});

					toolbar.append(button);
				}

				toolbar.children().css({width: 25, height: 25});
			}

		};

		this.height = 0; // computed height

		this.length = 0; // Amount of images

		this.object = object;

		this.theight = 0; // the greatest height of images title

		this.addBullets = function(){

			var options = self.buttons.options,
				bullets = object.find('.slideshow-bullets');

			self.links.each(function(){
				options.fallback = $(this).data('title');
				bullets
					.append($('<span>', {'class': 'ui-icon ui-icon-radio-off'})
						.tipsy(options)
						.click(function(){
							self.animate($(this).index());
						})
				)
			})
		};

		this.addImages = function(res){

			var pages = res.query.pages;

			self.links.each(function(){
				var info,
					title = self.decodeLink($(this)),
					isFirst = !self.length;

				for(var i in pages)
					if(pages[i].title == title) {
						info = pages[i].imageinfo[0];
						break;
					}

				var height = info.thumbheight,
					src = info.thumburl;

				$(this).children()
					.attr('src', src)
					.width(info.thumbwidth)
					.height(height)
					.wrap($('<div>', {'class': 'img-wrapper'})
						.css('right', isFirst ? 0 : self.imageWidth)
				);

				if(!$(this).index()) {
					$(this).addClass('active');
					object.find('.slideshow-title').css('display', 'block'); // IE to hell
					object.find('.slideshow-files').css('opacity', 1).show();
					object.find('.slideshow-preview, #slideshow-loading').hide();
				}

				if(self.height < height)
					self.height = height;

				self.length++;
			});

			self.addBullets();
			self.showNavigator();

			if (!fullScreen) {
				if ($.client.profile().name == 'msie')
					setTimeout(self.computeHeight, 500);
				else
					self.computeHeight();
			}
		};

		this.addTitles = function(){
			var title,
				htmlTitle,
				theight,
				rows = object.find('.slideshow-preprocessing p').html().split(self.local.file + ':');

			object.find('.slideshow-title').hide();

			for(var i = 0; i < rows.length; i++) {
				if(!$.trim(rows[i])) {
					rows.splice(i, 1);
					i--;
					continue;
				}
				title = rows[i].split('#');

				if(title[2])
					for(var m = 2; m < title.length; m++)
						title[1] += '#' + title[m];

				htmlTitle = title[1] || '';
				self.links.eq(i).data({title: htmlTitle});

				theight = object.find('.theight-calculator').html(htmlTitle).height();

				if(self.theight < theight)
					self.theight = theight;
			}
		};

		this.animate = function(button, trigger){
			var bullet = typeof button == 'number',
				slider = object.find('.slideshow-files'),
				active = slider.children('a.active'),
				index = bullet ? button : self.getActive();

			if(!bullet) {
				var method, className = function(){
					var cls = button.attr('class').split(' ');
					for(var n in cls)
						if(cls[n].match(/ui-icon-/))
							return cls[n].replace('ui-icon-', '');
				}.call();

				switch(className) {
					case 'zoomin':
						return self.fullScreen();
					case 'seek-end':
						if(index) method = 'prev';
						index = 0;
						break;
					case 'seek-next':
						if(index) method = 'prev';
						index--;
						break;
					case 'seek-prev':
						if(index < self.length - 1) method = 'next';
						else self.stop();
						index++;
						break;
					case 'seek-start':
						if(index < self.length) method = 'next';
						index = self.length - 1;
						break;
					case 'triangle-1-w':
						return self.play();
					case 'stop':
						return self.stop();
					case 'help':
						return window.open(location.protocol + '//' + location.hostname + '/wiki/' + self.local.helpPage, '_blank');
				}

				button.blur();
			}

			if(!trigger) self.stop();

			var toImage = slider.find('a').eq(index),
				zIndex = eval(object.find('.active').children().css('z-index')),
				newImage = toImage.children(),
				oldImage = active.children(),
				animComplete = function(){
					newImage.css('zIndex', zIndex + 1);
					active.removeClass('active').children().css({right: self.imageWidth, marginTop: 0});
					toImage.addClass('active').children().css('right', 0);
					object.find('.slideshow-title').html(toImage.find('img').data('title'));
					self.showNavigator();
				};

			var effects = {};

			effects[self.local.verSliding] = {
				prev: {
					old: {
						animate: {right: self.imageWidth}
					},

					toNew: {
						css: {right: 0}
					}
				},

				next: {
					toNew: {
						css: {zIndex: zIndex + 1},
						animate: {right: 0}
					}
				}
			};

			effects[self.local.horizSliding] = {
				prev: {
					toNew: {
						css: {marginTop: -self.imageHeight, zIndex: zIndex + 1, right: 0},
						animate: {marginTop: 0}
					}
				},

				next: {
					old: {
						css: {zIndex: zIndex + 1},
						animate: {marginTop: -self.imageHeight}
					},
					toNew: {
						css: {right: 0}
					}
				}
			};

			effects[self.local.fading] = {
				next: {
					toNew: {
						css: {zIndex: zIndex + 1, opacity: 0, right: 0},
						animate: {opacity: 1}
					}
				}
			};

			if(bullet) {
				var oldIndex = self.getActive();
				if(oldIndex == index)
					return;
				method = index > oldIndex ? 'next' : 'prev';
			}

			if(method) {
				var effect;
				if(effects[self.currentEffect])
					effect = effects[self.currentEffect][method];
				else
					effect = effects[self.local.verSliding][method];

				if(!effect) effect = effects[self.currentEffect][method == 'next' ? 'prev' : 'next'];

				if(effect.toNew) {
					if(effect.toNew.css) newImage.css(effect.toNew.css);
					if(effect.toNew.animate) newImage.animate(effect.toNew.animate, self.duration, animComplete);
				}

				if(effect.old) {
					if(effect.old.css) oldImage.css(effect.old.css);
					if(effect.old.animate) oldImage.animate(effect.old.animate, self.duration, animComplete);
				}
			}
		};

		this.computeHeight = function(){
			var theight = self.theight,
				heightDiff = theight - 57,
				caption = object.find('.slideshow-caption'),
				capHeight = caption.text() ? caption.show().height() : 0,
				bullHeight = object.find('.slideshow-bullets').height(),
				height = self.height + heightDiff + bullHeight + capHeight;

			object
				.height(height + 120)
				.find('.slideshow-wrapper')
				.height(height + 65)
				.find('.slideshow-files')
				.height(self.height + 2)
				.nextAll('.slideshow-title')
				.height(theight);
		};

		this.construct = function(){

			var imageBox = object.find('.slideshow-files');

			self.requires = imageBox.html();
			self.links = imageBox.find(' .gallerybox > div > .thumb a.image');
			imageBox.html(self.links);
			self.addTitles();

			if(fullScreen)
				imageBox.after($('<div>', {id: 'slideshow-loading'}).text(self.local.loading + '...'));

			self.getSource();
		};

		this.decodeLink = function(elem){
			return self.local.file + ':' + decodeURIComponent(elem.attr('href').split(':')[1].replace(/_/g, ' '));
		};

		this.fullScreen = function(){

			var content = object.clone(),
				toolbar = object.find('.gallery-slideshow-toolbar').clone().empty(),
				option = {
					imageWidth: 600,
					imageHeight: 400
				};

			for(var o in option) {
				if(!content.find('.' + o).length)
					content.find('.slideshow-custom').append($('<div>').addClass(o));
				content.find('.' + o).text(option[o]);
			}

			content.find('.gallery-slideshow-toolbar, .slideshow-navigator').remove();
			content.find('.slideshow-bullets').empty();
			content.find('.slideshow-files')
				.css('opacity', 0)
				.height(option.imageHeight)
				.html(self.requires);
			content.find('.slideshow-title').height(30);

			content.css({
				width: option.imageWidth,
				height: option.imageHeight + 55
			});

			$('#ssDialog').remove();

			var options = {
				bgiframe: true,
				draggable: false,
				resizable: false,
				width: option.imageWidth + 30,
				height: option.imageHeight + 110,
				modal: true
			};

			var $dialog = $('<div>', {id: 'ssDialog'});

			$dialog
				.css({
					display: 'none',
					overflow: 'visible'
				})
				.append(content);

			$('body').append($dialog);

			$dialog.dialog(options);

			if ($.client.profile().name == 'msie') // for the cursed IE
				$('html').scrollTop($('#ssDialog').offset().top - 120);

			toolbar.css({
				textAlign: 'center',
				border: 'none'
			});

			var uiDialog = $dialog.parent();

			uiDialog.find('.ui-dialog-title').replaceWith(toolbar);

			var fs = new SlideShow(uiDialog, true);

			fs.run();
		};

		this.getActive = function(){
			return object.find('a.active').index();
		};

		this.getSource = function(){

			var titles = [],
				param = {
					action: 'query',
					format: 'json',
					prop: 'imageinfo',
					iiprop: 'url',
					iiurlwidth: self.imageWidth,
					iiurlheight: self.imageHeight
				};

			self.links.each(function(){
				titles.push(self.decodeLink($(this)));
			});

			param.titles = titles.join('|');

			$.getJSON('/w/api.php', param, function(res){
				self.addImages(res);
			})
		};

		this.play = function(){
			clearInterval(self.timer);
			object.find('.ui-icon-triangle-1-w').toggleClass('ui-icon-triangle-1-w ui-icon-stop')
				.filter('button')
				.attr('original-title', self.local.stop);
			self.timer = setInterval(function(){
				object.find('button.ui-icon-seek-prev').click();
			}, self.delay);
		};

		this.setOptions = function(){
			var options = {
				currentEffect: object.find('.currentEffect').text() || self.local.verSliding,
				imageHeight: parseInt(object.find('.imageHeight').text()) || 265,
				imageWidth: parseInt(object.find('.imageWidth').text()) || 220,
				duration: parseInt(object.find('.duration').text()) || 600,
				delay: parseInt(object.find('.delay').text()) || 3000
			};

			for(var o in options)
				self[o] = options[o];
		};

		this.showNavigator = function(){
			var index = self.getActive(),
				text = self.local.imageOf.replace('$1', index + 1).replace('$2', self.length);

			object.find('.slideshow-navigator').text(text);
			object.find('.slideshow-title').html(self.links.eq(index).data('title'));
			object.find('.slideshow-bullets span')
				.eq(index)
				.toggleClass('ui-icon-radio-off ui-icon-bullet')
				.siblings('.ui-icon-bullet')
				.toggleClass('ui-icon-radio-off ui-icon-bullet');
		};

		this.run = function(){
			self.setOptions();
			self.construct();
			self.buttons.create();
		};

		this.stop = function(){
			clearInterval(self.timer);
			object.find('.ui-icon-stop')
				.toggleClass('ui-icon-triangle-1-w ui-icon-stop')
				.filter('button')
				.attr('original-title', self.local.play)
				.blur();
		}
	};

	SlideShow.prototype.local = {
		direction: 'rtl',
		showFullScreen: 'צפיה בתמונות מוגדלות',
		firstImage: 'לתמונה הראשונה',
		prevImage: 'לתמונה הקודמת',
		play: 'הפעלת המצגת',
		nextImage: 'לתמונה הבאה',
		lastImage: 'לתמונה האחרונה',
		help: 'עזרה',
		file: 'קובץ',
		helpPage: 'עזרה:מצגת תמונות',
		horizSliding: 'החלקה אנכית',
		verSliding: 'החלקה אופקית',
		fading: 'עמעום',
		loading: 'טוען תמונות',
		stop: 'סיום מצגת',
		imageOf: 'תמונה $1 מתוך $2'
	};

	$(function(){
		if ( $('.gallery-slideshow').length === 0 ) return;
		mw.loader.using(['jquery.ui.button', 'jquery.tipsy', 'jquery.ui.dialog'], function(){
			$('.gallery-slideshow')
				.each(function(){
					var slide = new SlideShow($(this));
					slide.run();
				})
				.on('click', function(){
					$('.gallery-slideshow.selected').removeClass('selected');
					$(this).addClass('selected');
				});

			$(document).keydown(function(event){

				var code = event.keyCode,
					$dialog = $('#ssDialog'),
					$elem = $dialog.is(':visible') ? $dialog.prev() : $('.gallery-slideshow.selected'); // is full screen?

				if((code != 37 && code != 39) || !wgIsArticle || !$elem.length)
					return;

				if(code == 37)
					$elem.find('button.ui-icon-seek-prev').click();
				else
					$elem.find('button.ui-icon-seek-next').click();

				return false;
			})
		})
	});

	importStylesheet('Mediawiki:SlideShow.css');
}