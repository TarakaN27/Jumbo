/**
 * Webmart Soft
 * Created by zhenya on 17.07.15.
 */
/**
 * Добавляем сообщение об ошибке. Non blocked
 * Зависит от библиотеки PNotify
 * @param title
 * @param errorText
 */
function addErrorNotify(title,errorText)
{
    new PNotify({
        title: title,
        text: errorText,
        type: "error",
        //nonblock: {
        //    nonblock: true,
        //    nonblock_opacity: .2
        //}
    });
}
/**
 * @param title
 * @param text
 */
function addSuccessNotify(title,text)
{
    new PNotify({
        title: title,
        text: text,
        type: "success",
        //nonblock: {
        //    nonblock: true,
        //    nonblock_opacity: .2
        //}
    });
}

/**
 * @param title
 * @param text
 */
function addWarningNotify(title,text)
{
    new PNotify({
        title: title,
        text: text,
        type: "warning",
        //nonblock: {
        //    nonblock: true,
        //    nonblock_opacity: .2
        //}
    });
}

/**
 * @param title
 * @param text
 */
function addInfoNotify(title,text)
{
    new PNotify({
        title: title,
        text: text,
        type: "info",
        //nonblock: {
        //    nonblock: true,
        //    nonblock_opacity: .2
        //}
    });
}