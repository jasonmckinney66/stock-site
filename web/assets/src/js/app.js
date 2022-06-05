$(document).on('ready', function(){
  alertBar();
  alertBarCheck();
});
// Check cookie
$(function alertBarCheck() {
  if (localStorage.getItem("alert") !== null) {
  };
}) 
//Assign cookie on click
$(function alertBar() {
  const showAlert = localStorage.getItem("alert") === null;
  $(".alert").toggleClass("d-none",!showAlert)
  $('.alert').show();
  $(".js-close").on("click",function() {
    localStorage.setItem("alert","seen");
    $(this).closest(".alert").addClass("d-none");
    $('body').removeClass("alert-bar");
  });
})
// Goodbye Widows
function noMoreLonelyWords(selector, numWords) {
  // Get array of all the selected elements
  var elems = document.querySelectorAll(selector);
  var i;
  for (i = 0; i < elems.length; ++i) {
  // Split the text content of each element into an array
  var textArray = elems[i].innerText.split(" ");
  // Remove the last n words and join them with a none breaking space
  var lastWords = textArray.splice(-numWords, numWords).join("&nbsp;");
  // Join it all back together and replace the existing
  // text with the new text
  var textMinusLastWords = textArray.join(" ");
  elems[i].innerHTML = textMinusLastWords + " " + lastWords;
  }
}
noMoreLonelyWords(".widont", 2);

// More
(function($) {
  $.fn.more = function(options) {
      var config = $.extend({}, $.fn.more.defaults, options);

      return this.each(function() {
          //------------------------------------------------------------------
          // Variables
          //------------------------------------------------------------------

          var $filter  = $(this);
          var section  = $filter.data('section');
          var $content = $(config.contentSelector);
          var $post    = $(config.postSelector);
          var $more    = $(config.moreSelector);
          var useHist  = !!(window.history && history.pushState);

          //------------------------------------------------------------------
          // Functions
          //------------------------------------------------------------------

          function appendContent(html) {
              $content.append(html);
              showHideMore();
              updateCount();
          }

          function currentFilter() {
              return $filter.find('option:selected:first');
          }

          function currentOffset() {
              return $content.find(config.postSelector).length;
          }

          function replaceContent(html) {
              $content.empty().append(html);
              showHideMore();
              updateCount();
          }

          function showHideMore() {
              if (currentOffset() < currentFilter().data('count')) {
                $more.show();
              } else {
                $more.hide();
              };
          }

          function updateAddressBar(href) {
              if (useHist) {
                history.pushState(null, null, href);
              }
          }

          function updateCount() {
            let currentCount = currentOffset();
            let totalCount = $('.total-count:last').text();
            if (config.displayCount) {
              let $displayCount = $('.display-count');
              if ($displayCount.length) {
                $displayCount.text(currentCount);
              }
            }
            if (config.displayTotal) {
              let $displayTotal = $('.display-total');
              if ($displayTotal.length && totalCount.length) {
                $displayTotal.text(totalCount);
              }
            }
            console.log('currentCount: '+currentCount, 'totalCount: '+totalCount);
            if (totalCount.length) {
              if (currentCount < totalCount) {
                $('.counts').show();
              } else {
                $('.counts').hide();
              }
            }
          }

          //------------------------------------------------------------------
          // Events
          //------------------------------------------------------------------

          $filter.change(function() {
              var $cur = $(this).find('option:selected:first');

              var params = {
                 section: section,
                category: $cur.data('category'),
                   where: config.where,
                 orderBy: config.orderBy,
                  offset: 0,
                   limit: config.limit,
              };

              $.get(config.url, params, replaceContent);

              updateAddressBar($cur.data('url'));
          });

          $more.click(function() {
              var params = {
                 section: section,
                category: currentFilter().data('category'),
                   where: config.where,
                 orderBy: config.orderBy,
                  offset: currentOffset(),
                   limit: config.limit,
              };

              $.get(config.url, params, appendContent);
          });

          //------------------------------------------------------------------
          // On page load
          //------------------------------------------------------------------

          showHideMore();
      });
  };

  // Override these defaults to customize behavior:
  $.fn.more.defaults = {
    contentSelector: '.listings',   // In template: jQuery selector of content list
       postSelector: '.col',        // In template: jQuery selector of content entry
       moreSelector: 'button.more', // In template: load more button
                url: '/more', // Script invoked by js
              limit: 9,       // In "url" script: max entries to load per call
            orderBy: '',      // In "url" script: custom sort entries
              where: '',      // In "url" script: custom entry filter
       displayCount: false,   // Display current count of entries shown in page
       displayTotal: false,   // Display total number of entries in database
  };
}(jQuery));

// Search dropdown
$(function() {
  $(document).on('click', function (e) {
    var $target = $(e.target),
        isItem = $target.hasClass('nav-menu__item--parent'),
        isLink = $target.hasClass('nav-menu__link'),
        $el = isLink ? $target.parent() : $target,
        wasOpened = $el.hasClass('opened');

    if (isItem || isLink && !wasOpened) {
      $el.addClass('opened')
      $('.nav').removeClass('shadow');
    }
  });

  $('.js-openSearch').on('click', function (e) {
    e.preventDefault();
    $('.search').show();
    $('#headerSearch').toggleClass('opened');
    document.getElementById("searchField").focus();
  });

  $('.search-close').on('click', function (e) {
    e.preventDefault();
    $(this).closest('.opened').removeClass('opened');
  });
});

// On-page Navigation
$('body.onpage-nav #navigation').navpoints({
  offset: 100
});
$('#navigation').navpoints({
  updateHash: false
});

/* Dark nav
(function () {
	var header = document.getElementsByTagName('nav')[0];

	var run = function () {
		var scroll = function (e) {
			if (window.pageYOffset > 0) {
				header.style.background = 'rgba(0, 0, 0, .7)';
			} else {
				header.style.background = null;
			}
		};

		window.addEventListener('scroll', scroll, false);
	};

	header ? run() : false;
}());
*/

// Intersectional Observer
function callback(entries) {
  for (var i in entries) {
    entries.forEach(function (entry) {
      if (entry.isIntersecting) {
        entry.target.classList.add('in-view');
      } else {
        entry.target.classList.remove('in-view');
      }
    });
  }
}

function initObserver() {
  var observer = new IntersectionObserver(callback);
  var items = document.querySelectorAll('.js-observe');
  for (var i in items) {
    if (!items.hasOwnProperty(i)) {
      continue;
    }
    observer.observe(items[i]);
  }
}
if (window.IntersectionObserver) {
  initObserver();
} else {
  console.log("IntersectionObserver not supported.");
}
//Only run this on home page
if ($('body').hasClass('home')) {
  $(".view").hide();
  $("#in").fadeIn("slow");
  $(window).on("load", function() {
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
  
  $(window).on("load", function() {
    $("#in").hide();
    $("#grid").fadeIn("slow");
  });
      
  // Hover on Grid Item
  $(document).on('mouseenter','.roContent', function (event) {
      $(this).addClass('active')
  }).on('mouseleave','.roContent',  function(){
      $(this).removeClass('active')
  });
}