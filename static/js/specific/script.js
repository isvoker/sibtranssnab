(function(d, $, C) {
    'use strict';

    //C.defaultOverlay.backgroundColor = 'rgba(20,43,67,.8)';
	/*------------------
            Navigation
        --------------------*/
	$("#menu-canvas-show").on('click', function () {
		$('.offcanvas-menu-wrapper').fadeIn(400, function () {
			$('.offcanvas-menu-wrapper').addClass('active');
		}).css("display", "flex");
	});
	$("#menu-canvas-close").on('click', function () {
		$('.offcanvas-menu-wrapper').removeClass('active').delay(1100);
		$('.offcanvas-menu-wrapper').fadeOut(400);
	});


	/*------------------
		Background Set
	--------------------*/
	$('.set-bg').each(function() {
		var bg = $(this).data('setbg');
		$(this).css('background-image', 'url(' + bg + ')');
	});


	/*------------------
		Hero Item Size
	--------------------*/
	function heroItemSize () {
		if($(window).width() > 767) {
			var header_h = $('.header-section').innerHeight();
			var footer_h = $('.footer-section').innerHeight();
			var window_h = $(window).innerHeight();
			var hero_item_h = ((window_h) - (header_h + footer_h + 5));
			$('.hero-item').each(function() {
				$(this).height(hero_item_h);
			});

		}
	}
	if($(window).width() > 767) {
		heroItemSize ();
		$(window).resize(function(){
			heroItemSize ();
		});
	}

	/*------------------
		Hero Slider
	--------------------*/
	$('.hero-slider').owlCarousel({
		loop: true,
		nav: true,
		dots: false,
		navText:['<i class="arrow_left"></i>','<i class="arrow_right"></i>'],
		mouseDrag: false,
		animateOut: 'fadeOut',
		animateIn: 'fadeIn',
		items: 1,
		autoplay: true,
		smartSpeed: 1000,
	});

     C.gaSendEvent = (category, action) => {
		try {
			if (
				 C.is(window.ga, 'undefined')
				||  C.is(category, 'undefined')
				||  C.is(action, 'undefined')
			) {
				return;
			}
			ga('send', {
				hitType: 'event',
				eventCategory: category,
				eventAction: action
			});
			return true;
		} catch (err) {
			return false;
		}
	};

	 C.yaMetrikaReachGoal = (target) => {
		try {
			if (
				! C.hasOwnProperty('yaCounterID')
				|| ! w.ya_counter_id
				||  C.is(window['yaCounter' +  w.ya_counter_id], 'undefined')
				||  C.is(target, 'undefined')
			) {
				return;
			}
			window['yaCounter' +  w.ya_counter_id].reachGoal(target);
            console.log(w.ya_counter_id + ' - ' + target);
			return true;
		} catch (err) {
			return false;
		}
	};

	 C.onReachGoal = (goal, category) => {
		 C.gaSendEvent(category, goal);
		 C.yaMetrikaReachGoal(goal);
	};

    d.addEventListener('DOMContentLoaded', () => {
        //d.querySelector('.main-content').style.visibility = 'visible';
    });

}(document, jQuery, Common));