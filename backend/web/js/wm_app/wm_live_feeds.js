/**
 * Webmart Soft
 * Created by zhenya on 17.07.15.
 */
function initDefaultState() {
    $(".dialog_section").fadeOut();
    $(".redactor_panel").fadeOut();
}

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

function sendComment($this) {
    var
        id = $($this).attr("data");

    if (id == "" || id == undefined) {
        addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_EMPTY_ID_TEXT);
        return false;
    }

    var
        content = $(".msgBox[data-id=\'" + id + "\'] textarea").redactor("code.get"),
        formData = $(".msgBox[data-id=\'" + id + "\']").serialize();

    if (content == "" || content == undefined) {
        addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_EMPTY_MSG_TEXT);
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url: DIALOG_SEND_MSG_URL,
        dataType: "json",
        data: formData,
        success: function(msg){
            console.log(msg);
        },
        error: function(msg){

        }
    });
}

//****** Вешаем обработчики *******//
initDefaultState();                                     //инициализируем состояние по умолчанию
$(".open_dialog_button").on("click",function(){         //открытие/закрытие диалога
    fadeDialogBlock(this,"dialog_section");
});
$(".dialog_add_comment_btn").on("click",function(){     //открытие/закрытие формы
    fadeDialogBlock(this,"redactor_panel");
});
$(".sendComment").on("click",function(){
    sendComment(this);
});