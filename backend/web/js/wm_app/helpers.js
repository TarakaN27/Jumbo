/**
 * Webmart Soft
 * Created by zhenya on 18.09.15.
 * Useful scripts
 * Only native JavaScripts!!!!!
 */
"use strict";
/**
 * Get number from sting
 * @param str
 * @returns {Number}
 */
function parseNum(str){ return parseFloat(String(str).match(/\d+(?:\.\d+)?/g, '') || 0, 10); }
function amountFormatter(itemSelector)
{
    var
        amount = $(itemSelector).val();
    if(amount == '')
        amount = '0';
    $(itemSelector).val(convertAmountToInvalid(amount));
}
function convertAmountToInvalid(amount)
{
    if(isNaN(amount))
        return amount;

    if($.isNumeric(amount)) {
        console.log('to string');
        amount = amount.toString();
    }
    console.log(amount);
    amount = amount.replace(/\s+/g, '');
    amount = amount.replace(/,/g,'.');
    amount = parseFloat(amount);
    amount = accounting.formatNumber(amount, 2, " ");
    return amount.replace(/\./g,',');
}

function convertAmountToValid(amount)
{
    amount = amount.replace(/\s+/g, '');
    amount = amount.replace(/,/g,'.');
    amount = parseFloat(amount);
    return amount;
}