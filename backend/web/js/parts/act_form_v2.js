/**
 * Created by zhenya on 20.5.16.
 */
"use strict";
function loadPayments()
{
    var
        container = $('#paymentsBlock'),
        iCUser = $('#actform-icuser').val(),
        iLegalPerson = $('#actform-ilegalperson').val(),
        checkBoxs = container.find('input[type="checkbox"]'),
        preloadEntity = getPreloaderEntity('paymentPreloader');

    if(checkBoxs.lenght > 0)
    {
        checkBoxs.prop('checked',false);
        checkBoxs.trigger('change');
    }
    container.html(preloadEntity);                          //set preloader
    console.log('ads');
    if(customEmpty(iCUser) || customEmpty(iLegalPerson))
    {
        console.log('pnf');
        container.html('Платежи не найдены');
        return false;
    }
    console.log('asda');
    $.ajax({
        type: "POST",
        cache: false,
        url:URL_LOAD_ACTS_PAYMENTS,
        dataType: "json",
        data: {iCUser:iCUser,iLegalPerson:iLegalPerson},
        success: function(data){
            container.html(data.content);
        },
        error: function(msg){
            addErrorNotify('Получение платежей', 'Не удалось выполнить запрос!');
            container.html('Платежи не найдены');
            return false;
        }
    });

    return true;
}

$(function(){
    $('#actform-ilegalperson,#actform-icuser').on('change',loadPayments);
});