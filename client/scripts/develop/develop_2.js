"use strict";

function numberWithSpaces(x) {
  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
}

$(document).on('click','.form-jur .tab-menu',recalculateBasket);

function recalculateBasket() {

  var total = 0;
  $('.basket_table_holder').find('.table-row').each(function () {
    if ($(this).find('.js-number').length) {
      var p = $(this).find('.table-wrap-mobile .price').data('value'); // price
      p = parseInt(p);
      var c = $(this).find('.table-wrap-mobile input.js-number').val();  //count
      c = parseInt(c);
      if(p>0 && c>0) {
        total += parseInt(c) * parseInt(p);
      }
    }
  });
  //delivery stuff
  var limit = parseInt($('.basket_table_holder').find('.delivery-summ').data('limit'));
  var value = parseInt($('.basket_table_holder').find('.delivery-summ').data('value'));

  value = value>0?value:0;
  limit = limit>0?limit:0;

  var delivery_type = $('input[name="form[delivery]"]').val();

  var delivery_value = 0;
  if(delivery_type == 1) {
    if (limit) {
      if (total < limit) {
        delivery_value = value;
      }
    } else {
      delivery_value = value;
    }
  }
  total += delivery_value;
  $('.basket_table_holder').find('.delivery-summ .table-col span').text(numberWithSpaces(delivery_value));
  $('.basket_table_holder').find('.table-summ .table-col span').text(numberWithSpaces(total));

}

//begin combobox
$(function () {
  $.widget("custom.combobox", {
    _create: function _create() {
      this.wrapper = $("<span>").addClass("custom-combobox").insertAfter(this.element);

      this.element.hide();
      this._createAutocomplete();
      this._createShowAllButton();
    },

    _createAutocomplete: function _createAutocomplete() {
      var selected = this.element.children(":selected"),
          value = selected.val() ? selected.text() : "";

      this.input = $("<input>").appendTo(this.wrapper).val(value).attr("title", "").addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left").autocomplete({
        delay: 0,
        minLength: 0,
        source: $.proxy(this, "_source")
      }).tooltip({
        classes: {
          "ui-tooltip": "ui-state-highlight"
        }
      });

      this._on(this.input, {
        autocompleteselect: function autocompleteselect(event, ui) {
          ui.item.option.selected = true;
          this._trigger("select", event, {
            item: ui.item.option
          });
        },

        autocompletechange: "_removeIfInvalid"
      });
    },

    _createShowAllButton: function _createShowAllButton() {
      var input = this.input,
          wasOpen = false;

      $("<a>").attr("tabIndex", -1).attr("title", "Show All Items").tooltip().appendTo(this.wrapper).button({
        icons: {
          primary: "ui-icon-triangle-1-s"
        },
        text: false
      }).removeClass("ui-corner-all").addClass("custom-combobox-toggle ui-corner-right").on("mousedown", function () {
        wasOpen = input.autocomplete("widget").is(":visible");
      }).on("click", function () {
        input.trigger("focus");

        // Close if already visible
        if (wasOpen) {
          return;
        }

        // Pass empty string as value to search for, displaying all results
        input.autocomplete("search", "");
      });
    },

    _source: function _source(request, response) {
      var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
      response(this.element.children("option").map(function () {
        var text = $(this).text();
        if (this.value && (!request.term || matcher.test(text))) return {
          label: text,
          value: text,
          option: this
        };
      }));
    },

    _removeIfInvalid: function _removeIfInvalid(event, ui) {

      // Selected an item, nothing to do
      if (ui.item) {
        return;
      }

      // Search for a match (case-insensitive)
      var value = this.input.val(),
          valueLowerCase = value.toLowerCase(),
          valid = false;
      this.element.children("option").each(function () {
        if ($(this).text().toLowerCase() === valueLowerCase) {
          this.selected = valid = true;
          return false;
        }
      });

      // Found a match, nothing to do
      if (valid) {
        return;
      }

      // Remove invalid value
      this.input.val("").attr("title", value + " didn't match any item").tooltip("open");
      this.element.val("");
      this._delay(function () {
        this.input.tooltip("close").attr("title", "");
      }, 2500);
      this.input.autocomplete("instance").term = "";
    },

    _destroy: function _destroy() {
      this.wrapper.remove();
      this.element.show();
    }
  });

  $("#combobox").combobox();
  $("#toggle").on("click", function () {
    $("#combobox").toggle();
  });
});
//end combobox

