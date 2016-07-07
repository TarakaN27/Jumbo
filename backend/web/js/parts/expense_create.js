/**
 * Created by zhenya on 24.6.16.
 */
//document ready
function showByrInfo(this1)
{
    $(this1).siblings('.amountInfo').remove();
    if($('#expense-currency_id').val() != 2)
        return false;

    var
        amount = convertAmountToValid($(this1).val());

    $(this1).after( $('<div></div>',{class:'amountInfo'}).html(convertAmountToInvalid(amount*10000) + ' BYR'));
}
$(function(){
    $("#expense-pay_summ").on('change',function(){
        "use strict";
        amountFormatter(this);
        showByrInfo(this);
    });
    $('#expense-currency_id').on('change',function(){
        "use strict";
        showByrInfo("#expense-pay_summ");
    });
    amountFormatter("#expense-pay_summ");
    showByrInfo("#expense-pay_summ");
});