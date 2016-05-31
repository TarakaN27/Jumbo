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

$(function () {
    var cnt = 10; //$("#custom_notifications ul.notifications li").length + 1;
    TabbedNotification = function (options) {
        var message = "<div id='ntf" + cnt + "' class='text alert-" + options.type + "' style='display:none'><h2 class='tabled-notification-h2'><i class='fa fa-bell'></i> " + options.title + "</h2><div class='close'><a href='javascript:;' class='notification_close'><i class='fa fa-close'></i></a></div><p>" + options.text + "</p></div>";

        if (document.getElementById('custom_notifications') == null) {
            alert('doesnt exists');
        } else {
            $('#custom_notifications ul.notifications').append("<li><a id='ntlink" + cnt + "' class='alert-" + options.type + "' href='#ntf" + cnt + "'><i class='fa fa-bell animated shake'></i></a></li>");
            $('#custom_notifications #notif-group').append(message);
            cnt++;
            CustomTabs(options);
        }
    };
    CustomTabs = function (options) {
        $('.tabbed_notifications > div').hide();
        $('.tabbed_notifications > div:first-of-type').show();
        $('#custom_notifications').removeClass('dsp_none');
        $('.notifications a').click(function (e) {
            e.preventDefault();
            var $this = $(this),
                tabbed_notifications = '#' + $this.parents('.notifications').data('tabbed_notifications'),
                others = $this.closest('li').siblings().children('a'),
                target = $this.attr('href');
            others.removeClass('active');
            $this.addClass('active');
            $(tabbed_notifications).children('div').hide();
            $(target).show();
        });
    };

    CustomTabs();

    var tabid = idname = '';
    $(document).on('click', '.notification_close', function (e) {
        idname = $(this).parent().parent().attr("id");
        tabid = idname.substr(-2);
        $('#ntf' + tabid).remove();
        $('#ntlink' + tabid).parent().remove();
        $('.notifications a').first().addClass('active');
        $('#notif-group div').first().css('display','block');
    });
});

/**
 *
 * @param title
 * @param body
 * @param type
 */
function abstractTabletnotification(title,body,type)
{
    new TabbedNotification({
        title: title,
        text: body,
        type: type,
        sound: true
    });
}
/**
 *
 * @param title
 * @param body
 */
function addTabledNotificationSuccess(title,body)
{
    abstractTabletnotification(title,body,'success');
}
/**
 *
 * @param title
 * @param body
 */
function addTabledNotificationError(title,body)
{
    abstractTabletnotification(title,body,'error');
}
/**
 *
 * @param title
 * @param body
 */
function addTabledNotificationWarning(title,body)
{
    abstractTabletnotification(title,body,'warning');
}
/**
 *
 * @param title
 * @param body
 */
function addTabledNotificationInfo(title,body)
{
    abstractTabletnotification(title,body,'info');
}
/**
 * @param title
 * @param errorText
 */
function addErrornotificationStickly(title,errorText) {
    new PNotify({
        title: title,
        text: errorText,
        type: "error",
        hide: false
    });
}
/**
 * 
 */
function removeAllNotifications()
{
    PNotify.removeAll();
}