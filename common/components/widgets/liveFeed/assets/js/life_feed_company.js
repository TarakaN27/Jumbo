/**
 * Created by zhenya on 14.12.15.
 */
function initDefaultState()
{
    $('.dialog-control .formBlock').fadeOut(100);
    $('.msg_list').fadeOut(100);
    $('.btn-show-hide').addClass('hided');
}

function addNewDialogBtn()
{
    $('.wraperNewDialog').fadeOut(100);
    $('.dialog-control .formBlock').fadeIn(100);
}

function hideFormShowBtn()
{
    $('.wraperNewDialog').fadeIn(100);
    $('.dialog-control .formBlock').fadeOut(100);
}

function addNewDialog()
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
        url: DIALOG_SEND_MSG_URL,
        dataType: "json",
        data: formData,
        success: function(msg){
            if(msg.content)
            {
                $('.msgBoxList').prepend(msg.content);
                hideFormShowBtn();
                addSuccessNotify(DIALOG_SUCCESS_TITLE,DIALOG_SUCCESS_ADD_DIALOG);
                $('.emptyDialog').remove();
            }else{
                addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_ADD_DIALOG);
                return false;
            }
        },
        error: function(msg){
            addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_ADD_DIALOG);
            return false;
        }
    });
}

function loadMoreCmp()
{
    var
        url = $(this).attr('data-url');
    $(this).remove();

    $.ajax({
        type: "POST",
        cache: false,
        url: url,
        dataType: "json",
        data: {},
        success: function(msg){
            if(msg.content)
            {
                $('.msgBoxList').append(msg.content);
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

/**
 * скрываем показываем комментарии диалога
 */
function showHideComments()
{
    var
        dID = $(this).attr('data-id');

    if($(this).hasClass('hided')) {
        $('.msg_list[data-id="' + dID + '"]').fadeIn(100);
        $(this).removeClass('hided');

        if($('.msg_list[data-id="' + dID + '"]').hasClass('need-load'))
        {
            $('.msg_list[data-id="' + dID + '"]').removeClass('need-load');
            loadMoreComments(dID);
        }
    }else{
        $('.msg_list[data-id="' + dID + '"]').fadeOut(100);
        $(this).addClass('hided');
    }
}

function loadMoreComments()
{

}