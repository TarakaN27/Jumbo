/**
 * Created by Yauheni Motuz on 22.3.16.
 */
"use strict";

/**
 * action when bonus type is changed
 */
function changeBonusType()
{
    var
        groupType = $('#bonusscheme-grouping_type'),
        numMonth = $('#bonusscheme-num_month'),
        type = parseInt($(this).val());

    switch (type){
        case B_TYPE_UNIT:
            $('.type2,.type3,.type5').addClass('hide');
            $('.type1').removeClass('hide');
            groupType.removeAttr('disabled');
            numMonth.attr('disabled','disabled');
            numMonth.val('');
            numMonth.trigger('change');
            break;

        case B_TYPE_SIMPLE:
        case B_TYPE_COMPLEX:
            $('.type1,.type5').addClass('hide');
            $('.type2,.type3').removeClass('hide');
            groupType.removeAttr('disabled');
            if(type == B_TYPE_SIMPLE)
            {
                $('.ch_type2').removeClass('hide');
                $('.ch_type3').addClass('hide');
                numMonth.attr('disabled','disabled');
                numMonth.val('');
                numMonth.trigger('change');
            }else{
                $('.ch_type2').addClass('hide');
                $('.ch_type3').removeClass('hide');
                numMonth.removeAttr('disabled','disabled');
            }
            break;

        case B_TYPE_COMPLEX_PARTNER:
            groupType.removeAttr('disabled');
            $('.type2,.type3,.type1,.type5').addClass('hide');
            $('.type4').removeClass('hide');
            break;

        case B_TYPE_PAYMENT_RECORDS:
            groupType.val('');
            groupType.attr('disabled','disabled');
            $('.type2,.type3,.type1,.type4').addClass('hide');
            $('.type5').removeClass('hide');
            break;

        default:
            $('.type1,.type2,.type3,.type4').addClass('hide');
            groupType.removeAttr('disabled');
            numMonth.removeAttr('disabled','disabled');
            numMonth.val('');
            numMonth.trigger('change');
            break;
    }
}
/**
 *
 * @returns {boolean}
 */
function changeSchemeNumMonth()
{
    var
        num = $(this).val();

    if(num == undefined || num < 0 || num =='')
    {
        $('.monthList').html('');
        $('.monthList').attr('data-num',0);
        return false;
    }
    num = parseInt(num);
    var
        monthList = $('.monthList');    //get all month container

    monthList.each(function( index ) {
        var
            this1 = this,
            servID = $(this).attr('data-col'),
            currentNum = parseInt($(this).attr('data-num'));
        if(currentNum > num)        //if need remove element
        {
            for(var j = currentNum; j >= num+1;j--)
            {
                $('#div_mid_'+servID +'_'+j).remove();
            }
        }else{                  //if need add new element
            for(var i = currentNum+1;i <= num;i++)
            {
                var
                    input = $(document.createElement('input')), //input
                    label = $(document.createElement('label')), //label
                    div = $(document.createElement('div'));     //div container
                label.html(i);
                input.attr('name','months['+servID +']['+i+']');
                input.attr('id','mid_'+servID +'_'+i);
                div.addClass('form-group');
                div.attr('id','div_mid_'+servID +'_'+i)
                div.append(label);
                div.append(input);
                div.appendTo(this1);    //add to dom
            }
        }
        $(this).attr('data-num',num);       //set current number of month
    });
}
/**
 *
 * @returns {boolean}
 */
function legalCheckBoxAction()
{
    var
        id = $(this).attr('data-id');
    if(id == undefined)
        return false;

    if($(this).prop("checked"))         //если отмечено юр. лицо открываем настройки для резидентов
    {
        $('#'+id).removeClass('hide');
    }else{  //скрываем настройки и сбрасываем параметры
        $('#'+id).addClass('hide');
        $('#'+id+' input[type="checkbox"]').prop('checked',false);
        $('#'+id+' input[type="text"]').val('');
    }
}
/**
 *
 * @returns {*|jQuery}
 */
function addRecordRow()
{
    var
        num = parseInt($(this).attr('data-curr-num'))+1,
        $containter = $('#recordContainer'),
        row = $('<div></div>',{
            class:'form-group'
        })
            .attr('data-col',num)
            .append(
                $('<div></div>',{class:'row'})
                    .append(
                        $('<div></div>',{class:'col-md-4 col-sm-4 col-xs-12'})
                            .append(
                                $('<input/>',{class:'form-control',type:'text',name:"records["+num+"]['from']"})
                            )
                    )
                    .append(
                        $('<div></div>',{class:'col-md-4 col-sm-4 col-xs-12'})
                            .append(
                                $('<input/>',{class:'form-control',type:'text',name:"records["+num+"]['to']"})
                            )
                    )
                    .append(
                        $('<div></div>',{class:'col-md-4 col-sm-4 col-xs-12'})
                            .append(
                                $('<input/>',{class:'form-control',type:'text',name:"records["+num+"]['rate']"})
                            )
                    )
            );
        $containter.append(row);
        $(this).attr('data-curr-num',num);
    return row;
}
/**
 *
 */
function removeRecordRow()
{
    var
        btnAdd = $('#addRecordId'),
        num  = parseInt(btnAdd.attr('data-curr-num'));

    if(num > 0)
    {
        $('#recordContainer .form-group:last').remove();
        num--;
        btnAdd.attr('data-curr-num',num);
    }
}
/**
 *
 * @returns {boolean}
 */
function beforeSubmitFormValidation()
{
    var
        type = parseInt($('#bonusscheme-type').val());
    
    if(type == B_TYPE_PAYMENT_RECORDS && $('#recordContainer .form-group').length == 0)
    {
        addErrorNotify('Сохранение бонусной схемы','Не заданы параметры рекордов');
        return false;
    }
    return false;
}

//документ реади
jQuery(document).ready(function(){
    $('#bonusscheme-num_month').on('change',changeSchemeNumMonth);
    $('#bonusscheme-type').on('change',changeBonusType);
    $('#preloader').remove();
    $('.bonus-scheme-form').removeClass('hide');
    $('.legal-check-box').on('change',legalCheckBoxAction);
    $('#addRecordId').on('click',addRecordRow);
    $('#removeRecordId').on('click',removeRecordRow);
    $(document).on("submit", "form#bonusFormId", beforeSubmitFormValidation);
});

