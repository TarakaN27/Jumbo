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

    var port = ':8890';
    if(wm_chanel == 'notification_test')
        port = ':8889';
    var socket = io.connect(host+port);
    console.log('connect');
    socket.on(wm_chanel, function (data) { //мониторим канал notification
        var
            message = JSON.parse(data);
        console.log('3423');
        console.log(data);
        proccessingMessages(message);
    });
});