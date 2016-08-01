//сущность прелойдера
function getPreloaderEntity(t){var a=$(document.createElement("div"));return a.addClass("loader"),a.addClass("mrg-auto"),a.attr("id",t),a}
function customEmpty(a){return a==='' || a===undefined || a === null || isNaN(a); }
function amountFormatter(itemSelector,precision)
{
    if(precision == undefined || isNaN(precision) || precision == '')
        precision =2;

    var
        amount = $(itemSelector).val();
    if(amount == '')
        amount = '0';
    $(itemSelector).val(convertAmountToInvalid(amount,precision));
}
function convertAmountToInvalid(amount,precision)
{
    if(precision == undefined || isNaN(precision) || precision == '')
        precision =2;

    if(typeof amount !== 'string' && isNaN(amount))
        return amount;
    if($.isNumeric(amount)) {
        amount = amount.toString();
    }
    amount = amount.replace(/\s+/g, '');
    amount = amount.replace(/,/g,'.');
    amount = parseFloat(amount);
    amount = accounting.formatNumber(amount, precision, " ");
    return amount.replace(/\./g,',');
}

function convertAmountToValid(amount)
{
    if(typeof amount !== 'string' && isNaN(amount))
        return amount;
    if($.isNumeric(amount)) {
        amount = amount.toString();
    }
    amount = amount.replace(/\s+/g, '');
    amount = amount.replace(/,/g,'.');
    amount = parseFloat(amount);
    return amount;
}
