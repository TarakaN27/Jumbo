/**
 * Created by zhenya on 24.6.16.
 */
function amountFormatter()
{
    var
        amount = $('#expense-pay_summ').val();
    if(amount == '')
        amount = '0';

    amount = amount.replace(/\s+/g, '');
    amount = amount.replace(/,/g,'.');
    amount = parseFloat(amount);
    amount = accounting.formatNumber(amount, 2, " ");
    amount = amount.replace(/\./g,',');
    $('#expense-pay_summ').val(amount);
}

//document ready
$(function(){
    $("#expense-pay_summ").on('change',amountFormatter);
    amountFormatter();
});