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



    console.log(bFound);
    console.log(data.type);
    console.log(jQuery.inArray(wmu, data.wmu));



    if(data.type == TYPE_BROADCAST) //общевещательный канал
    {
        console.log('1111');
        abstractTabletnotification(data.name,data.message,data.ntf_type);
    }
    if(data.type == TYPE_PRIVATE && bFound ) //только для определенного пользователя
    {
        console.log('22222');
        abstractTabletnotification(data.name,data.message,data.ntf_type);
    }
    console.log(data);
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