/**
 * Webmart Soft
 * Created by zhenya on 17.07.15.
 */
/**
 * Устанавливаем default состояние
 */
function initDefaultState() {
    $(".dialog_section").fadeOut();
    $(".redactor_panel").fadeOut();
    $(".msgBoxAll").fadeOut();
}
/**
 * добавляем события кликов
 */
function bindEventsToBlock()
{
    $(".open_dialog_button").on("click",function(){         //открытие/закрытие диалога
        fadeDialogBlock(this,"dialog_section");
    });
    $(".dialog_add_comment_btn").on("click",function(){     //открытие/закрытие формы
        fadeDialogBlock(this,"redactor_panel");
    });
    $(".btn-msg-for-all").on("click",function(){     //открытие/закрытие формы нового диалога
        fadeDialogBlock(this,"msgBoxAll");
    });
    $(".sendComment").on("click",function(){ //добавление комментария
        sendComment(this,true);
    });
}
/**
 * eбиваем события кликов
 */
function killEventsForBlock()
{
    $(".open_dialog_button").off("click");
    $(".dialog_add_comment_btn").off("click");
    $(".btn-msg-for-all").off("click");
    $(".sendComment").off("click");
}

/**
 * Скрытие/открытие блоков
 * @param $this
 * @param blockName
 */
function fadeDialogBlock($this, blockName) {
    var
        tagI = $($this).find("i"),
        id = $($this).attr("data");

    if ($($this).hasClass("open")) {
        $("." + blockName + "[data-id='" + id + "']").fadeOut(500);
        $($this).removeClass("open");
        $(tagI).removeClass("fa-chevron-up");
        $(tagI).addClass("fa-chevron-down");
    } else {
        $("." + blockName + "[data-id='" + id + "']").fadeIn(500);
        $($this).addClass("open");
        $(tagI).removeClass("fa-chevron-down");
        $(tagI).addClass("fa-chevron-up");
    }

    var
        viewed = $($this).attr('data-viewed');

    if(viewed != undefined && viewed == 'no')
    {
        jQuery.post(DIALOG_VIEWED_ACTION,{dialog_id:id},function(data){
            if(data == 1)
            {
                $('#dialogBlockId_'+id).removeClass('dialog-not-viewed');
                $($this).removeAttr('data-viewed');
            }

        });  // отправим запрос, что просмотрели
    }
}

/**
 * Добавляем новый комментарий.
 * @param $this
 * @returns {boolean}
 */
function sendComment($this,updateComment) {
    var
        id = $($this).attr("data");

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
            if(msg.status)
            {
                if(msg.newDialog)
                {
                    $('.msgBoxList').prepend(msg.content);
                    $(".msgBox[data-id='" + msg.dialogID + "'] textarea").redactor();
                    killEventsForBlock();
                    initDefaultState();
                    bindEventsToBlock();
                    addSuccessNotify(DIALOG_SUCCESS_TITLE,DIALOG_SUCCESS_ADD_DIALOG);
                }else{
                    $(".dialog_section[data-id='"+msg.dialogID+"'] div.block_content").append(msg.content);
                    addSuccessNotify(DIALOG_SUCCESS_TITLE,DIALOG_SUCCESS_ADD_COMMENT);
                }
            }else{
                addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_ADDCOMMENT);
                return false;
            }
        },
        error: function(msg){
            addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_ADDCOMMENT);
            return false;
        }
    });
}
/**
 *
 * @param page
 */
function loadMoreLiveFeedDialogs(page)
{
    $.ajax({
        type: "POST",
        cache: false,
        url: DIALOG_LOAD_MORE_LF_DIALOGS,
        dataType: "json",
        data: {page:page},
        success: function(msg){
            $('.loadMoreBlock').remove();
            $('.msgBoxList').append(msg);
            killEventsForBlock();
            $(".msgBoxList ul[data-pages='"+page+"'] textarea").redactor();
            initDefaultState();             //инициализируем состояние по умолчанию
            bindEventsToBlock();
        },
        error: function(msg){
            addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_LOAD_CONTENT);
            return false;
        }
    });
}
//****** Вешаем обработчики *******//
initDefaultState();             //инициализируем состояние по умолчанию
bindEventsToBlock();

function deleteMsg(this1)
{
    $.confirm({
        title:MESSAGE,
        content: CONFIRM_DELETE_MSG,
        confirm: function() {
            var
                pk = $(this1).attr('data-id');
            if(pk == undefined)
            {
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
                            $('.msgBoxList .li-msg[data-id='+pk+']').remove();
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
        cancel: function() {
        },
        confirmButton: "Да",
        cancelButton: "Нет",
        confirmButtonClass: "btn-success ",
        cancelButtonClass: "btn-default mrg-bottom-5",
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
                $('.li-msg[data-id='+pk+'] .excerpt').html(msg.msg);
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
    $('.msgBoxList').on('click','.msg-trash',function(){
        deleteMsg(this);
    });
    $('.msgBoxList').on('click','.msg-edit',function(){
        updateMsg(this);
    });
    $('#update-msg-dialog .btn-save').on('click',function(){
        updateMsgSend();
    })
});

