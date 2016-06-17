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
            html:serviceName+'<a href="#nogo" data-toggle="tooltip" data-placement="top" data-original-title="Выбрать описание и договор" class="pull-right red  marg-l-10"><i class="fa fa-minus" data-serv="'+serviceId+'"></i></a><a href="#nogo" data-toggle="tooltip" data-placement="top" data-original-title="Выбрать описание и договор" class="pull-right red"><i class="fa fa-check"></i></a>'
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
                }).attr('data-serv-id',serviceId)
            )
        );

    li.append($('<div></div>',{             //add select
            class:'form-group col-md-6 col-sm-6 col-xs-12'
        }).append(
            $('<label></label>',{
                text:'Шаблон услуги',
                class:'control-label'
            })
            ).append(getDropDownList('BillForm[arServTpl]['+serviceId+']', 's'+serviceId, arServTplOptions))
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
 *
 *
 * $arServTitle = [],
 $arServDesc = [],
 $arServContract = [],
 *
 */


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
function getDropDownList(name, id, optionList) {
    var
        combo = $("<select></select>",{id:id, name:name,class:'form-control'});
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

        console.log($(value).val());
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

    console.log(arServ);
    var
        order = parseInt(getMaxServOrder());

    $.each(arServ,function(ind,val){
        let
            servId = $(val).val();
        $('#servicesBlock').append(createElement(servId,arServMap[servId],order));
        order++;
    });

    $('#servicesBlock').sortable('reload');
    $('#activity-modal .close').trigger('click');
    $('#activity-modal input').prop('checked',false);
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
    console.log(arList);
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
    currAmount-=amount;
    if(currAmount <0)
    {
        currAmount = 0;
    }
    fullAmount.val(currAmount);                                         //set new amount

    $('#s'+servId).remove();                                            //remove service block
    sortUpdateFunction();
}

function getBillTpl() {
    var
        iLegalId = $('#billform-ilegalperson').val();

    if(customEmpty(iLegalId))
    {
        $('#billform-busetax').val(0);
        $('#billform-btaxrate').val('');
        return false;
    }

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
        },
        error: function (msg) {
            addErrorNotify('Получение параметров юр. лица', 'Не удалось выполнить запрос!');
            return false;
        }
    });
}

function getServbiceTplParams(serviceIds)
{
    if(customEmpty(serviceIds) || serviceIds.length == 0)
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
        data: {iLegalId: iLegalId,iCtrId:iCtrId,arServ:$.toJSON(serviceIds)},
        success: function (data) {
                    



        },
        error: function (msg) {
            addErrorNotify('Получение параметров юр. лица', 'Не удалось выполнить запрос!');
            return false;
        }
    });






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
});