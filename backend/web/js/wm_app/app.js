//сущность прелойдера
function getPreloaderEntity(t){var a=$(document.createElement("div"));return a.addClass("loader"),a.addClass("mrg-auto"),a.attr("id",t),a}
function customEmpty(a){return a==='' || a===undefined || a === null || isNaN(a); }
function amountFormatter(itemSelector)
{
    var
        amount = $(itemSelector).val();

    console.log(amount);
    if(amount == '')
        amount = '0';
    $(itemSelector).val(convertAmountToInvalid(amount));
}
function convertAmountToInvalid(amount)
{
    console.log('amount'+amount);
    if(typeof amount !== 'string' && isNaN(amount))
        return amount;
    console.log('amount'+amount);
    if($.isNumeric(amount)) {
        amount = amount.toString();
    }
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