function fileSelect() {
  var fileExtentionRange = '.jpg .jpeg .tif .tiff .png .gif';
  var MAX_SIZE = 1; // MB
  var input_name = $('input[type="file"]').attr('name');

  $(document).on('change', '.feedback-form .form-row :file', function () {
    var input = $(this);
    setTimeout(function() {
        input.attr('name', input_name);
    }, 500);
    if (navigator.appVersion.indexOf("MSIE") != -1) {
      // IE
      var label = input.val();
      input.trigger('fileselect', [1, label, 0]);
    } else {
      var label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
      var numFiles = input.get(0).files ? input.get(0).files.length : 1;
      var size = input.get(0).files[0].size;
      input.trigger('fileselect', [numFiles, label, size]);
    }
  });

  $('.feedback-form .form-row :file').on('fileselect', function (event, numFiles, label, size) {
    $(this).attr('name', 'file'); // allow upload.
    var postfix = label.substr(label.lastIndexOf('.'));
    if (fileExtentionRange.indexOf(postfix.toLowerCase()) > -1) {
      if (size > 1024 * 1024 * MAX_SIZE) {
        $.fancybox.open({ src: '#error-size' });
        $('.feedback-form .form-row :file').removeAttr('name'); // cancel upload file.
        $('.feedback-form .form-row .jq-file__name').text("");
      } else {
        $('.feedback-form .form-row .jq-file__name').removeClass('error');
      }
    } else {
      $.fancybox.open({ src: '#error-format' });
      $('.feedback-form .form-row :file').removeAttr('name'); // cancel upload file.
      $('.feedback-form .form-row .jq-file__name').text("");
    }
  });
}

function busketCount(selector) {
  $(selector).each( function () {
    var val = parseInt($(this).val());
    if (typeof val != 'number' || val < 1) {
      $(this).val(1);
    }
  });
  var price = 0;
  var count = 1;
  $(selector).find('input').on('change', function () {
    //debugger;
    if ($(this).val() <= 0) {
      $(this).val(1);
    } else if ($(this).val() > 1000) {
      $(this).val(1000);
    }
    price = $(this).closest('.table-wrap-mobile').find('.price').data('value');///text().replace(/\s+/g, '');
    count = $(this).val();

    //change count ajax
    var url = $(this).data('url');
    if(url){
      $.ajax({
        url: url,
        data: {
          count: count
        },
        method: 'POST',
        datatype: 'json',
        success: function success(data) {
          if (typeof data.params == 'object') {
            updateHeaderBasket(data.params);
          }
          if(typeof data.error == 'string')
            $.fancybox.open(data.error);
        },
        error: function (e) {
          $.fancybox.open('Ошибка добавления товара, перезагрузите страницу и попробуйте еще раз.');
        }
      });
    }

    $(this).closest('.table-wrap-mobile').find('.summ span').text(numberWithSpaces(price * count));

    recalculateBasket();
  });
}

var text = '';

//Main-card Slider
function cardSlider(selector) {
    $(selector).slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      dots: false,
      arrows: false,
      infinite: false,
      draggable: false,
      asNavFor: '.bouquet-sider-nav'

    });
};

function cardSliderNav(selector) {
  $(selector).slick({
    slidesToShow: 3,
    slidesToScroll: 1,
    asNavFor: '.bouquet-sider',
    dots: false,
    arrows: false,
    infinite: false,
    focusOnSelect: true
  });
};

