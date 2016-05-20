/**
 * Created by zhenya on 20.5.16.
 */
"use strict";
function loadPayments()
{
    var
        container = $('#paymentsBlock'),
        iCUser = $('#actform-icuser').val(),
        iLegalPerson = $('#actform-ilegalperson');


    var
        checkBoxs = container.find(input[type="checkbox"]);
    if(checkBoxs.lenght > 0)
    {
        checkBoxs.prop('checked',false);
        checkBoxs.trigger('change');
    }

    if(customEmpty(iCUser) || customEmpty(iLegalPerson))
    {
        container.html('Платежи не найдены');
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url:URL_LOAD_ACTS_PAYMENTS,
        dataType: "json",
        data: {pid:pid},
        success: function(msg){
            modal.find('.modal-body').html(msg);
            initDatePicker(modal.find('.datePicker'));
            swInitSelect2(modal.find('#partnermultilinkform-cntr'));
        },
        error: function(msg){
            addErrorNotify('Получение формы', 'Не удалось выполнить запрос!');
            return false;
        }
    });

    return true;
}