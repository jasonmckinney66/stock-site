$(document).ready(function(){
	
	//Only run this on homes page
	if ($('body').hasClass('home')) {
		$(".view").hide();
		$("#in").fadeIn("slow");
		$(document).foundation();
		$(document).foundation('reflow');
		Foundation.utils.image_loaded($('.random img'), function(){
			$("#in").hide();
			$(".view").fadeIn("slow");
		});
	}
	//Only run this on categories page
	if (($('body').hasClass('categories')) || ($('body').hasClass('tags'))) {
	
		//Hide Stuff
		$(".view").hide();
		$("#in").fadeIn("slow");
		
		// Initialize Masonry
		var $containter = $('#container-one');
		$containter.imagesLoaded( function(){
			$containter.masonry({
					itemSelector: '.box',
					isAnimated: !Modernizr.csstransitions,
					isFitWidth: true
			});
		});
		
		$(document).foundation();
		$(document).foundation('reflow');
		Foundation.utils.image_loaded($('.col4 img'), function(){
			$("#in").hide();
			$("#grid").fadeIn("slow");
		});
				
				
		// Hover on Grid Item
		$(document).on('mouseenter','.roContent', function (event) {
			  $(this).addClass('active')
		}).on('mouseleave','.roContent',  function(){
			  $(this).removeClass('active')
		});
		
		// Back to Top
		$("#followTab").hide();
		var divs = $('#followTab');
		$(window).scroll(function(){
		   if($(window).scrollTop()>100){
		         divs.fadeIn("slow");
		   } else {
		         divs.fadeOut("slow");
		   }
		});
		divs.click(function(){
			$("html, body").animate({ scrollTop: 0 }, 500);
			return false;
		});
		
		// Wait, why are you leaving
		triggered = false;
		if (!sessionStorage.alreadyVisited) {
		sessionStorage.alreadyVisited = 1;
		$("body").on("mouseleave", function (e) {
		    if (e.offsetY - $(window).scrollTop() < 0 && !triggered) {
		        triggered = true;
		        $('#leavingModal').foundation('reveal', 'open');
		    }
		});
		}
		
	}	//End Home Page Stuff
		

	//Start Single Photo
	if ($('body').hasClass('photos')) {
		$("#container-two").hide();
		$(".panel").hide();
		//Fade in image when loaded on detail
		Foundation.utils.image_loaded($('.main-image img'), function(){
				$("#in").hide();
				$("#container-two").fadeIn("slow");
				$(".panel").fadeIn("slow");
			});
	}
	
	//Open Search Window
	var $openSearchBtn = $('.js-open-search');
	var $closeSearchBtn = $('.js-close-search');
	var $bigSearchContainer = $('.big-search');
	$openSearchBtn.on('click', function (e) {
	    e.preventDefault();
	    $bigSearchContainer.addClass('open');
	    $(window).scrollTop(0);
	    $('html').addClass('html-search-height');
	    $bigSearchContainer.find('input[type=text]').focus();
	});
	$closeSearchBtn.on('click', function (e) {
	    e.preventDefault();
	    $('html').removeClass('html-search-height');
	    $bigSearchContainer.removeClass('open');
	});
	
	//Open Nav Window
	var $openNavBtn = $('.js-open-nav');
	var $closeNavBtn = $('.js-close-nav');
	var $bigNavContainer = $('.big-nav');
	$openNavBtn.on('click', function (e) {
	    e.preventDefault();
	    $bigNavContainer.addClass('open');
	    $(window).scrollTop(0);
	    $('html').addClass('html-nav-height');
	});
	$closeNavBtn.on('click', function (e) {
	    e.preventDefault();
	    $('html').removeClass('html-nav-height');
	    $bigNavContainer.removeClass('open');
	});
	
});