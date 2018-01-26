'use strict';

/**
* Main validation form
* @param {form} jquery obj - Form
* @param {options} obj - object width params
*/
function validate(form, options) {
    var setings = {
        errorFunction: null,
        submitFunction: null,
        highlightFunction: null,
        unhighlightFunction: null
    };
    $.extend(setings, options);

    var $form = $(form);

    if ($form.length && $form.attr('novalidate') === undefined) {
        if(typeof options.blockSubmit != 'boolean' && !options.blockSubmit) {
            $form.on('submit', function (e) {
                e.preventDefault();
            });
        }

        $form.validate({
            errorClass: 'errorText',
            focusCleanup: true,
            focusInvalid: false,
            invalidHandler: function invalidHandler(event, validator) {
                if (typeof setings.errorFunction === 'function') {
                    setings.errorFunction(form);
                }
            },
            errorPlacement: function errorPlacement(error, element) {
                error.appendTo(element.closest('.form_input'));
            },
            highlight: function highlight(element, errorClass, validClass) {
                $(element).addClass('error');
                $(element).closest('.form_row').addClass('error').removeClass('valid');
                if (typeof setings.highlightFunction === 'function') {
                    setings.highlightFunction(form);
                }
            },
            unhighlight: function unhighlight(element, errorClass, validClass) {
                $(element).removeClass('error');
                if ($(element).closest('.form_row').is('.error')) {
                    $(element).closest('.form_row').removeClass('error').addClass('valid');
                }
                if (typeof setings.unhighlightFunction === 'function') {
                    setings.unhighlightFunction(form);
                }
            },
            submitHandler: function submitHandler(form) {
                if (typeof setings.submitFunction === 'function') {
                    setings.submitFunction(form);
                } else {
                    $form[0].submit();
                }
            }
        });

        $('[required]', $form).each(function () {
            $(this).rules("add", {
                required: true,
                messages: {
                    required: "Вы пропустили"
                }
            });
        });

        if ($('[type="email"]', $form).length) {
            $('[type="email"]', $form).rules("add", {
                messages: {
                    email: "Невалидный email"
                }
            });
        }

        if ($('.tel-mask[required]', $form).length) {
            $('.tel-mask[required]', $form).rules("add", {
                messages: {
                    required: "Введите номер мобильного телефона."
                }
            });
        }

        $('[type="password"]', $form).each(function () {
            if ($(this).is("#re_password") == true) {
                $(this).rules("add", {
                    minlength: 3,
                    equalTo: "#password",
                    messages: {
                        equalTo: "Неверный пароль.",
                        minlength: "Недостаточно символов."
                    }
                });
            }
        });
    }
}

function fancyboxTextWrapper(str) {
    return '<div><p></p><p>' + str + '</p></div>';
}

/**
 * Sending form with a call popup
 * @param {form} string - Form
 */
