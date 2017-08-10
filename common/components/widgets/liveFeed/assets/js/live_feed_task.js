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
                $("#dropzoneCommentpreview"+msg.dialogID).html('');
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

function deleteMsg(this1)
{
    $.confirm({
        title:MESSAGE,
        content: CONFIRM_DELETE_MSG,
        buttons: {
            Да: function() {
                var
                    pk = $(this1).attr('data-id');
                if(pk == undefined)
                {console.log('pk == undefined');
                    addErrorNotify(MESSAGE,MSG_ERROR_DEL);
                }else{
                    $.ajax({
                        type: "POST",
                        cache: false,
                        url:DIALOG_DEL_MSG_URL,
                        dataType: "json",
                        data: {pk:pk},
                        success: function(msg){
                            if(msg)
                            {
                                $('.message_wrapper .li-msg[data-id='+pk+']').remove();
                                return true;
                            }else{
                                addErrorNotify(MESSAGE,MSG_ERROR_DEL);
                                return false;
                            }
                        },
                        error: function(msg){
                            addErrorNotify(MESSAGE,MSG_ERROR_DEL);
                            return false;
                        }
                    })
                }
            },
            Нет: function() {
            }
        }
    });
}
function updateMsg(this1)
{
    var
        pk = $(this1).attr('data-id');

    if(pk == undefined)
    {
        addErrorNotify(MESSAGE,MSG_ERROR_UPDATE);
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url:DIALOG_UPDATE_MSG+'?pk='+pk,
        dataType: "json",
        data: {},
        success: function(msg){
            if(msg.type == 'form')
            {
                $('#update-msg-dialog .modal-body').html(msg.body);
                $('#update-msg-dialog').attr('data-id',pk);
                $('#update-msg-dialog .modal-body .upd-textarea').redactor();
                $('#update-msg-dialog').modal();
            }else{
                addErrorNotify(MESSAGE,MSG_ERROR_UPDATE);
                return false;
            }
        },
        error: function(msg){
            addErrorNotify(MESSAGE,MSG_ERROR_UPDATE);
            return false;
        }
    })
}
/**
 * @returns {boolean}
 */
function updateMsgSend()
{
    var
        pk = $('#update-msg-dialog').attr('data-id'),
        data = $('#update-msg-dialog').find('form.upd-msg').serialize();

    if(pk == undefined)
    {
        addErrorNotify(MESSAGE,MSG_ERROR_UPDATE);
        return false;
    }
    $.ajax({
        type: "POST",
        cache: false,
        url:DIALOG_UPDATE_MSG+'?pk='+pk,
        dataType: "json",
        data: data,
        success: function(msg){
            if(msg.type == 'upd' && msg.status == 'ok')
            {
                $('.li-msg[data-id='+pk+'] .message').html(msg.msg);
                $('#update-msg-dialog .close').trigger('click');
            }else{
                addErrorNotify(MESSAGE,MSG_ERROR_UPDATE);
                return false;
            }
        },
        error: function(msg){
            addErrorNotify(MESSAGE,MSG_ERROR_UPDATE);
            return false;
        }
    })
}
$(function(){
    $('.message_wrapper').on('click','.msg-trash',function(){
        deleteMsg(this);
    });
    $('.message_wrapper').on('click','.msg-edit',function(){
        updateMsg(this);
    });
    $('#update-msg-dialog .btn-save').on('click',function(){
        updateMsgSend();
    })
});