/**
 * Created by zhenya on 27.6.16.
 */
"use strict";
function sendActs()
{
    var
        items = $('.selectedActs:checked');

    if(items == undefined || items.length == 0)
    {
        alert(errorNotSelectedActs);
        return false;
    }
    $.ajax({
        type: 'POST',
        cache: false,
        url: urlSendAct,
        dataType: 'json',
        data: items.serialize(),
        success: function(data){
            var
                text = '';
            if(data.success != undefined && data.success.length > 0)
            {
                text+='Успешно добавлены в очередь на отправку акты с ID: ';
                $.each(data.success,function(ind,value){
                    text+=value+',';
                    if(data.success.length != ind+1)
                    {
                        text+=',';
                    }
                });
            }

            if(data.error != undefined && data.error.length > 0)
            {
                text+='<br/> Не удалось добавить в очередь на отправку акты с ID: ';
                $.each(data.error,function(ind,value){
                    text+=value;
                    if(data.error.length != ind+1)
                    {
                        text+=',';
                    }
                });
                text+=' <br/>Проверьте наличие файла и емаила на который необходимо отправить акт'
            }

            $.alert({
                title: 'Отправка актов',
                content: text
            });
        },
    error: function(msg){
        addErrorNotify(errorTitleSendAct,actServerError);
        return false;
    }
});
}

$(function () {
    $('#sendActID').on('click',sendActs);
    $('.editable').editable({                                   //editable plugin
        clear: false,
        validate: function(value) {
            var
                tmpVal = $.trim(value);

            if(tmpVal == '') {
                return 'This field is required';
            }
            if(!tmpVal.match(emailPattern))
            {
                return 'Invalid email';
            }
        }
    });
});