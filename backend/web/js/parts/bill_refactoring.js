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
            html:serviceName+'<i class="fa fa-minus pull-right" data-serv="'+serviceId+'"></i>'
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

function getBillTpl()
{
    $('#')



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
});