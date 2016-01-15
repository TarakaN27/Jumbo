/**
 * Created by zhenya on 14.12.15.
 */
function initDefaultState()
{
    $('.dialog-control .formBlock').fadeOut(100);
    $('.msg_list').fadeOut(100);
    $('.btn-show-hide').addClass('hided');
    $('.form-add-msg').fadeOut(100);
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
                $("."+msg.uniqueStr+" textarea").redactor();
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
                $("."+msg.uniqueStr+" textarea").redactor();
                $("."+msg.uniqueStr).fadeOut(100);
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

    var
        text = $(this).find('span'),
        icon = $(this).find('i');

    if($(this).hasClass('hided')) {
        $('.msg_list[data-id="' + dID + '"]').fadeIn(100);
        $(this).removeClass('hided');
        icon.removeClass('fa-chevron-down');
        icon.addClass('fa-chevron-up');
        text.html(HIDE_MSG_TEXT);
        $('.form-add-msg[data-id="'+dID+'"]').fadeIn(100);


        if($('.msg_list[data-id="' + dID + '"]').hasClass('need-load'))
        {
            $('.msg_list[data-id="' + dID + '"]').removeClass('need-load');
            loadMoreComments(dID,true,false);
        }
    }else{
        $('.msg_list[data-id="' + dID + '"]').fadeOut(100);
        $(this).addClass('hided');
        icon.removeClass('fa-chevron-up');
        icon.addClass('fa-chevron-down');
        text.html(SHOW_MSG_TEXT);
        $('.form-add-msg[data-id="'+dID+'"]').fadeOut(100);
    }

    //если диалог не просмотрен и есть новое событие, то нужно вызвать действие "просмотрено" по открытию комментариев
    var
        viewed = $(this).attr('data-viewed');

    if(viewed != undefined && viewed == 'no')
    {
        jQuery.post(DIALOG_VIWED_ACTION,{dialog_id:dID},function(data){
            if(data == 1)
            {
                $('#dialogBlockId_'+dID).removeClass('dialog-not-viewed');
                $(this).removeAttr('data-viewed');
            }

        });  // отправим запрос, что просмотрели
    }
}

function loadMoreComments(id,firstLoad,$this)
{
    id = typeof id !== 'undefined' ? id : false;
    firstLoad = typeof firstLoad !== 'undefined' ? firstLoad : false;
    $this = typeof $this !== 'undefined' ? $this : false;

    var
        url = '';

    if(!$this)
    {
        url = DIALOG_LOAD_COMMENTS;
    }else{
        url = $($this).attr('data-url');
        id = $($this).attr('data-d-id');
        $($this).remove();
    }

    $.ajax({
        type: "POST",
        cache: false,
        url: url,
        dataType: "json",
        data: {dID:id},
        success: function(msg){
            if(msg.content != undefined)
            {
                if(msg.content == '' && firstLoad)
                {
                    $('.msg_list[data-id="'+id+'"]').append('<span class="emptyMsg">'+DIALOG_NO_COMMETS+'</span>');
                }else
                {
                    $('.msg_list[data-id="'+id+'"]').prepend(msg.content);
                }
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