/**
 * Created by zhenya on 20.5.16.
 */
"use strict";
function loadPayments() {
    var
        container = $('#paymentsBlock'),
        iCUser = $('#actform-icuser').val(),
        iLegalPerson = $('#actform-ilegalperson').val(),
        checkBoxs = container.find('.cbPayment'),
        preloadEntity = getPreloaderEntity('paymentPreloader');

    if (checkBoxs.length > 0) {
        checkBoxs.prop('checked', false);
        checkBoxs.trigger('change');
    }
    container.html(preloadEntity);                          //set preloader
    if (customEmpty(iCUser) || customEmpty(iLegalPerson)) {
        container.html('Платежи не найдены');
        return false;
    }
    $.ajax({
        type: "POST",
        cache: false,
        url: URL_LOAD_ACTS_PAYMENTS,
        dataType: "json",
        data: {iCUser: iCUser, iLegalPerson: iLegalPerson},
        success: function (data) {
            container.html(data.content);
            checkDate();
        },
        error: function (msg) {
            addErrorNotify('Получение платежей', 'Не удалось выполнить запрос!');
            container.html('Платежи не найдены');
            return false;
        }
    });
    return true;
}

function checkboxPaymentProcessed() {
    var
        currency = $('#actform-icurr'),
        checkedVal = $('#paymentsBlock .cbPayment:checked'),
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
    var
        currencyId = parseInt($('#actform-icurr').val());
    if (!$(this).prop('checked')) {
        if (currencyId == parseInt($(this).attr('data-curr'))) {
            processUnfillService(this);
            let
                fAmount = $('#actform-famount');
            fAmount.val(parseFloat(fAmount.val()) - parseFloat($(this).attr('data-sum')));
        }
    } else {
        if (currencyId == parseInt($(this).attr('data-curr'))) {
            processFillService(this);
            let
                fAmount = $('#actform-famount');
            fAmount.val(parseFloat(fAmount.val()) + parseFloat($(this).attr('data-sum')));
        }
    }
    return false;
}

function changeCurrencyField() {
    var
        id = $(this).val();
    unfillServices();
    if (id != undefined && id != 0 && id != '') {
        fillServices();
    }
}

function unfillServices() {
    $('#servicesBlock').html('');
    $('#actform-famount').val(0);
}

function fillServices() {
    var
        fAmount = $('#actform-famount'),
        currencyId = parseInt($('#actform-icurr').val()),
        valAmount = 0,
        arCb = $('#paymentsBlock .cbPayment:checked');

    $.each(arCb, function (index, value) {
        if (parseInt($(value).attr('data-curr')) != currencyId || $(value).attr('data-curr') == '' || currencyId == 0) {
            //todo ????
        } else {
            valAmount += parseFloat($(value).attr('data-sum'));
            processFillService(value);
        }
    });

    fAmount.val(valAmount);
}
/**
 * Создаем услуги, если услуга новая, то для ней необходимо получить информацию по контракту и описание для услуги из шаблона
 * @param value
 */
function processFillService(value) {
    let
        servID = $(value).attr('data-serv_id'),
        containter = $('#s' + servID);
    if (containter.length > 0) {
        let
            currAmount = parseFloat(containter.find('.serv-amount').val());
        currAmount += parseFloat($(value).attr('data-sum'));
        containter.find('.serv-amount').val(currAmount);
    } else {
        let
            contractDetail = $.parseJSON(getContractDateAndContractNumber(servID)),
            servBlock = createEntityServicesBlock(servID, $(value).attr('data-sum'));
        $('#servicesBlock').append(servBlock);
        if (contractDetail.contractDate != undefined && contractDetail.contractNumber != undefined) {
            $('#servicesBlock #s' + servID + ' .contractDate').val(contractDetail.contractDate);
            $('#servicesBlock #s' + servID + ' .contractNumber').val(contractDetail.contractNumber);
        }
        if(!customEmpty(contractDetail.bTplFind) && contractDetail.bTplFind)
        {
            $('#servicesBlock #s' + servID + ' .templateField').val(contractDetail.job_description);
        }else{
            addErrorNotify('Получение шаблона полей для услуги', 'Шаблон не найден!');
        }
    }
}
/**
 *
 * @param value
 */
function processUnfillService(value) {
    let
        servID = $(value).attr('data-serv_id'),
        containter = $('#s' + servID);

    if (containter.length > 0) {
        let
            currAmount = parseFloat(containter.find('.serv-amount').val());
        currAmount -= parseFloat($(value).attr('data-sum'));
        if (currAmount > 0) {
            containter.find('.serv-amount').val(currAmount);
        } else {
            containter.remove();
            sortUpdateFunction($('#servicesBlock'));                //if remove block, we need update order
        }
    }
}
/**
 *
 * @param arCurrID
 */
