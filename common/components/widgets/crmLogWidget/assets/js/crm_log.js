/**
 * Created by zhenya on 7.4.16.
 */

function loadMsgLog()
{
    var
        container = $('.crm-log-widget'),
        url = container.attr('data-url'),
        page = container.attr('data-page'),
        entity = container.attr('data-entity'),
        itemID = container.attr('data-item');

    if(undefined == page || undefined == entity || undefined == itemID)
    {
        addErrorNotify('История','Ошибка загрузки истории');
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url: url,
        dataType: "json",
        data: {page:page,entity:entity,itemID:itemID},
        success: function(msg){
            var
                table = container.find('table');
            table.append(msg.tr);
        },
        error: function(msg){
            addErrorNotify('История','Ошибка загрузки истории');
            return false;
        }
    });

}

function initEntityHistory() {
    var
        loaded = $('.crm-log-widget').attr('data-loaded');
    if(loaded == 0)
        loadMsgLog();
}