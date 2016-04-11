/**
 * Created by Yauheni Motuz on 22.3.16.
 */
jQuery(document).ready(function(){
    $('#bonusscheme-num_month').on('change',function(){
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
    });

    $('#bonusscheme-type').on('change',function(){
        var
            numMonth = $('#bonusscheme-num_month'),
            type = $(this).val();

        if(type == B_TYPE_UNIT)
        {
            $('.type2,.type3').addClass('hide');
            $('.type1').removeClass('hide');
            numMonth.attr('disabled','disabled');
            numMonth.val('');
            numMonth.trigger('change');
        }else if(type == B_TYPE_SIMPLE || type == B_TYPE_COMPLEX)
        {
            $('.type1').addClass('hide');
            $('.type2,.type3').removeClass('hide');

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
        }else{
            $('.type1,.type2,.type3').addClass('hide');
            numMonth.removeAttr('disabled','disabled');
            numMonth.val('');
            numMonth.trigger('change');
        }
    });

    //
    $('#preloader').remove();
    $('.bonus-scheme-form').removeClass('hide');

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
});