function fillCurrencyOption(arCurrID) {
    var
        arrOptions = [],
        currency = $('#actform-icurr');

    currency.val('');                                   //set default value
    currency.trigger('change');                         //call change trigger
    currency.find('option[value!=""]').remove();        //remove old options

    if (arCurrID.length > 0)                             //find new options
        for (let i = 0; i < arCurrency.length; i++) {
            let
                tmpID = parseInt(arCurrency[i].id);
            if ($.inArray(tmpID, arCurrID) != -1) {

                arrOptions.push(arCurrency[i]);
            }
        }

    if (arrOptions.length > 0)                           //check is need fill dropdown
    {
        for (let j = 0; j < arrOptions.length; j++)        //fill dropdown
        {
            let
                value = arrOptions[j],
                option = $(document.createElement('option'));
            option.attr('value', value.id);
            option.html(value.code);
            currency.append(option);
        }
    }
}
/**
 *
 * @param selector
 */
function sortUpdateFunction(selector) {
    let
        arSort = selector.sortable("toArray");
    for (let k = 0; k < arSort.length; k++) {
        let
            tmpID = arSort[k];
        $('#' + tmpID + ' .service-order').val(k);
    }
}
/**
 *
 * @param serviceID
 * @param amount
 * @returns {jQuery|HTMLElement}
 */
function createEntityServicesBlock(serviceID, amount) {
    var
        inputServices = createElement('input', [
            {name: 'name', value: 'ActForm[arServices][]'},
            {name: 'value', value: serviceID},
            {name: 'type', value: 'hidden'}
        ]),
        inputOrder = createElement('input', [
            {name: 'name', value: 'ActForm[arServOrder][' + serviceID + ']'},
            {name: 'value', value: (parseInt(getMaxServOrder()) + 1)},
            {name: 'type', value: 'hidden'},
            {name: 'class', value: 'service-order'}
        ]),
        inputQuantity = createElement('input', [
            {name: 'name', value: 'ActForm[arServQuantity][' + serviceID + ']'},
            {name: 'value', value: 1},
            {name: 'type', value: 'text'},
            {name: 'class', value: 'form-control'}
        ]),
        inputAmount = createElement('input', [
            {name: 'name', value: 'ActForm[arServAmount][' + serviceID + ']'},
            {name: 'value', value: amount},
            {name: 'type', value: 'text'},
            {name: 'class', value: 'form-control serv-amount'}
        ]),
        inputContractNumber = createElement('input', [
            {name: 'name', value: 'ActForm[sContractNumber][' + serviceID + ']'},
            {name: 'value', value: 0},
            {name: 'type', value: 'text'},
            {name: 'class', value: 'form-control contractNumber'}
        ]),
        inputContractDate = createElement('input', [
            {name: 'name', value: 'ActForm[contractDate][' + serviceID + ']'},
            {name: 'value', value: 0},
            {name: 'type', value: 'text'},
            {name: 'class', value: 'form-control contractDate'}
        ]),
        inputTemplateField = createElement('textarea', [
            {name: 'name', value: 'ActForm[arTemplate][' + serviceID + ']'},
            //{name: 'value', value: ''},
            //{name: 'type', value: 'textarea'},
            {name: 'class', value: 'form-control templateField'}
        ]),
        li = createElement('li', [
            {name: 'class', value: 'block-sortable'},
            {name: 'id', value: 's' + serviceID}
        ]),
        h4 = createElement('h4', []),
        div1 = createElement('div', [{name: 'class', value: 'form-group col-md-6 col-sm-6 col-xs-12'}]),
        div2 = createElement('div', [{name: 'class', value: 'form-group col-md-6 col-sm-6 col-xs-12'}]),
        div3 = createElement('div', [{name: 'class', value: 'form-group col-md-6 col-sm-6 col-xs-12'}]),
        div4 = createElement('div', [{name: 'class', value: 'form-group col-md-6 col-sm-6 col-xs-12'}]),
        div5 = createElement('div', [{name: 'class', value: 'form-group col-md-12 col-sm-12 col-xs-12'}]),
        label1 = createElement('label', [{name: 'class', value: 'control-label'}]),
        label2 = createElement('label', [{name: 'class', value: 'control-label'}]),
        label3 = createElement('label', [{name: 'class', value: 'control-label'}]),
        label4 = createElement('label', [{name: 'class', value: 'control-label'}]),
        label5 = createElement('label', [{name: 'class', value: 'control-label'}]),
        clearfix = createElement('div', [{name: 'class', value: 'clearfix'}])
        ;
    h4.html(arServices[serviceID]);
    li.append(h4);
    li.append(inputServices);
    li.append(inputOrder);
    div2.append(label2.html('Сумма'));
    div2.append(inputAmount);
    li.append(div2);
    div1.append(label1.html('Кол-во'));
    div1.append(inputQuantity);
    li.append(div1);
    div3.append(label3.html('Номер контракта'));
    div3.append(inputContractNumber);
    li.append(div3);
    div4.append(label4.html('Дата контракта'));
    initDatePicker(inputContractDate);
    div4.append(inputContractDate);
    li.append(div4);
    div5.append(label5.html('Наименование работы (услуги)'));
    div5.append(inputTemplateField);
    li.append(div5);
    li.append(clearfix);
    return li;
}
/**
 *
 * @param serviceID
 * @returns {*|string}
 */
