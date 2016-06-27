/**
 * Created by zhenya on 27.6.16.
 */
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
        success: function(msg){
    if(!msg)
    {
        addErrorNotify(errorTitleSendAct,actsNotSent);
    }else{
        addSuccessNotify(errorTitleSendAct,actSuccessSent);
        location.reload();
    }
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