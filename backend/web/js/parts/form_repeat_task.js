'use strict';
function changeEverydayType()
{
    var
        id = $(this).attr('id');
    $('.everyDayType[id!="'+id+'"]').prop('checked',false);
    allowAndDisallowCustomValue();
}
function allowAndDisallowCustomValue()
{
    var
        isChecked = $('#crmtaskrepeat-everyday_custom').prop('checked'),
        value = $('#crmtaskrepeat-everyday_value');
    if(isChecked)
    {
        value.removeAttr('disabled');
    }else{
        value.attr('disabled','disabled');
        value.val(null);
    }
}
function allowAndDisallowMonthlyTypesSections()
{
    /*
    var
        mon1 = $('.mon1'),
        mon2 = $('.mon2'),
        monthlyType = $('input[name = "CrmTaskRepeat[monthly_type]"]:checked').val();

    switch (monthlyType){
        case '1':
            mon1.removeClass('hide');
            mon2.addClass('hide');
            break;
        case '2':
            mon1.addClass('hide');
            mon2.removeClass('hide');
            break;
        default:
            mon1.addClass('hide');
            mon2.addClass('hide');
            break;
    }
    */
}
function switchEndInput()
{
    var
        occBlock = $('.field-crmtaskrepeat-count_occurrences'),
        monthBlock  = $('.field-crmtaskrepeat-end_date'),
        value = $('input[name = "CrmTaskRepeat[end_type]"]:checked').val();

    switch (value) {
        case undefined:
        case '':
        case '1':
            occBlock.addClass('hide');
            monthBlock.addClass('hide');
            break;
        case '2':
            occBlock.removeClass('hide');
            monthBlock.addClass('hide');
            break;
        case '3':
            occBlock.addClass('hide');
            monthBlock.removeClass('hide');
            break;
        default:
            break;
    }

    return true;
}
function allowDisallowRepeatTask()
{
    var
        value = $('input[name="CrmTask[repeat_task]"]:checked').val(),
        block = $('.repeatTaskBlock');

    if(value == 1)
    {
        block.removeClass('hide');
    }else{
        block.addClass('hide');
    }
}
function switchTypes()
{
    var
        daily = $('.blockDaily'),
        weekly = $('.blockWeekly'),
        mothly = $('.blockMonthly'),
        value = $('input[name="CrmTaskRepeat[type]"]:checked').val();

    switch (value){
        case '1':
            daily.removeClass('hide');
            weekly.addClass('hide');
            mothly.addClass('hide');
            break;
        case '2':
            daily.addClass('hide');
            weekly.removeClass('hide');
            mothly.addClass('hide');
            break;
        case '3':
            daily.addClass('hide');
            weekly.addClass('hide');
            mothly.removeClass('hide');
            allowAndDisallowMonthlyTypesSections();
            break;
        default:
            daily.addClass('hide');
            weekly.addClass('hide');
            mothly.addClass('hide');
            break;
    }

    return true;
}
function weekDaySwitcher()
{
    var
        id = $(this).attr('id');
    $('.weekDay[id!="'+id+'"]').prop('checked',false);
}
$(function(){
    $('.everyDayType').on('change',changeEverydayType);             //bind action for change event
    $('.weekDay').on('change',weekDaySwitcher);
    $('#crmtaskrepeat-everyday_custom').on('change',allowAndDisallowCustomValue);
    $('.blockDaily,.blockWeekly,.blockMonthly').addClass('hide');
    $('input[name = "CrmTaskRepeat[end_type]"]').on('change',switchEndInput);
    $('input[name="CrmTask[repeat_task]"]').on('change',allowDisallowRepeatTask);
    $('input[name="CrmTaskRepeat[type]"]').on('change',switchTypes);
    $('input[name = "CrmTaskRepeat[monthly_type]"]').on('change',allowAndDisallowMonthlyTypesSections);
    switchEndInput();
    switchTypes();
    allowDisallowRepeatTask();
    allowAndDisallowCustomValue();
    allowAndDisallowMonthlyTypesSections();

});
