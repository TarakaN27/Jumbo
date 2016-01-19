/**
 * Created by Yauheni Motuz on 13.1.16.
 */
/**
 * Обработка сообщения
 * @param data
 */
function proccessingMessages(data)
{
    var
        bFound = false;
    wmu = parseInt(wmu);
    data.wmu.forEach(function(item, i, arr) {
        if(parseInt(item) == wmu)
        {
            bFound = true;
        }
    });
    if(data.type == TYPE_BROADCAST) //общевещательный канал
    {
        abstractTabletnotification(data.name,data.message,data.ntf_type);
    }
    if(data.type == TYPE_PRIVATE && bFound ) //только для определенного пользователя
    {
        abstractTabletnotification(data.name,data.message,data.ntf_type);
    }
}

/**
 * Инициируем сокет
 */
$( document ).ready(function() {
    var socket = io.connect(host+':8890');
    socket.on(wm_chanel, function (data) { //мониторим канал notification
        var
            message = JSON.parse(data);
        proccessingMessages(message);
    });
});