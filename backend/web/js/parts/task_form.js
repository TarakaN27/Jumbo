/**
 * Created by zhenya on 27.4.16.
 */
"use strict";
function showCmpInfo()
{
    var
        container = $('.field-crmtask-cmp_id'),
        ul = $(document.createElement('ul')),

        cmp = $('#crmtask-cmp_id')
    ;
    
    ul.addClass('cmpInfo');
    ul.addClass('col-md-offset-3');

    let
        oldInfo = container.find('.cmpInfo');
    if(oldInfo != undefined && oldInfo != '')
    {
        oldInfo.remove();
    }

    if(cmp == undefined || cmp == ''  || cmp.val() == undefined || cmp.val() == '')
    {
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url:URL_CMP_INFO,
        dataType: "json",
        data: {pk:cmp.val()},
        success: function(data){
            for (var key in data) {
                let
                    liCopy = $(document.createElement('li'));
                let
                    value = data[key] == null ? '--' : data[key];
                liCopy.html(key+': '+value);
                ul.append(liCopy);
            }
            container.append(ul);
        },
        error: function(msg){
            addErrorNotify('Получение инфо о компании',msg.status+'.Не удалось выполнить операцию. ');
            return false;
        }
    });

}
$(function() {
    showCmpInfo();
    $('#crmtask-cmp_id').on('change',showCmpInfo);
});


