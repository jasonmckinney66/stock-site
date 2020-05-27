$(document).ready(function(){

	//Only run this on home page
	if ($('body').hasClass('home')) {
	
	//Setup Variable
	var currentView = localStorage['view'];
	
	//Hide Grid or List
	$(".view").hide();
	$("#in").hide();
	
	//Run Loading Graphic
	$("#in").fadeIn("slow");
	
	if (!currentView || currentView=="grid") {
	
		$.get("partials/grid.html", function(data) {
			$("#grid").html(data);
			localStorage['view'] = "grid";
			
			// Initialize Masonry
			var $containter = $('#container-one');
			$containter.imagesLoaded( function(){
    			$containter.masonry({
      				itemSelector: '.box',
      				isAnimated: !Modernizr.csstransitions,
      				isFitWidth: true
				});
			});
			
			$("#in").hide();
			$("#grid").fadeIn("slow");
			
		});
		
	} else {
	
	$.get("partials/list.html", function(data) {
		$("#in").hide();
		$("#list").html(data).fadeIn("slow");
		localStorage['view'] = "list";
		// ReInitialize Interchange
		$(document).foundation('reflow');
		$(document).foundation('interchange', 'reflow');
	});
	
	}
		
	// Toggle Buttons
	
	$('#toggle-grid').click(function(){
	// hide both loops
		$(".view").hide();
		// check if loop aleady has been generated
		if ($("#grid").html() != "") {
			$("#in").hide();
			$("#grid").fadeIn("slow");
		} else { // otherwise fetch loop
			$.get("partials/grid.html", function(data) {
				$("#in").hide();
				$("#grid").html(data).fadeIn("slow");
				localStorage['view'] = "grid";
				// Initialize Masonry
				var $containter = $('#container-one');
				    $containter.imagesLoaded( function(){
				        $containter.masonry({
				          itemSelector: '.box',
				          isAnimated: !Modernizr.csstransitions,
				          isFitWidth: true
				     });	
				});

			});
		}
	});

	$('#toggle-list').click(function(){
		// hide both loops
		$(".view").hide();
		// check if loop aleady has been generated
		if ($("#list").html() != "") {
			$("#in").hide();
			$("#list").fadeIn("slow");
		} else { // otherwise fetch loop
			$.get("partials/list.html", function(data) {
				$("#in").hide();
				$("#list").html(data).fadeIn("slow");
				localStorage['view'] = "list";
				// ReInitialize Interchange
				$(document).foundation('reflow');
				$(document).foundation('interchange', 'reflow');
			});
		}
	});
	
	}
	//End Home Page Stuff
	
	if ($('body').hasClass('single')) {

		$("#container-two").hide();
		$(".panel").hide();
		//Get Image Size
		$(document).on('replace', 'img', function (e, new_path, original_path) {
			function imgSIZE(){
		    	var img = $("#singleImg");
		    	theImage = new Image();
		    	theImage.src = img.attr("src");
		    	var imgW = theImage.width;
		    	var imgH = theImage.height;
		    
		    	$('.dimensions').text('Image Size: ' + imgW + ' X ' + imgH);
			}
			imgSIZE();
			//Fade in image when loaded on detail
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
	
	//Initialize GhostHunter
	$("#search-field").ghostHunter({
		results   : "#results",
		rss       : "rss.xml",
		info_template   : "<p>Number of posts found: {{amount}}</p>",
		result_template : "<a href='{{link}}'><p><h2>{{title}}</h2><h4>{{pubDate}}</h4>{{description}}</p></a>"
	});
	
});