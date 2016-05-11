/**
 * Created by zhenya on 29.4.16.
 */
'use strict';
/**
 * Hide preloader and show content
 * @returns {boolean}
 */
function hidePPreloader()
{
    $('.pMainContent').removeClass('hide');
    $('#preloader').addClass('hide');
    return true;
}
/**
 * Hide content and show preloader
 * @returns {boolean}
 */
function showPPreloader()
{
    $('.pMainContent').addClass('hide');
    $('#preloader').removeClass('hide');
    return true;
}
/**
 * Fill select box by options tags
 * @param selector
 * @param options
 * @returns {boolean}
 */
function fillConditionSelect(selector,options)
{
    var
        ul = $(selector);

    ul.find('option:not([value=""])').remove();

    $.each(options, function( index, value ) {
        let
            li = $(document.createElement('option'));
        li.attr('value',value.id);
        li.text(value.name);
        ul.append(li);
    });
    return true;
}
/**
 *
 * @returns {boolean}
 * @constructor
 */
function PWFindCondition()
{
        var
            bClear = true,
            payDate = $('#pay_date').val(),
            currencyID = $('#currency_id').val(),
            line = $(this).attr('id');

        if(line != undefined)
        {
            let
                lineCopy = line,
                arrTmp = lineCopy.split('-'),
                key = arrTmp.pop();

            if(payDate == undefined || currencyID == undefined || payDate == '' || currencyID == '')
             {
                 addErrorNotify('Получение подходящих условий', 'Не заданы дата или валюта платежа!');
                 if(key != undefined && key != '')
                 {
                     $('#'+line.replace(key,'conditionid')).val('');
                     fillConditionSelect('#'+line.replace(key,'conditionid'),[]);
                 }
                 return false;
             }

            if(key != undefined && key != '')
            {
                let
                    amount = $('#'+line.replace(key,'amount')),
                    cntr = $('#'+line.replace(key,'contractorid')),
                    service = $('#'+line.replace(key,'serviceid')),
                    legalPerson = $('#'+line.replace(key,'legalpersonid'));

                if(
                    amount.val() == undefined ||
                    cntr.val() == undefined ||
                    service.val() == undefined ||
                    legalPerson.val() == undefined ||
                    amount.val() == '' ||
                    cntr.val() == '' ||
                    service.val() == '' ||
                    legalPerson.val() == ''
                ){
                    if(key != undefined && key != '')
                    {
                        $('#'+line.replace(key,'conditionid')).val('');
                        fillConditionSelect('#'+line.replace(key,'conditionid'),[]);
                    }
                    return false;
                }
                showPPreloader();
                $.ajax({
                    type: "POST",
                    cache: false,
                    url:FIND_CONDITION_URL,
                    dataType: "json",
                    data: {
                        iservId:service.val(),
                        iLegalId:legalPerson.val(),
                        amount:amount.val(),
                        iCuserId:cntr.val(),
                        iPayDate:payDate,
                        iCurrID:currencyID
                    },
                    success: function(msg){
                        fillConditionSelect('#'+line.replace(key,'conditionid'),msg);
                        hidePPreloader();
                    },
                    error: function(msg){
                        hidePPreloader();
                        $('#'+line.replace(key,'conditionid')).val('');
                        fillConditionSelect('#'+line.replace(key,'conditionid'),[]);
                        addErrorNotify('Получение подходящих условий', 'Не удалось выполнить запрос!');
                        return false;
                    }
                });
            }
        }else{
            addErrorNotify('Получение подходящих условий', 'Не удалось получить селектор!');
        }

    return false;
}
//Bind events
$(function () {
    $('#dynamic-form').on('change','.change-event',PWFindCondition);        //bind function to change event
    hidePPreloader();                       //when document is ready hide preloader and show content
    $('#dynamic-form .change-event').trigger('change');
});


