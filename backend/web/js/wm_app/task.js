/**
 * Created by Yauheni Motuz on 21.12.15.
 */
var
    intervalID = 0;
/**
 * Форматированный вывод времени
 */
var timeFormat = (function (){
    function num(val){
        val = Math.floor(val);
        return val < 10 ? '0' + val : val;
    }
    return function (ms){
        var sec = ms
            , hours = sec / 3600  % 24
            , minutes = sec / 60 % 60
            , seconds = sec % 60
            ;
        return num(hours) + ":" + num(minutes);
    };
})();

/**
 * Часы
 * @returns {boolean}
 */
function clock()
{
    intervalID = setInterval(function (){
        var
            action = $('.user-time').attr('data-action'),
            currTime = parseInt($('.user-time').attr('data-current'));

        if(!action)
            return false;

        if(!$('.user-time').hasClass('red'))
            $('.user-time').addClass('red');
        else
            $('.user-time').removeClass('red');

        if(currTime == undefined || action == undefined)
            return false;

        currTime++;
        $('.user-time').html(timeFormat(currTime));
        $('.user-time').attr('data-current',currTime);
    }, 1000);
    return true;
}
/**
 * начать выполнение таска
 * @returns {boolean}
 */
function beginTask()
{
    var
        $this = this,
        tID = $(this).attr('data-task-id');

    if(tID == undefined || tID == "")
    {
        alert('Error. Не указан ID задачи');
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url: URL_BEGIN_TASK,
        dataType: "json",
        data: {tID:tID},
        success: function(msg){
            if(msg.code)
            {
                //$('.user-time').attr('data-log-id',msg.success);
                //clock(); //запускаем таймер
                $($this).addClass('hide'); //скрываем текущую кнопку
                $('.pause-task').removeClass('hide'); //показываем кнопку приостановить
                $('.done-task').removeClass('hide');
                $('#taskStatusID').html(msg.text);
                addSuccessNotify(TASK,TASK_BEGIN_SUCCESS)
            }else{
                addErrorNotify(TASK,msg.error);
            }
        },
        error: function(msg){
            alert(msg);
            return false;
        }
    });
}
/**
 * Останавливаем таймер
 */
function stopClock()
{
    return clearInterval(intervalID);
}

/**
 * @returns {boolean}
 */
function pauseTask()
{
    var
        $this = this,
        tID = $(this).attr('data-task-id');

    if(tID == undefined || tID == "")
    {
        alert('Error. Не указан ID задачи');
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url: URL_PAUSE_TASK,
        dataType: "json",
        data: {tID:tID},
        success: function(msg){
            if(msg.code)
            {
                //$('.user-time').attr('data-log-id',0);
                //stopClock(); //запускаем таймер
                $($this).addClass('hide'); //скрываем текущую кнопку
                $('.begin-task').removeClass('hide'); //показываем кнопку приостановить
                $('.done-task').addClass('hide');
                $('#taskStatusID').html(msg.text);
                addSuccessNotify(TASK,TASK_TIME_TRACKING_PAUSE_SUCCESS)
            }else{
                addErrorNotify(TASK,msg.error);
            }
        },
        error: function(msg){
            alert(msg);
            return false;
        }
    });
}

function doneTask()
{
    var
        $this = this,
        tID = $(this).attr('data-task-id');

    if(tID == undefined || tID == "" )
    {
        alert('Error. Не указан ID задачи');
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url: URL_DONE_TASK,
        dataType: "json",
        data: {tID:tID},
        success: function(msg){
            if(msg.code)
            {
                $($this).addClass('hide'); //скрываем текущую кнопку
                $('.begin-task,.pause-task').addClass('hide');

                $('.open-task').removeClass('hide');
                $('#taskStatusID').html(msg.text);
                addSuccessNotify(TASK,TASK_DONE_SUCCESS)
            }else{
                addErrorNotify(TASK,msg.error);
            }
        },
        error: function(msg){
            alert(msg);
            return false;
        }
    });
}

function openTask()
{
    var
        $this = this,
        tID = $(this).attr('data-task-id');

    if(tID == undefined || tID == "" )
    {
        alert('Error. Не указан ID задачи');
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url: URL_OPEN_TASK,
        dataType: "json",
        data: {tID:tID},
        success: function(msg){
            if(msg.code)
            {
                $($this).addClass('hide'); //скрываем текущую кнопку
                $('.begin-task').removeClass('hide');
                $('#taskStatusID').html(msg.text);
                addSuccessNotify(TASK,TASK_OPEN_SUCCESS)
            }else{
                addErrorNotify(TASK,msg.error);
            }
        },
        error: function(msg){
            alert(msg);
            return false;
        }
    });



}

//вешаем обработчики на события
jQuery(document).ready(function(){
    if(CLOCK_ON_LOAD)
        clock();
    jQuery('.company-time-control').on('click','.begin-task',beginTask);
    jQuery('.company-time-control').on('click','.pause-task',pauseTask);
    jQuery('.company-time-control').on('click','.done-task',doneTask);
    jQuery('.company-time-control').on('click','.open-task',openTask);
});