function orderSubmit(form) {
    var thisForm = $(form);
    var formSur = thisForm.serializeArray().concat(oneClickObject);
    oneClickObject = [];
    $.ajax({
        url: thisForm.attr('action'),
        data: formSur,
        method: 'POST',
        datatype: 'json',
        success: function success(data) {
            if(typeof data.redirection == 'string') {
                window.location.href = data.redirection;
            } else if(typeof data.message == 'string'){
                //redirect
                $.fancybox.open(fancyboxTextWrapper(data.message), {
                    afterClose: function () {
                        if(typeof data.redirect == 'string'){
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                    }
                })
            } else if(typeof data.error == 'string') {
                $.fancybox.open(fancyboxTextWrapper(data.error));
            }
        },
        error: function () {
            $.fancybox.open(fancyboxTextWrapper('Ошибка отправки запроса, перезагрузите страницу и попробуйте еще раз.'));
        }
    });
}

function subscribeCall(form) {
    var thisForm = $(form);
    var formSur = thisForm.serialize();

    $.ajax({
        url: thisForm.attr('action'),
        data: formSur,
        method: 'POST',
        success: function success(data) {

            if (data.trim() == 'true') {
                thisForm.trigger("reset");
                setTimeout(function() {
                    $('.js-radio').prop('checked',false).removeClass('checked');
                    $('.datepicker').datepicker( 'setDate', new Date() );
                }, 3000)

                $.fancybox.close();
                popNext("#call_success", "call-popup");
            } else {
                thisForm.trigger('reset');
            }
        }
    });
}

function profileCall(form) {
    $(form).submit();
    //var formSur = thisForm.serialize();
}

function individualValidationCall(form) {
    var thisForm = $(form);
    var formSur = thisForm.serialize();

    $.ajax({
        url: thisForm.attr('action'),
        data: formSur,
        method: 'POST',
        dataType: 'json',
        success: function success(data) {
            if (data == 'true') {
                thisForm.trigger("reset");

                setTimeout(function () {
                    $('.js-radio').prop('checked', false).removeClass('checked');
                    $('.datepicker').datepicker('setDate', new Date());
                }, 3000);
                var priceIndivid = $('.js-price').data('price');
                var priceIndividString = priceIndivid.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
                $('.js-price').text(priceIndividString);

                $.fancybox.close();
                popNext("#call_success", "call-popup");
            } else if(typeof data.link == 'string'){
                window.location.href = data.link;
            } else {
                thisForm.trigger('reset');
            }
        }
    });
}

/**
* Sending form with a call popup
* @param {form} string - Form
*/
function validationCall(form) {

    var thisForm = $(form);
    var formSur = thisForm.serialize();

    $.ajax({
        url: thisForm.attr('action'),
        data: formSur,
        method: 'POST',
        success: function success(data) {
            if (data.trim() == 'true') {
                thisForm.trigger("reset");

                setTimeout(function() {
                  $('.js-radio').prop('checked',false).removeClass('checked');
                  $('.datepicker').datepicker( 'setDate', new Date() );
                }, 3000)
                var priceIndivid = $('.js-price').data('price');
                var priceIndividString = priceIndivid.toString().replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ');
                $('.js-price').text( priceIndividString );

                $.fancybox.close();
                popNext("#call_success", "call-popup");
            } else {
                thisForm.trigger('reset');
            }
        }
    });
}

function validationCall1(form) {

    var thisForm = $(form);
    var formSur = thisForm.serialize();

    $.ajax({
        url: thisForm.attr('action'),
        data: formSur,
        method: 'GET',
        success: function success(data) {
            $('.wrap-category .sales-list').append(data);
        }
    });
}

/**
* Sending form with a call popup
* @param {popupId} string - Id form, that we show
* @param {popupWrap} string - Name of class, for wrapping popup width form
*/
function popNext(popupId, popupWrap) {

    $.fancybox.open({
        src: popupId,
        closeClickOutside: true,
        type: '',
        opts: {
            baseClass: popupWrap || '',
            afterClose: function afterClose() {
                $('form').trigger("reset");
                clearTimeout(timer);
            }
        }
    });

    var timer = null;

    timer = setTimeout(function () {
        $('form').trigger("reset");
        $.fancybox.close();
    }, 2000);
}

/**
* Submitting the form with the file
* @param {form} string - Form
* не использовать input[type="file"] в форме и не забыть дописать форме enctype="multipart/form-data"
*/
function validationCallDocument(form) {

    var thisForm = $(form);
    var formData = new FormData($(form)[0]);

    formData.append(' ', thisForm.find('input[type=file]')[0].files[0]);

    $.ajax({
        url: thisForm.attr('action'),
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        cache: false,
        success: function success(response) {
            thisForm.trigger("reset");
            setTimeout(function() {
                $('input[type="file"]').trigger('refresh');
            }, 1);
            popNext("#call_success", "call-popup");
            setTimeout(function() {
                location.reload();
            }, 2000);
        }
    });
}

/**
* Submitting the form with the files
* @param {form} string - Form
* не использовать input[type="file"] в форме и не забыть дописать форме enctype="multipart/form-data"
*/
function validationCallDocuments(form) {

    var thisForm = $(form);
    var formData = new FormData($(form)[0]);

    $.each(thisForm.find('input[type="file"]')[0].files, function (index, file) {
        formData.append('file[' + index + ']', file);
    });

    $.ajax({
        url: thisForm.attr('action'),
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        cache: false,
        success: function success(response) {
            thisForm.trigger("reset");
            popNext("#call_success", "call-popup");
        }
    });
}

/**
* Mask on input(russian telephone)
*/
function Maskedinput() {
    if ($('.tel-mask')) {
        $('.tel-mask').mask('+9 (999) 999-99-99');
    }
}

/**
* Fansybox on form
*/
function fancyboxForm() {
    $('.fancybox-form').fancybox({
        baseClass: 'fancybox-form'
    });
}

var oneClickObject = [];
$(document).on('click','.one_click_buy',function () {

    if($(this).closest('form').length){
        oneClickObject = $(this).closest('form').serializeArray();
    }

    $.fancybox.open({
        src: '#one-click-buy-popup',
        closeClickOutside: true,
        type: '',
        opts: {
            baseClass: 'call-popup' || '',
            afterClose: function afterClose() {
                $('form').trigger("reset");
            }
        }
    });

});

$(document).ready(function () {

    validate('#call-popup .contact-form', { submitFunction: validationCall });
    validate('.events-form', { submitFunction: validationCall });
    validate('.contacts-form', { submitFunction: validationCall });
    validate('.feedback-form form', { submitFunction: validationCallDocument });
    //validate('.form-fiz', { submitFunction: validationCall });
    validate('.form-jur', { submitFunction: orderSubmit });
    validate('.one-click-buy-form', { submitFunction: orderSubmit });
    validate('.forgot-form', { submitFunction: validationCall });
    //validate('.entrance-form', { submitFunction: validationCall });
    //validate('.registration-form', { submitFunction: validationCall });
    validate('.change-password', { submitFunction: profileCall, blockSubmit: true });
    validate('.fix-profil', { submitFunction: profileCall, blockSubmit: true });
    validate('.letters-form', { submitFunction: validationCall });
    validate('.individual-form', { submitFunction: individualValidationCall });
    validate('.sidebar-form', { submitFunction: validationCall });

    validate('.subscribe-popup', { submitFunction: subscribeCall });

    $('.sorting select').on('change', function () {
        var form = $(this).closest('form');
        validationCall(form);
    });

    Maskedinput();
    fancyboxForm();
});
//# sourceMappingURL=validate_script.js.map