$(document).ready(function () {
  $('.js_select').styler();
  $('.js_select-dotted').styler();
  $('.js-beautiful').styler();
  $('.js-filter').styler();
  $('.js-checkbox').styler();
  $('.js-number').styler();
  $('.js-radio').styler();
  $('.js-file').styler();
  $('.js-combobox').combobox();

  if($('.bouquet-sider').length > 0)
      cardSlider('.bouquet-sider');

  if($('.bouquet-sider-nav').length > 0)
      cardSliderNav('.bouquet-sider-nav');

  recalculateBasket();

  $('.error-wrapper .link').click(function(e) {
    e.preventDefault();
    $(this).next('div').find('.info').slideToggle();
  });

  $('.wrap-category .sales-list ul li').each(function() {
    text = $(this).find('.sale-item .visible-overlay p').text();
    var sliced = text.slice(0,60);
    if (sliced.length < text.length) {
      sliced += '...';
    }
    $(this).find('.sale-item .visible-overlay p').text(sliced);
  });

  //filter begin

  $('.filter-list > li').on('mouseover', function () {
    var dropdown = $(this).find('ul');
    $('.filter-list li ul').hide();
    if (dropdown.hasClass('column-2')) {
      dropdown.css("display", "flex");
    } else {
      dropdown.show();
    }
  });

  var filterItem = $('.dropdown li');
  filterItem.on('click', function (e) {
    // e.preventDefault();
    var filterItemTitle = $(this).find('a').text();
    var filterTitle = $(this).closest('ul').prev('span');
    $(this).closest('ul').parent('li').addClass('active');
    filterTitle.text(filterItemTitle);
    $('.filter-list li ul').hide();
  });

  $('.filter-list > li, .dropdown li').on('mouseleave', function () {
    $('.filter-list li ul').hide();
  });
  //filter end

  fileSelect();
  busketCount('.js-number');

  $('.bouquet-sider a').fancybox();

  var mon = new Date().getMonth() + 1;
  if (mon < 10) {
    mon = '0' + mon;
  }
  $(".datepicker").val(new Date().getDate() + '.' + mon + '.' + new Date().getFullYear());
  $(".datepicker").datepicker({
    dateFormat: 'dd.mm.yy',
    minDate: +1,
    monthNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
    dayNamesMin: ['Вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб']
  });

  $(".datepicker").datepicker("setDate", "1");

  $('.wrap-category .sorting .sort-item select').change(function () {
    $(this).closest('form').submit();
  });

  // removing product from busket

  if ($('.wrap-busket').length) {
    $('.el-table .delete').click(function (e) {
      var url = $(this).data('url');
      if(url){
        $.ajax({
          url: url,
          data: {},
          method: 'POST',
          datatype: 'json',
          success: function success(data) {
            debugger;
            if(typeof data.items == 'object' && data.items.length == 0){
              window.location.reload();
            }

            if (typeof data.params == 'object') {
              updateHeaderBasket(data.params);
            }

            if(typeof data.error == 'string')
              $.fancybox.open(data.error);
          },
          error: function (e) {
            $.fancybox.open('Ошибка добавления товара, перезагрузите страницу и попробуйте еще раз.');
          }
        });
      }

      $(this).closest('.table-row').remove();
      // recalculate total basket
      recalculateBasket();

    });
  }

  // /removing product from busket

  // adding product to busket
  $('.to-busket').click(function (e) {
    e.preventDefault();

    var id = $(this).attr('data-id');

    $.ajax({
      url: adding_to_busket,
      data: { 'add_to_busket': id, "format": "_json" },
      method: 'GET',
      success: function success(data) {
        var count = data.count;
        var price = data.price;
        $('.main-wrap .products-amount').text(count + " Товаров");
        $('.main-wrap .products-price').text(price + " руб");
        $.fancybox.open({
          src: '#basket_success'
        });
      }
    });
  });

  // /adding product to busket

  if ($('.wrap-cabinet').length) {
  	var indexTab = 0;
    	$('.wrap-cabinet .cabinet-content .navbar .cabinet-navbar li').each(function() {
  	  	if ($(this).find('a').hasClass('active')) {
  	  		indexTab = $(this).index();
  		}
  	});

  	$('.wrap-cabinet .cabinet-content .navbar .cabinet-navbar li').eq(indexTab).find('a').addClass('active');
  	$('.wrap-cabinet .cabinet-content .cabinet .cabinet-tab').eq(indexTab).addClass('active');

    $('.wrap-cabinet .cabinet-content .navbar .cabinet-navbar li a').click(function (e) {
      if (!$(this).hasClass('escape')) {
        e.preventDefault();
        $(this).closest('.cabinet-navbar').find('li a').removeClass('active');
        $(this).addClass('active');

        var index = $(this).closest('li').index();
        $(this).closest('.cabinet-content').find('.cabinet .cabinet-tab').removeClass('active');
        $(this).closest('.cabinet-content').find('.cabinet .cabinet-tab').eq(index).addClass('active');

        if ($(window).width() < 768) {
          $(this).closest('.cabinet-navbar').slideUp();
          $(this).closest('.cabinet-navbar').prev('.burger').toggleClass('active');
        }
      }
    });

    $('.wrap-cabinet .cabinet-content .cabinet .cabinet-tab .cab-link').click(function (e) {
      if (!$(this).hasClass('escape')) {
        e.preventDefault();
        var index = $(this).index();
        $(this).closest('.cabinet-content').find('.navbar .cabinet-navbar li a').removeClass('active');
        $(this).closest('.cabinet-content').find('.navbar .cabinet-navbar li').eq(index + 1).find('a').addClass('active');

        $(this).closest('.cabinet-content').find('.cabinet .cabinet-tab').removeClass('active');
        $(this).closest('.cabinet-content').find('.cabinet .cabinet-tab').eq(index + 1).addClass('active');
      }
    });
  }

  $('.entrace-autorised a').click(function (e) {
    e.preventDefault();
    $(this).next('.autorised-dropdown').slideToggle();
  });

  $('.visible-price').click(function () {
    $(this).next('.advertise-content').toggleClass('active');
    return false;
  });

  $('.history-col .details').click(function (e) {
    e.preventDefault();
    $(this).closest('.history-table').find('.history-row .details').not($(this)).removeClass('active');
    $(this).toggleClass('active');

    var next = $(this).closest('.history-row').next('.el-table');
    $(this).closest('.history-table').find('.el-table').not(next).slideUp();
    $(this).closest('.history-row').next('.el-table').slideToggle();
  });

  $('.burger-header').fancybox({
    smallBtn: true,
    buttons: false,
    keyboard: false,
    closeClickOutside: true
  });

  $('.fancybox_open').fancybox({
    smallBtn: true,
    buttons: false,
    keyboard: false,
    closeClickOutside: true
  });

  $('.buy').fancybox({
    smallBtn: true,
    buttons: false,
    keyboard: false,
    closeClickOutside: true
  });

  $('.to-news').fancybox({
    smallBtn: true,
    buttons: false,
    keyboard: false,
    closeClickOutside: true
  });

  $('.js-tel').fancybox({
    smallBtn: true,
    buttons: false,
    keyboard: false,
    closeClickOutside: true
  });

  $('.custom-combobox-input').focus(function () {
    $(this).closest('.widget-wrap').addClass('active');
  });

  if ($('.select-wrap').length) {
    $('.jq-selectbox__select-text').each(function() {
      if ($(this).text().length > 0) {
        $(this).closest('.select-wrap').addClass('active');
      }
    })
  }

  $('.jq-selectbox li').click(function () {
    if ($('.js-beautiful, .js-filter').hasClass('changed')) {
      $(this).closest('.select-wrap').addClass('active');
    } else {
      $(this).closest('.select-wrap').removeClass('active');
    }
  });

  $('#bonus-popup .close').click(function (e) {
    e.preventDefault();
    $.fancybox.close('#bonus-popup');
  });

  $('.bouquet__addition ul li').eq(0).find('a').addClass('active');
  $('.bouquet__addition .tabs .tab-content').eq(0).addClass('active');
  $('.wrap-busket-jur .form-jur .form-col ul li').eq(0).find('a').addClass('active');
  $('.wrap-busket-jur .form-jur .form-col .tab-content .content').eq(0).addClass('active');

  $('.bouquet__addition .tabs-list li a').click(function (e) {
    e.preventDefault();

    var this_index = $(this).closest('li').index();
    $(this).closest('ul').find('li a').removeClass('active');
    $(this).addClass('active');

    $(this).closest('ul').next('.tabs').find('.tab-content').removeClass('active');
    $(this).closest('ul').next('.tabs').find('.tab-content').eq(this_index).addClass('active');
  });
  $('.wrap-busket-jur .form-jur .form-col ul li a').click(function (e) {
    e.preventDefault();

    var this_index = $(this).closest('li').index();
    $(this).closest('ul').find('li a').removeClass('active');
    $(this).addClass('active');

    $(this).closest('ul').next('.tab-content').find('.content').removeClass('active');
    $(this).closest('ul').next('.tab-content').find('.content').eq(this_index).addClass('active');
  });

  if ($(window).width() <= 768) {
    $('.footer .footer-content .menu .footer-col .ttl').click(function () {
      $(this).closest('.menu').find('.footer-col').removeClass('current');
      $(this).closest('.footer-col').addClass('current');
      $(this).closest('.menu').find('.footer-col').each(function () {
        if (!$(this).hasClass('current')) {
          $(this).find('ul').slideUp();
        }
      });
      $(this).next('ul').slideToggle();
    });
  }

  if ($(window).width() < 768) {
    $('.burger').click(function (e) {
      e.preventDefault();
      $(this).toggleClass('active');
      $(this).next('.cabinet-navbar').slideToggle();
    });
  }
});

jQuery(document).click(function (event) {
  if ($(event.target).closest(".advertising-sidebar").length) return;
  jQuery(".advertise-content").removeClass("active");

  event.stopPropagation();
});

$(window).load(function () {});

$(window).resize(function () {});
//# sourceMappingURL=develop_2.js.map
