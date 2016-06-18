/**
 * Created by zhenya on 15.6.16.
 */
"use strict";
function  createElement(serviceId,serviceName,iOrder) {
    var
        li = $('<li></li>',{                    //create li element
            class:'block-sortable',
            id:'s'+serviceId
        });

    li.append($('<h4></h4>',{               //create tittle
            html:serviceName+'<a href="#nogo" data-toggle="tooltip" data-placement="top" data-original-title="Удалить услугу" class="pull-right red  marg-l-10"><i class="fa fa-minus" data-serv="'+serviceId+'"></i></a><a href="#nogo" data-id="'+serviceId+'" data-choose="0" data-toggle="tooltip" data-placement="top" data-original-title="Выбрать описание и договор" class="pull-right red chooseService"><i class="fa fa-check"></i></a>'
        }));
    li.append($('<input />',{               //input hidden array of services
            value:serviceId,
            type:'hidden',
            class:"arServClass",
            name:'BillForm[arServices][]'
        }));
    li.append($('<input />',{               //input hidden array of service order
            value:iOrder,
            type:'hidden',
            class:"service-order",
            name:'BillForm[arServOrder]['+serviceId+']'
        }));
    li.append($('<div></div>',{             //add input block
                class:'form-group col-md-6 col-sm-6 col-xs-12'
            }).append(
                $('<label></label>',{
                    text:'Сумма',
                    class:'control-label'
                })
            ).append(
                $('<input />',{
                    name:'BillForm[arServAmount]['+serviceId+']',
                    type: 'text',
                    value: 0,
                    class: 'form-control serv-amount'
                }).attr('data-serv-id',serviceId).attr('old-amount',0)
            )
        );

    li.append($('<div></div>',{             //add select
            class:'form-group col-md-6 col-sm-6 col-xs-12'
        }).append(
            $('<label></label>',{
                text:'Шаблон услуги',
                class:'control-label'
            })
            ).append(getDropDownList('BillForm[arServTpl]['+serviceId+']', 'sel'+serviceId, arServTplOptions,serviceId))
        );

    li.append($('<div></div>',{             //add input block
            class:'form-group col-md-12 col-sm-12 col-xs-12'
        }).append(
        $('<label></label>',{
            text:'Предмет счета',
            class:'control-label'
        })
        ).append(
        $('<textarea />',{
            name:'BillForm[arServTitle]['+serviceId+']',
            type: 'text',
            value: 0,
            class: 'form-control serv-title'
        }).attr('data-serv-id',serviceId)
        )
    );

    li.append($('<div></div>',{             //add input block
            class:'form-group col-md-6 col-sm-6 col-xs-12'
        }).append(
        $('<label></label>',{
            text:'Описание',
            class:'control-label'
        })
        ).append(
        $('<textarea />',{
            name:'BillForm[arServDesc]['+serviceId+']',
            type: 'text',
            value: 0,
            class: 'form-control serv-desc'
        }).attr('data-serv-id',serviceId)
        )
    );

    li.append($('<div></div>',{             //add input block
            class:'form-group col-md-6 col-sm-6 col-xs-12'
        }).append(
        $('<label></label>',{
            text:'Договор оферты',
            class:'control-label'
        })
        ).append(
        $('<textarea />',{
            name:'BillForm[arServContract]['+serviceId+']',
            type: 'text',
            value: 0,
            class: 'form-control serv-contract'
        }).attr('data-serv-id',serviceId)
        )
    );


    li.append($('<div></div>',{
        class:'clearfix'
    }));

    return li;
}

/**
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
 * @param name
 * @param id
 * @param optionList
 * @returns {*|jQuery}
 */
function getDropDownList(name, id, optionList,serviceId) {
    var
        combo = $("<select></select>",{id:id, name:name,class:'form-control tpl'}).attr('data-serv-id',serviceId);
    $.each(optionList, function (i, el) {
        combo.append($('<option></option>',{value:i, text:el}));
    });
    return combo;
}
/**
 * 
 */
function addService()
{
    var 
        addedServ = $('#servicesBlock .arServClass');
    $.each(addedServ,function(index,value){
        let
            check = $('#activity-modal input[value="'+$(value).val()+'"]');
        check.attr('disabled','disabled');
    });
    $('#activity-modal').modal();
}
/**
 *
 */
function addServClickAction()
{
    var
        arServ = $('#activity-modal input:checked');
    var
        order = parseInt(getMaxServOrder());
    var
        arServicesId = [];
    $.each(arServ,function(ind,val){
        let
            servId = $(val).val();
        $('#servicesBlock').append(createElement(servId,arServMap[servId],order));
        order++;
        arServicesId.push(servId);
    });

    $('#servicesBlock').sortable('reload');
    $('#activity-modal .close').trigger('click');
    $('#activity-modal input').prop('checked',false);
    if( arServicesId.length > 0)
    {
        getServbiceTplParams( arServicesId);
    }
}


