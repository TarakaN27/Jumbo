/**
 * Created by zhenya on 18.4.16.
 */
jQuery(document).ready(function(){
    $('#preloader').remove();
    $('.partner-schemes-form').removeClass('hide');
    $('.legal-check-box').on('change',function(){
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
    });

    $('.addRange').on('click',function(){
        var
            col = parseInt($(this).attr('data-col')),
            servID = $(this).attr('data-serv'),
            colNext = col + 1 ;
        var 
            tr =  $(document.createElement('tr')),

            td2 = $(document.createElement('td')),
            td3 = $(document.createElement('td')),
            td4 = $(document.createElement('td')),
            inputMin = $(document.createElement('input')),
            inputMax = $(document.createElement('input')),
            inputPercent = $(document.createElement('input')),
            linkTrash = $(document.createElement('a'));

        inputMin.addClass('form-control');
        inputMax.addClass('form-control');
        inputPercent.addClass('form-control');

        inputMin.attr('name','range['+servID+']['+colNext+'][left]');
        inputMax.attr('name','range['+servID+']['+colNext+'][right]');
        inputPercent.attr('name','range['+servID+']['+colNext+'][percent]');

        tr.attr('data-col',colNext+1);
        td2.html(inputMin);
        td3.html(inputMax);
        td4.html(inputPercent);
        
        tr.append(td2);
        tr.append(td3);
        tr.append(td4);

        $('.servRange[data-serv="'+servID+'"]').append(tr);
        $(this).attr('data-col',colNext);
    });

    $('.removeRange').on('click',function(){
        var
            servID = $(this).attr('data-serv'),
            addBtn = $('.addRange[data-serv="'+servID+'"'),
            col = parseInt(addBtn.attr('data-col'));

        if(col == 0)
            return false;

        $('.servRange[data-serv="'+servID+'"] tr:last').remove();
        col = col -1;
        addBtn.attr('data-col',col);
    });
});