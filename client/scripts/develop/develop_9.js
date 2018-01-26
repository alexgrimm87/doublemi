function showHeaderBasket() {
    var route = $('#header_basket').data('update');
    if(route.length>0){
        $.ajax({
            url: route,
            method: 'GET',
            dataType: 'json',
            success: function success(data) {
                if(typeof data.params === 'object'){
                    updateHeaderBasket(data.params);
                }
            },
            error: function(){
                console.error('Update basket error');
            }
        });
    }
}

function updateHeaderBasket(params) {
    $('#header_basket').show();
    var str = ( params.count?params.count:0) + ' ' + (params.str?params.str:'Товаров');
    $('#header_basket .products-amount').html(str);
    $('#header_basket .products-price').html( params.price_format ? params.price_format : '0 руб.' );
}

function formsClear() {
    $('form').trigger("reset");
    setTimeout(function() {
        $('.js-radio').prop('checked',false).removeClass('checked');
        $('.js-checkbox').prop('checked',false).removeClass('checked');
        $('.datepicker').datepicker( 'setDate', new Date() );
    }, 1000);
}

function recalculateDeliveryDate() {
    if($('#delivery_date_input').length>0){
        var d = $('#delivery_date_input_date').val();
        var t = $('#delivery_date_input_time').val();
        $('#delivery_date_input').val(t + ' ' + d);
    }
}

function imageErrorHandler(e) {
    $(this).attr('src','/placeholder.svg');
}

function registerFormToggle(){
    var val = $('#fos_user_registration_form_type').val();
    if(val != 'uri'){
        $('.registration-form .juridical_row').hide();
    } else {
        $('.registration-form .juridical_row').show();
    }
}

$(document).on('change','#delivery_date_input_date, #delivery_date_input_time', recalculateDeliveryDate);

$(document).on('change','#create_registration', function () {
    if ($(this).is(":checked")) {
        $('.hidden_registration').show(200);
    } else {
        $('.hidden_registration').hide(200);
    }
    //one-click-buy-form
});


$(document).on('click','.delivery_hidden_input_trigger',function () {
    var val = parseInt($(this).data('value'));
    if(val>0){
        $('#delivery_hidden_input').val(val);
    }
    if(val == 2){
        $('#alone-styler').prop('checked',true).addClass('checked');
    } else {
        $('#alone-styler').prop('checked',false).removeClass('checked');
    }
});

$(document).on('reset','form',function () {
    if($(this).find('.size_list_wrap input').length>0){
        $('.price_str_viewer').html($(this).find('.size_list_wrap input').first().data('price'));
    }
});

$(document).on('change','.size_list_handler input', function () {
    $('.price_str_viewer').html($(this).data('price'));
});

$(document).on('click','.add_2_basket',function () {
    if($(this).closest('form').length){

        var thisForm = $(this).closest('form');
        var formSur = thisForm.serialize();

        $.ajax({
            url: thisForm.attr('action'),
            data: formSur,
            method: 'POST',
            datatype: 'json',
            success: function success(data) {
                if (typeof data.params == 'object') {
                    setTimeout(function() {
                        $('.js-radio').prop('checked',false).removeClass('checked');
                        $('.js-checkbox').prop('checked',false).removeClass('checked');
                        $('.datepicker').datepicker( 'setDate', new Date() );
                    }, 3000);

                    updateHeaderBasket(data.params);
                    popNext("#basket_success", "call-popup");
                }
                if(typeof data.error == 'string'){
                    $.fancybox.open(fancyboxTextWrapper(data.error));
                }
                thisForm.trigger("reset");
            },
            error: function (e) {
                $.fancybox.open(fancyboxTextWrapper('Ошибка добавления товара, перезагрузите страницу и попробуйте еще раз.'));
            }
        });
    }
    return false;
});

$(document).on('change','input[name=person][type=radio]', function(){
    $('#fos_user_registration_form_type').val($(this).val());
    registerFormToggle();
});

$(document).on('change',"input[name='form[catalogPayment]']", function(){
    basketCheckUriPayment();
});

function basketCheckUriPayment() {
    if($('.form-jur').length > 0){
        if($("input[name='form[catalogPayment]']:checked").hasClass('uri_info')){ // show uri blocks
           $('.uri_info_form_block').show();
        } else { //hide uri blocks
            $('.uri_info_form_block').hide();
        }
    }
}

$(document).ready(function () {
    showHeaderBasket();
    recalculateDeliveryDate();
    basketCheckUriPayment();

    if( $('.registration-form').length > 0)
        registerFormToggle();
    $('img').bind('error', imageErrorHandler);

});