function getContractDateAndContractNumber(serviceID) {
    var
        legalPersonId = $('#actform-ilegalperson').val(),
        iCUserId = $('#actform-icuser').val();
    return $.ajax({
        url: URL_LOAD_CONTRACT_DETAIL,
        type: "POST",
        async: false,
        dataType: "json",
        data: {iCUser: iCUserId, iServId: serviceID,iLegalPerson:legalPersonId},
        error: function (msg) {
            addErrorNotify('Получение номера и даты контракта', 'Не удалось выполнить запрос!');
            return false;
        }
    }).responseText;
}

/**
 *
 * @returns {number}
 */
function getMaxServOrder() {
    let
        maxOrder = 0;
    $.each($('#servicesBlock .service-order'), function (index, value) {
        let
            tmpOrder = $(value).val();

        if (tmpOrder > maxOrder)
            maxOrder = tmpOrder;
    });
    return maxOrder;
}
/**
 *
 * @param tagName
 * @param attributes
 * @returns {jQuery|HTMLElement}
 */
function createElement(tagName, attributes) {
    let
        element = $(document.createElement(tagName));
    for (let ind = 0; ind < attributes.length; ind++) {
        let
            tmp = attributes[ind];
        element.attr(tmp.name, tmp.value);
    }
    return element;
}
/**
 *
 */
function checkDate()
{
    var 
        actDate = $('#actform-actdate'),
        container = $('#paymentsBlock .cbPayment');

    $.each(container,function(index,value){
        if(
            actDate.val() == undefined ||
            actDate.val() == '' ||
            (
                actDate.val() != undefined &&
                actDate.val() != '' &&
                strtotime(actDate.val()) < strtotime($(value).attr('data-date'))
            )
        )
        {
            if($(value).prop('checked'))
            {
                $(value).prop('checked',false);
                $(value).trigger('change');
            }

            $(value).attr('disabled','disabled');
        }else{
            $(value).removeAttr('disabled');
        }
    });
}
/**
 *
 */
function recalculateActFullActAmount()
{
    var
        fAmount = 0,
        services = $('#servicesBlock .serv-amount');

    $.each(services,function(index,value){
        fAmount+=parseFloat($(value).val());
    });

    $('#actform-famount').val(fAmount);
}
/**
 * @returns {boolean}
 */
function customValidateForm()
{
    if($('#paymentsBlock .cbPayment:checked').length <= 0)                      //check if selected payments
    {
        addErrorNotify('Сохрание акта', 'Необходимо выбрать платежи!');
        return false;
    }

    if($('#servicesBlock .serv-amount').length <= 0)                            //check if set services
    {
        addErrorNotify('Сохрание акта', 'Необходимо задать услуги!');
        return false;
    }

    var                                                                         //check for correct amount
        fAmount = 0,
        services = $('#servicesBlock .serv-amount');

    $.each(services,function(index,value){
        fAmount+=parseFloat($(value).val());
    });

    if(parseFloat($('#actform-famount').val(fAmount)) < fAmount)
    {
        addErrorNotify('Сохрание акта', 'Сумма акта должна быть больше либо равна сумме по услугам!');
        return false;
    }

    var
        bContractValid = true,
        arTemplate = $('#servicesBlock .templateField'),
        arDates = $('#servicesBlock .contractDate'),
        arNumbers = $('#servicesBlock .contractNumber');

    if(arDates.length == 0 || arNumbers == 0)
    {
        addErrorNotify('Сохрание акта', 'Необходимо задать номер акта и дату акта!');
        return false;
    }

    if(arDates.length == 1 && arNumbers.length == 1)
    {
        if(arDates.val() == '' || arNumbers == '')
        {
            addErrorNotify('Сохрание акта', 'Необходимо задать номер акта и дату акта!');
            return false;
        }
    }

    var
        tmpDate = null;
    $.each(arDates,function(index,value){
        let
           tmpD = $(value).val();
        if(tmpD == undefined || tmpD == '')
        {
           bContractValid = false;
        }

        if(bContractValid && tmpDate)
        {
            if(tmpDate != tmpD)
            {
                bContractValid = false;
            }
        }else{
            tmpDate = tmpD;
        }
    });

    if(!bContractValid)
    {
        addErrorNotify('Сохрание акта', 'Дата актов заданы неверно!');
        return false;
    }

    var
        tmpNumber = null;

    $.each(arNumbers,function(index,value){
        let
            tmpN = $(value).val();

        if(tmpN == undefined || tmpN == '')
        {
            bContractValid = false;
        }

        if(bContractValid && tmpNumber)
        {
            if(tmpNumber != tmpN)
            {
                bContractValid = false;
            }
        }else{
            tmpNumber = tmpN;
        }
    });
    
    if(!checkActNumber())
    {
        addErrorNotify('Сохрание акта', 'Неверно задан номер акта либо он уже занят!');
        return false;
    }

    $.each(arTemplate,function(index,value){
        let
            tmpT = $(value).text();

        if(tmpT == undefined || tmpT == '')
        {
            bContractValid = false;
        }
    });

    if(!checkActNumber())
    {
        addErrorNotify('Сохрание акта', 'Необходимо хадать "Наименование работы (услуги)"');
        return false;
    }

    return true;
}
/**
 * @returns {boolean}
 */
