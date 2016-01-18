/**
 * Created by zhenya on 18.12.15.
 */
function loadMoreComments()
{
    var
        url = $(this).attr('data-url');
        id = $(this).attr('data-d-id');
        $(this).remove();

    $.ajax({
        type: "POST",
        cache: false,
        url: url,
        dataType: "json",
        data: {dID:id,type:"task"},
        success: function(msg){
            if(msg.content != undefined)
            {
                $('.msg_list[data-id="'+id+'"]').prepend(msg.content);
            }else{
                addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_LOAD_DIALOG);
                return false;
            }
        },
        error: function(msg){
            addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_LOAD_DIALOG);
            return false;
        }
    });
}

function addCmpMessage()
{
    var
        id = $(this).attr("data");

    if (id == "" || id == undefined) {
        addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_EMPTY_ID_TEXT);
        return false;
    }

    var
        content = $(".msgBox[data-id='" + id + "'] textarea").redactor("code.get"),
        formData = $(".msgBox[data-id='" + id + "']").serialize();

    if (content == "" || content == undefined) {
        addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_EMPTY_MSG_TEXT);
        return false;
    }

    $(".msgBox[data-id='" + id + "'] textarea").redactor('code.set', '');   //сбрасываем редактор
    $.ajax({
        type: "POST",
        cache: false,
        url: DIALOG_SEND_CRM_MSG_URL,
        dataType: "json",
        data: formData,
        success: function(msg){
            if(msg.content)
            {
                $('.msg_list[data-id="'+id+'"]').append(msg.content);
                $('.emptyMsg').remove();
            }else{
                addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_ADD_MESSAGE);
                return false;
            }
        },
        error: function(msg){
            addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_ADD_MESSAGE);
            return false;
        }
    });
}