/**
 * Обновление инпутов порядка по событию перетаскивания
 * @param selector
 */
function sortUpdateFunction() {
    let
        arSort = $('#servicesBlock').sortable("toArray");
    var
        arList = arSort.find('li');
    for (let k = 0; k < arList.length; k++) {
        let
            tmpID = $(arList[k]).attr('id');
        $('#' + tmpID + ' .service-order').val(k);
    }
}
/**
 *
 */
function removeService()
{
    var
        servId = $(this).attr('data-serv'),                             //service id
        amount = parseFloat($('#s'+servId+' .serv-amount').val()),      //get amount for del service
        fullAmount = $('#billform-famount');                            //get full amount input
    var
        currAmount = parseFloat(fullAmount.val());                      //current amount
    if(amount < 0 || customEmpty(amount))
    {
        amount = 0;
    }

    currAmount-=amount;
    if(currAmount < 0 || customEmpty(currAmount))
    {
        currAmount = 0;
    }
    fullAmount.val(currAmount);                                         //set new amount

    if($('.chooseService[data-id="'+servId+'"]').attr('data-choose') == 1)
    {
        $('#billform-sdescription').text('');
        $('#billform-soffercontract').val('');
    }

    $('#s'+servId).remove();                                            //remove service block
    sortUpdateFunction();
}
/**
 * @returns {boolean}
 */
function getBillTpl() {
    var
        iLegalId = $('#billform-ilegalperson').val();

    if(customEmpty(iLegalId))
    {
        $('#billform-busetax').val(0);
        $('#billform-btaxrate').val('');
        return false;
    }
    $('.servPreloader').removeClass('hide');
    $('#servicesBlock').addClass('hide');
    $.ajax({
        type: "POST",
        cache: false,
        url: urlFindDocxTpl,
        dataType: "json",
        data: {iLegalId: iLegalId},
        success: function (data) {
            $('#billform-idocxtpl').val(data.docx_id);
            $('#billform-busetax').val(data.use_vate);
            if (data.use_vate == 1) {
                $('#billform-btaxrate').val(data.vat_rate);
            } else {
                $('#billform-btaxrate').val('');
            }
            $('.servPreloader').addClass('hide');
            $('#servicesBlock').removeClass('hide');
        },
        error: function (msg) {
            addErrorNotify('Получение параметров юр. лица', 'Не удалось выполнить запрос!');
            $('.servPreloader').addClass('hide');
            $('#servicesBlock').removeClass('hide');
            return false;
        }
    });
}
/**
 * @param serviceIds
 * @returns {boolean}
 */
function getServbiceTplParams(serviceIds)
{
    if(serviceIds.length == 0)
    {
        addErrorNotify('Получение параметров шаблона услуги', 'Не заданы услуги!');
        return false;
    }

    var
        iCtrId = $('#billform-icuserid').val(),
        iLegalId = $('#billform-ilegalperson').val();

    if(customEmpty(iCtrId) || customEmpty(iLegalId))
    {
        addErrorNotify('Получение параметров шаблона услуги', 'Необходимо задать контрагента и юр. лицо!');
        return false;
    }
    $.ajax({
        type: "POST",
        cache: false,
        url: urlFindServiceTpl,
        dataType: "json",
        data: {iLegalId: iLegalId,iCtrId:iCtrId,arServ:serviceIds},
        success: function (data) {
            $.each(data,function(ind,value){
                $('#sel'+value.service_id).val(value.id);
                $('#s'+value.service_id+' .serv-title').val(value.object_text);
                $('#s'+value.service_id+' .serv-desc').val(value.description);
                $('#s'+value.service_id+' .serv-contract').val(value.offer_contract);
            });
        },
        error: function (msg) {
            addErrorNotify('Получение параметров юр. лица', 'Не удалось выполнить запрос!');
            return false;
        }
    });
}

/**
 *
 * @returns {boolean}
 */
function chooseService()
{
    var
        serviId = parseInt($(this).attr('data-id'));

    if(customEmpty(serviId))
    {
        addErrorNotify('Выбор параметров услуги', 'Не удалось получить id услуги!');
        return false;
    }

    var
        description = $('#s'+serviId+' .serv-desc').val(),
        contract = $('#s'+serviId+' .serv-contract').val();

    $('#billform-sdescription').text(description);
    $('#billform-soffercontract').val(contract);

    $('.chooseService').removeClass('green').addClass('red').attr('data-choose',0);
    $(this).removeClass('red').addClass('green').attr('data-choose',1);
}
/**
 *
 */