function checkActNumber()
{
    var
        number = $('#actform-iactnumber').val(),
        legalId = $('#actform-ilegalperson').val();

    if(customEmpty(number) || customEmpty(legalId))
    {
        addErrorNotify('Проверка номера акта', 'Необходимо задать номер акта и юр. лицо');
        return false;
    }

    var
        response = $.ajax({
            url: URL_CHECK_ACT_NUMBER,
            type: "POST",
            async: false,
            dataType: "json",
            data: {number: number, iLegalId: legalId},
            error: function (msg) {
                addErrorNotify('Проверка номера акта', 'Не удалось выполнить запрос!');
                return false;
            }
        }).responseText;

    response = $.parseJSON(response);
    if(response.answer != undefined && response.answer != '' && response.answer == true)
    {
        return true;
    }
    return false;
}
/**
 *
 */
function getActsNumber()
{
    var
        actNumber = $('#actform-iactnumber'),
        iLegalPerson = $('#actform-ilegalperson').val();

    if(customEmpty(iLegalPerson))
    {
        actNumber.val('');
    }else{
        $.ajax({
            type: "POST",
            cache: false,
            url: URL_GET_NEXT_ACT_NUMBER,
            dataType: "json",
            data: {iLegalPerson: iLegalPerson},
            success: function (data) {
                actNumber.val(data);
            },
            error: function (msg) {
                addErrorNotify('Получение номера акта', 'Не удалось выполнить запрос!');
                actNumber.val('');
                return false;
            }
        });
    }
}
/**
 * @returns {boolean}
 */
function checkContactor()
{
    var 
        iCUserId = $('#actform-icuser').val();
    removeAllNotifications();
    if(customEmpty(iCUserId))
        return false;
    $.ajax({
        type: "POST",
        cache: false,
        url: URL_CHECK_CONTRACTOR_FIELDS,
        dataType: "json",
        data: {iCUserId: iCUserId},
        success: function (data) {
            if(!customEmpty(data) && data.hasError)
            {
                addErrornotificationStickly(data.corpName,data.error);
            }
        },
        error: function (msg) {
            addErrorNotify('Проверка полей контрагента', 'Не удалось выполнить запрос!');
            return false;
        }
    });
}
/**
 * @param item
 */
function initDatePicker(item)
{
    item.daterangepicker({
        singleDatePicker: true,
        calender_style: "picker_2",
        startDate : moment().startOf('day'),
        locale :{
            format: 'DD.MM.YYYY',
            separator: '.',
        }
    });
}
$(function () {
    $('#actform-ilegalperson,#actform-icuser').on('change', loadPayments);
    $('#paymentsBlock').on('change', '.cbPayment', checkboxPaymentProcessed);
    var
        sortList = $('#servicesBlock');
    sortList.sortable();
    sortList.sortable().bind('sortupdate', function (e, ui) {
        sortUpdateFunction(sortList);
    });
    $('#actform-icurr').on('change', changeCurrencyField);
    $('#actform-actdate').on('change',checkDate);
    sortList.on('change','.serv-amount',recalculateActFullActAmount);
    $(document).on("submit", "form#act-form", customValidateForm);
    $('#actform-ilegalperson').on('change',getActsNumber);
    $('#actform-icuser').on('change',checkContactor);
});