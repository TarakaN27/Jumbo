/**
 * Webmart Soft
 * Created by zhenya on 27.07.15.
 */
$(".msgBoxAll").fadeOut();
$(".blockRedactor").fadeOut();

/**
 * Скрытие/открытие блоков
 * @param $this
 * @param blockName
 */
function fadeDialogBlock($this, blockName) {
    var
        tagI = $($this).find("i");

    if ($($this).hasClass("open")) {
        $("." + blockName ).fadeOut(500);
        $($this).removeClass("open");
        $(tagI).removeClass("fa-chevron-up");
        $(tagI).addClass("fa-chevron-down");
    } else {
        $("." + blockName).fadeIn(500);
        $($this).addClass("open");
        $(tagI).removeClass("fa-chevron-down");
        $(tagI).addClass("fa-chevron-up");
    }
}

$("#add_new_dialog_id").on('click',function(){
    fadeDialogBlock(this, 'msgBoxAll');
});

$(".btn-add-comment").on("click",function(){
    fadeDialogBlock(this, 'blockRedactor');
});

/**
 *
 * @returns {boolean}
 */
function loadDialogContent()
{
    var
        DDate = $('#dialog-date'),
        DTheme = $('#dialog-theme'),
        DOwner = $('#dialog-owner'),
        ID = $(this).attr("data-id"),
        dataBlock = $('.mail_list[data-id="'+ID+'"]');

    if(!$(".mail_list[data-id='"+ID+"']").hasClass("active"))
        $(".mail_view .view-mail").html("");
    else
        return false;

    $(".mail_list").removeClass("active");
    $(".mail_list[data-id='"+ID+"']").addClass("active");

    var
       theme = $(dataBlock.find("input[name='theme']")).val(),
       owner = $(dataBlock.find("input[name='owner']")).val(),
       date = $(dataBlock.find("input[name='date']")).val();

    DDate.html(date);
    DTheme.html(theme);
    DOwner.html(owner);

    $.ajax({
        type: "POST",
        cache: false,
        url: DIALOG_LOAD_MSG_URL,
        dataType: "json",
        data: {iDID:ID},
        success: function(msg){
            $(".mail_view .view-mail").html(msg.content);
            $(".loadMoreBtn").on("click",loadMoreMsg); //вешаем обработчик подгрузки комментариев
        },
        error: function(msg){
            addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_LOAD_CONTENT);
            return false;
        }
    });
}
/**
 * Подгружает ранние сообщения
 */
function loadMoreMsg()
{
    var
        iPage = $(this).attr("data-page"),
        iDID = $(this).attr("data-d-id");

    $.ajax({
        type: "POST",
        cache: false,
        url: DIALOG_LOAD_MSG_URL,
        dataType: "json",
        data: {iDID:iDID,iPage:iPage},
        success: function(msg){
            $(".loadMoreMsg").remove();
            $(".mail_view .view-mail").prepend(msg.content);
            $(".loadMoreBtn").on("click",loadMoreMsg);
        },
        error: function(msg){
            addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_LOAD_CONTENT);
            return false;
        }
    });
}

$(".dialog-mail").on("click",loadDialogContent); //вешаем подгрузку сообщений
$(document).ready(function(){  //при загрузке страницы грузим первый диалог
   $($(".mail_list:first a")).click();
});

function sendComment()
{
    var
        iDID = $(".mail_list.active").attr("data-id"),
        content = $("#redactorBlock textarea").redactor("code.get");


    console.log(content);
    if (content == "" || content == undefined) {
        addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_EMPTY_MSG_TEXT);
        return false;
    }

    $("#redactorBlock textarea").redactor('code.set', '');   //сбрасываем редактор

    $.ajax({
        type: "POST",
        cache: false,
        url: DIALOG_ADD_MSG_URL,
        dataType: "json",
        data: {iDID:iDID,content:content},
        success: function(msg){
            if($(".mail_list.active").attr("data-id") == msg.iDID)
            {
                var
                    lComm = $(".mail_view .view-mail blockquote:last");
                $(".mail_view .view-mail").append(msg.content);
                if(!lComm.hasClass("blockquote-reverse"))
                    $(".mail_view .view-mail blockquote:last").addClass("blockquote-reverse");
            }
        },
        error: function(msg){
            addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_ADD_CONTENT);
            return false;
        }
    });
}

function addNewDialogs()
{
    var
       ID = $(this).attr("data"),
       content = $(".msgBox[data-id='"+ID+"'] textarea").redactor("code.get");

    if(content == undefined || content == '')
    {
        addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_EMPTY_MSG_TEXT);
        return false;
    }

    var
        formData = $(".msgBox[data-id='" + ID + "']").serialize();
    $(".msgBox[data-id='" + ID + "'] textarea").redactor('code.set', '');   //сбрасываем редактор
    $(".msgBox[data-id='" + ID + "'] select").select2("val", "");
    $.ajax({
        type: "POST",
        cache: false,
        url: DIALOG_ADD_DIALOG_URL,
        dataType: "json",
        data: formData,
        success: function(msg){
            $(".mail_list_column").prepend(msg.content);
            $(".dialog-mail").on("click",loadDialogContent);
            $($(".mail_list:first a")).click();
            addSuccessNotify(DIALOG_SUCCESS_TITLE,DIALOG_SUCCESS_ADD_DIALOG);
        },
        error: function(msg){
            addErrorNotify(DIALOG_ERROR_TITLE,DIALOG_ERROR_ADD_CONTENT);
            return false;
        }
    });
}

$('.addNewDialog').on("click",addNewDialogs);
