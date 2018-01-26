'use strict';

//Begin Gifts Slider
function giftsSlider(selector, prev, next) {
  $(selector).slick({
    slidesToShow: 1,
    slidesToScroll: 1,
    dots: false,
    arrows: false,
    fade: true,
    cssEase: 'linear'
  });

  $(prev).on('click', function (e) {
    e.preventDefault();
    $(selector).slick('slickPrev');
  });

  $(next).on('click', function (e) {
    e.preventDefault();
    $(selector).slick('slickNext');
  });
};
//End Gifts Slider


//Begin Shares Slider
function sharesSlider(selector, slides) {
  $(selector).slick({
    slidesToShow: slides,
    slidesToScroll: 1,
    dots: true,
    arrows: false
  });
};
//End Shares Slider


//Begin Portfolio Slider
function portfolioSlider(selector, slides) {
  $(selector).slick({
    slidesToShow: slides,
    slidesToScroll: 1,
    dots: false,
    arrows: false,
    responsive: [{
      breakpoint: 901,
      settings: {
        slidesToShow: 3
      }
    }, {
      breakpoint: 801,
      settings: {
        slidesToShow: 2
      }
    }, {
      breakpoint: 481,
      settings: {
        slidesToShow: 1
      }
    }]
  });

  $('.portfolio-prev').on('click', function (e) {
    e.preventDefault();
    $(selector).slick('slickPrev');
  });

  $('.portfolio-next').on('click', function (e) {
    e.preventDefault();
    $(selector).slick('slickNext');
  });
};
//End Portfolio Slider

//Begin Reviews Slider
function reviewsSlider(selector, slides) {
  $(selector).slick({
    slidesToShow: slides,
    slidesToScroll: 1,
    dots: false,
    arrows: false,
    responsive: [{
      breakpoint: 967,
      settings: {
        slidesToShow: 2
      }
    }, {
      breakpoint: 655,
      settings: {
        slidesToShow: 1
      }
    }]
  });

  $('.reviews-prev').on('click', function (e) {
    e.preventDefault();
    $(selector).slick('slickPrev');
  });

  $('.reviews-next').on('click', function (e) {
    e.preventDefault();
    $(selector).slick('slickNext');
  });
};
//End Reviews Slider


//Begin Tabs
function tabs() {
  $('.tab-item').not(':first').hide();
  $('.js-tab .tab').click(function () {
    $('.js-tab .tab').removeClass('active').eq($(this).index()).addClass('active');
    $('.js-tab .tab-item').hide().eq($(this).index()).fadeIn();
  }).eq(0).addClass('active');
}
//End Tabs

//Begin Portfolio Tabs
function portfolioTabs() {
  $('.pic-tabs li').click(function () {
    var picMediumSrc = $(this).data('medium');
    var picLargeSrc = $(this).data('large');
    var picMedium = $(this).closest('.pic-tabs-wrap').find('.pic-content img');
    picMedium.attr('src', picMediumSrc);
    $('.preload').show();
    picMedium.on('load', function () {
      $('.preload').hide();
    });
    $(this).closest('.pic-tabs-wrap').find('.pic-content').attr('href', picLargeSrc);
  });
}
//End Portfolio Tabs


//Begin Google Map
var map;

function initMap() {
  map = new google.maps.Map(document.getElementById('map'), {
    center: { lat: mapCenterY, lng: mapCenterX },
    zoom: mapZoom,
    scrollwheel: false,
    zoomControl: false,
    mapTypeControl: false,
    scaleControl: false,
    streetViewControl: false,
    rotateControl: false
  });

  var marker = new google.maps.Marker({
    position: { lat: mapMarkerY, lng: mapMarkerX },
    map: map,
    icon: mapMarkerIcon
  });
}
//End Google Map

//Individual Radio
function radioValid(){
  var indivPrice = $('.js-price').data('price');
  var addPrice = 0;
  $('.jq-radio').each(function(){
    if ( $(this).hasClass('checked') ) {
      addPrice += $(this).closest('.form-field').data('price');
    }
  });
  addPrice += indivPrice;
  var indivPriceString = addPrice.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
  $('.js-price').text( indivPriceString );
};

$(document).ready(function () {
  if ($('#map').length) {
    initMap();
  }
  giftsSlider('.giftsSlider1', '.giftsPrev1', '.giftsNext1');
  giftsSlider('.giftsSlider2', '.giftsPrev2', '.giftsNext2');
  sharesSlider('.shares-slider');
  portfolioSlider('.portfolio-slider', 4);
  sharesSlider('.delivery-slider');
  reviewsSlider('.reviews-slider', 3);
  giftsSlider('.sidebar-slider', '.blog-prev', '.blog-next');
  tabs();
  portfolioTabs();
  $('.js-figure-popup').fancybox();

  $('.js-juridical').hide();

  $('.js-radio').on('change', function(){
    if( $(this).parent().attr('data-price') && $(this).parent().data('price') >= 0) {
      radioValid();
    }

    //reg
    var radioVal = $(this).val();
    var natural = $('.js-natural');
    var juridical = $('.js-juridical');
    if (radioVal == 'natural') {
      natural.show();
      juridical.hide();
    } else if (radioVal == 'juridical') {
      natural.hide();
      juridical.show();
    }
  });
});

$(window).load(function () {});

$(window).resize(function () {});
//# sourceMappingURL=develop_7.js.map
