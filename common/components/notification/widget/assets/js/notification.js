/**
 * Created by Yauheni Motuz on 13.1.16.
 */
/**
 * Обработка сообщения
 * @param data
 */
function proccessingMessages(data)
{
    if(data.type == TYPE_BROADCAST) //общевещательный канал
    {
        abstractTabletnotification(data.name,data.message,data.ntf_type);
    }
    if(data.type == TYPE_PRIVATE && jQuery.inArray(wmu, data.wmu) >= 0 ) //только для определенного пользователя
    {
        abstractTabletnotification(data.name,data.message,data.ntf_type);
    }
}

/**
 * Инициируем сокет
 */
$( document ).ready(function() {
    var socket = io.connect('http://localhost:8890');
    socket.on(wm_chanel, function (data) { //мониторим канал notification
        var
            message = JSON.parse(data);
        proccessingMessages(message);
    });
});