function changeServiceAmount(){

    var
        oldAmount = parseFloat($(this).attr('old-amount')),
        newAmount = parseFloat($(this).val()),
        fullAMountContainer = $('#billform-famount'),
        fullAmount = parseFloat(fullAMountContainer.val());

    if(newAmount < 0 || customEmpty(newAmount))
    {
        newAmount = 0;
        $(this).val(0);
    }

    if(fullAmount < 0 || customEmpty(fullAmount))
    {
        fullAmount = 0;
        fullAMountContainer.val(0);
    }

    fullAmount = fullAmount+newAmount - oldAmount;
    if(fullAmount < 0)
        fullAmount = 0;

    fullAMountContainer.val(fullAmount);
    $(this).attr('old-amount',newAmount);
}
/**
 *
 */
function changeServiceTpl()
{
    var
        iCtrId = $('#billform-icuserid').val(),
        iServId = $(this).attr('data-serv-id'),
        tplId = $(this).val();
    $('.servPreloader').removeClass('hide');
    $('#servicesBlock').addClass('hide');
    $.ajax({
        type: "POST",
        cache: false,
        url: urlGetTplById,
        dataType: "json",
        data: {iBTpl:tplId,iCntr:iCtrId},
        success: function (data) {
            $('#s'+iServId+' .serv-title').val(data.object_text);
            $('#s'+iServId+' .serv-desc').val(data.description);
            $('#s'+iServId+' .serv-contract').val(data.offer_contract);

            if($('.chooseService[data-id="'+iServId+'"]').attr('data-choose') == 1)
            {
                $('#billform-sdescription').text(data.description);
                $('#billform-soffercontract').val(data.offer_contract);
            }

            $('.servPreloader').addClass('hide');
            $('#servicesBlock').removeClass('hide');
        },
        error: function (msg) {
            $('.servPreloader').addClass('hide');
            $('#servicesBlock').removeClass('hide');
            addErrorNotify('Получение параметров шаблона', 'Не удалось выполнить запрос!');
            return false;
        }
    });
}
/**
 * @returns {boolean}
 */
function validateFormBefore()
{
    //проверим, заполнены ли услуги
    var
        arServices = $('.arServClass');

    if(arServices.length <= 0)
    {
        addErrorNotify('Сохранение счета', 'Необходимо указать услуги!');
        return false;
    }

    var
        bError = false,
        fullAmount = parseFloat($('#billform-famount').val()),
        tmpAmount = 0,
        sOffertaContract = $.trim($('#billform-soffercontract').val());

    $.each(arServices,function(ind,val){
        let
            servId = $(val).val();

        let
            contract = $('.serv-contract[data-serv-id="'+servId+'"]').val(),
            amount = parseFloat($('.serv-amount[data-serv-id="'+servId+'"]').val());

        tmpAmount+=amount;

        if(customEmpty(amount) || amount <= 0)
        {
            addErrorNotify('Сохранение счета', 'В услуге "'+arServMap[servId]+'" неверно задана сумма!');
            bError = true;
        }
        if($.trim(contract) != sOffertaContract)
        {
            addErrorNotify('Сохранение счета', 'В услуге "'+arServMap[servId]+'" неверно задан договор оферты!');
            bError = true;
        }
    });

    if(tmpAmount != fullAmount)
    {
        addErrorNotify('Сохранение счета', 'Сумма сумм по услугам не равна общей сумме!');
        bError = true;
    }

    return !bError;
}


/**
 *
 */
function removeContractOrLegalPerson()
{
    $('#servicesBlock .fa-minus').trigger('click');
}

//document ready
$(function(){
    $('#addServId').on('click',addService);
    $('#activity-modal .btn').on('click',addServClickAction);
    var
        sortList = $('#servicesBlock');
    sortList.sortable({items: 'li'});
    sortList.sortable().bind('sortupdate', function (e, ui) {
        sortUpdateFunction();
    });
    $('#servicesBlock').on('click','.fa-minus',removeService);
    $('#billform-ilegalperson').on('change',getBillTpl);
    getBillTpl();
    $('#servicesBlock').on('click','.chooseService',chooseService);
    $('#servicesBlock').on('change','.serv-amount',changeServiceAmount);
    $('#servicesBlock').on('change','.tpl',changeServiceTpl);
    $('#billform-icuserid, #billform-ilegalperson').on('change',removeContractOrLegalPerson);
    $(document).on("submit", "form#form-bill", validateFormBefore);
});