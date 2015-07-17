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
        nonblock: {
            nonblock: true,
            nonblock_opacity: .2
        }
    });
}