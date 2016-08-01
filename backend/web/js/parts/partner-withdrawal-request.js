/**
 * Created by zhenya on 11.5.16.
 */
function findPartnerAvailableAmount()
{
    var
        container = $('#partner-a-amount-purse'),
        currID = $('#partnerwithdrawalrequest-currency_id').val(),
        date = $('#partnerwithdrawalrequest-date').val(),
        value = $('#partnerwithdrawalrequest-partner_id').val();
    $.ajax({
        type: "POST",
        cache: false,
        url:URL_PARTNER_PURSE_A,
        dataType: "json",
        data: {pk:value,date:date,currID:currID},
        success: function(msg){
            container.html(msg);
        },
        error: function(msg){
            container.html('');
            addErrorNotify('Получение кошелька партнера', 'Не удалось выполнить запрос!');
            return false;
        }
    });
}

$('#partnerwithdrawalrequest-partner_id,#partnerwithdrawalrequest-currency_id,#partnerwithdrawalrequest-date').on('change',findPartnerAvailableAmount);

$(function(){
    "use strict";
    $('#partnerwithdrawalrequest-amount').on('change',function(){
       amountFormatter(this);
        var amount = convertAmountToValid($(this).val());
        $(this).siblings('.amountInfo').remove();
        $(this).after( $('<div></div>',{class:'amountInfo'}).html(convertAmountToInvalid(amount*10000) + ' BYR'));
    });
    amountFormatter('#partnerwithdrawalrequest-amount');
});
