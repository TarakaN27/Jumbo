/**
 * Created by zhenya on 13.5.16.
 */
'use strict';
function swInitSelect2(item,selectData)
{
    console.log('1----');
    console.log(selectData);

    let
        select2DataItem = selectData == undefined || selectData == '' ? select2Data : selectData;

    console.log(select2DataItem);
    item.select2({
        data : select2DataItem,
        ajax: {
            url: ajaxSelectGetCmpUrl,
            dataType: 'json',
            delay: 250,
            data: function(params) { return {q:params.term}; },
            processResults: function (data, params) {return {results: data.results};},
            cache: true
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work
        minimumInputLength: 1,
        templateResult: function(cmp_id) { return cmp_id.text; }, // omitted for brevity, see the source of this page
        templateSelection: function (cmp_id) { return cmp_id.text; }, // omitted for brevity, see the source of this page
    });
}
function initDatePicker(item)
{
    item.daterangepicker({
        singleDatePicker: true,
        calender_style: "picker_2",
        locale :{
            format: 'DD.MM.YYYY',
        }
    });
}

function initPartnerLinkModal()
{
    let
        modal = $('#activity-modal');                               //get modal container

    modal.modal();                                                  //show modal window
    let
        preloader = getPreloaderEntity('partnerModalPreloader');    //get preloader entity
    modal.find('.modal-body').html(preloader);                      //put preloader to modal body
    $.ajax({
        type: "POST",
        cache: false,
        url:ajaxMultiLinkFormUrl,
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
}
//add-item
function addLinksGroups()
{
    var
        servicesArr = $('.servicesGroups input:checked'),
        cntr = $('#partnermultilinkform-cntr').val(),
        date = $('#partnermultilinkform-date').val();

    if(cntr == undefined || date == undefined || cntr == '' || date == '')
    {
        addErrorNotify('Добавление связи', 'Отсутствуют обязательные параметры!');
        return false;
    }

    if(servicesArr == undefined || servicesArr.length == 0)
    {
        addErrorNotify('Добавление связи', 'Не выбраны услуги!');
        return false;
    }

    var
        data = [],
        selectOptions = $(document.createElement('option'));

        selectOptions.attr('value',cntr);
        selectOptions.html($('#partnermultilinkform-cntr option[value='+cntr+']').html());

    data[cntr] = $('#partnermultilinkform-cntr option[value='+cntr+']').html();

    var
        rand = Math.floor((Math.random() * 10000) + 1);
    servicesArr.each(function( index ) {
        $('#dynamic-form .add-item:first').trigger('click');
        $('.dynamicform_wrapper .item:last .wm-select2').addClass('rand_'+rand);
        $('.dynamicform_wrapper .item:last .service').val($(this).val());
        $('.dynamicform_wrapper .item:last .datePicker').val(date);
    });

    $('.dynamicform_wrapper .item .rand_'+rand).select2("destroy");
    $('.dynamicform_wrapper .item .rand_'+rand).append(selectOptions);
    $('.dynamicform_wrapper .item .rand_'+rand).val(cntr);
    $('.dynamicform_wrapper .item .rand_'+rand).trigger('change');
    swInitSelect2($('.dynamicform_wrapper .item .rand_'+rand),data);

    $('#activity-modal .modal-dialog button.close').click();
}

//document ready
$(function(){
    swInitSelect2($('.wm-select2'));
    initDatePicker($('.datePicker'));
    $('.dynamicform_wrapper').on('afterInsert', function(e, item) {
        swInitSelect2($(item).find('.wm-select2'));
        initDatePicker($(item).find('.datePicker'));
    });
    $('#multipleLinkAdd').on('click',initPartnerLinkModal);
    $('#activity-modal').on('click','#idAddLinks',addLinksGroups)
});