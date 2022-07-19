function loadRequestPayments(cntr_id, request_ids) {
	var
        container = $('#paymentsEmptyBlock'),
        preloadEntity = getPreloaderEntity('paymentPreloader');
		
	container.html(preloadEntity);

	$.ajax({
        type: "POST",
        cache: false,
        url: URL_LOAD_ACTS_REQUEST_PAYMENTS,
        dataType: "json",
        data: {iCUser:cntr_id, iRequestIds:request_ids},
        success: function (data) {
			console.log(data);
            container.html(data.content);
        },
        error: function (msg) {
            addErrorNotify('Получение платежей', 'Не удалось выполнить запрос!');
            container.html('Платежи не найдены');
            return false;
        }
    });
	return true;
};

function checkboxRequestPaymentProcessed() {
    if($('.select-on-check-all').is(':checked')){
        $('.select-on-check-all').prop('checked', false);
    }
    var
        currency = $('#actform-icurr'),
        checkedVal = $('#paymentsEmptyBlock .cbPayment:checked'),
        checkedCurrency = [],
        existOptions = [];
    checkedVal.each(function (index, value) {
        let
            currID = parseInt($(value).attr('data-curr'));
        if ($.inArray(currID, checkedCurrency) === -1) {
            checkedCurrency.push(currID);
        }
    });
    currency.find('option[value!=""]').each(function (index, value) {
        let
            currExID = $(value).attr('value');
        existOptions.push(currExID);
    });
    var
        arDiff = array_diff(checkedCurrency, existOptions);
    if (arDiff.length || checkedCurrency.length == 0) {
        fillCurrencyOption(checkedCurrency);
    }

    addServiceBlock(this);

    $("#actform-famount").trigger('change');
    $('.serv-amount').trigger('change');

    return false;
}

/**
 * Заполнение услуг и неявных платежей
 */
function fillServicesRequest() {
    var
        fAmount = $('#actform-famount'),
        currencyId = parseInt($('#actform-icurr').val()),
        valAmount = 0,
        arCb = $('#paymentsEmptyBlock .cbPayment:checked');

    $.each(arCb, function (index, value) {
        if (parseInt($(value).attr('data-curr')) != currencyId || $(value).attr('data-curr') == '' || currencyId == 0) {
            //todo ????
        } else {
            if($(value).attr('data-hide') == 1)
            {
                addHiddenPaymentBlock(value);
            }else{
                processFillService(value);
                valAmount += parseFloat($(value).attr('data-sum'));
            }
        }
    });
	
    fAmount.val(convertAmountToInvalid(valAmount));
}

$(document).on('change', '#actform-no-pays', function() {
    if(this.checked && $('#paymentsEmptyBlock .cbPayment:checked').length <= 0) {
        $("#actform-icurr").append("<option selected value='2'>BYN</option>");
    } else if(!this.checked && $('#paymentsEmptyBlock .cbPayment:checked').length <= 0) {
        $("#actform-icurr").html("");
        $("#actform-icurr").append("<option value>Choose exchange currency</option>");
    }
});

$(function () {
    $('#paymentsEmptyBlock').on('change', '.cbPayment', checkboxRequestPaymentProcessed);
    $('#paymentsEmptyBlock').on('change', '.select-on-check-all', checkboxPaymentProcessedAll);

    var
        sortListEmpty = $('#paymentsEmptyBlock');
    sortListEmpty.sortable();
    sortListEmpty.sortable().bind('sortupdate', function (e, ui) {
        sortUpdateFunction(sortListEmpty);
    });
    sortListEmpty.on('change','.serv-amount',recalculateActFullActAmount);
});
