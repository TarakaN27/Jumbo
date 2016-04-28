/**
 * Created by zhenya on 28.4.16.
 */
'use strict';
/**
 * Вычисляет какая сумма осталась
 * @returns {*}
 */
function calculateAvalableAmount()
{
    var
        countA = 0,
        container = $('#avAmount'),
        availableAmount = container.attr('data-amount'),
        amounts = $('.amounts');

    if (availableAmount == undefined)
        return null;

    $.each(amounts, function( index, value ) {
        let
            aTmp = $(value).val();
        if (aTmp != undefined && aTmp != '') {
            countA += parseFloat(aTmp);
        }
    });

    return availableAmount - countA;
}

/**
 * Обновляет счетчик доступной формы, для информации
 * @returns {boolean}
 */
function checkAvailableAmount() {
    var
        container = $('#avAmount');
    let
        avAmount = calculateAvalableAmount();

    if(avAmount == null) {
        addWarningNotify('Вычисление доступной суммы', 'Ошибка калькуляции');
        return false;
    }

    container.html(avAmount);
    if(avAmount == 0)
    {
        container.removeClass('colorDanger');
        container.addClass('colorSuccess');
    }else{
        container.removeClass('colorSuccess');
        container.addClass('colorDanger');
    }
}
/**
 * Валидация формы
 * @returns {boolean}
 */
function validateWPProcessForm()
{
    var
        avAmount = calculateAvalableAmount();

    if(avAmount == null){
        addWarningNotify('Вычисление доступной суммы', 'Ошибка калькуляции');
        return false;
    }

    if(avAmount != 0)
    {
        addErrorNotify('Сохранение формы', 'Вы должны использовать всю сумму');
        return false;
    }

    return true;
}

$(function(){
    checkAvailableAmount();                                                 //init available amount
    $('#dynamic-form').on('change','.amounts',checkAvailableAmount);        //bind recalculate available amount by change events
    $(document).on("submit", "form#dynamic-form", validateWPProcessForm);   //validate form before send
});


