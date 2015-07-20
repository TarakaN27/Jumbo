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
                    initDefaultState();
                    bindEventsToBlock();
                }else{
                    $(".dialog_section[data-id='"+msg.dialogID+"'] div.block_content").append(msg.content);
                }
                addSuccessNotify(DIALOG_SUCCESS_TITLE,DIALOG_SUCCESS_ADD_COMMENT);
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

//****** Вешаем обработчики *******//
initDefaultState();             //инициализируем состояние по умолчанию
bindEventsToBlock();

