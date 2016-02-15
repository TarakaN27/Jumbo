/**
 * Created by Yauheni Motuz on 6.1.16.
 */
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

var
    intervalID = 0;

/**
 * Часы
 * @returns {boolean}
 */
function clock()
{
    intervalID = setInterval(function (){
        var
            currTime = parseInt($('#workedTime').attr('data-current'));

        if(!$('#workedTime').hasClass('red'))
            $('#workedTime').addClass('red');
        else
            $('#workedTime').removeClass('red');

        if(currTime == undefined)
            return false;

        currTime++;
        $('#workedTime').html(timeFormat(currTime));
        $('#workedTime').attr('data-current',currTime);
    }, 1000);
    return true;
}

$(document).ready(function() {
    // Create two variable with the names of the months and days in an array
    var monthNames = [ "января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря" ];
    var dayNames= ["Воскресенье - ","Понедельник - ","Вторник - ","Среда - ","Четверг - ","Пятница - ","Суббота - "]

    // Create a newDate() object
    var newDate = new Date();
    // Extract the current date from Date object
    newDate.setDate(newDate.getDate());
    // Output the day, date, month and year
    $('#Date').html(newDate.getDate() + ' ' + monthNames[newDate.getMonth()] + ' ' + newDate.getFullYear());

    setInterval( function() {
        // Create a newDate() object and extract the minutes of the current time on the visitor
        var minutes = new Date().getMinutes();
        // Add a leading zero to the minutes value
        $("#min").html(( minutes < 10 ? "0" : "" ) + minutes);
    },1000);

    setInterval( function() {
        // Create a newDate() object and extract the hours of the current time on the visitor
        var hours = new Date().getHours();
        // Add a leading zero to the hours value
        $("#hours").html(( hours < 10 ? "0" : "" ) + hours);
    }, 1000);

    $('body').on('beforeSubmit', 'form#work_form', function () {
        var form = $(this);
        // return false if form still have some validation errors
        if (form.find('.has-error').length) {
            return false;
        }
        // submit form
        $.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            success: function (res) {
                if(res.model != '')
                {
                    addSuccessNotify(WORK_DAY,WORK_DAY_STARTED);
                    location.reload();
                }else{
                    addErrorNotify(WORK_DAY,WORK_DAY_ERROR);
                }
            }
        });
        return false;
    });
    $('body').on('beforeSubmit', 'form#work_form_end', function () {
        var form = $(this);
        // return false if form still have some validation errors
        if (form.find('.has-error').length) {
            return false;
        }

        var
            begin_time = form.find('#workday-begin_time'),
            end_time = form.find('#workday-end_time');

        if(begin_time == undefined || end_time == undefined)
        {
            return false;
        }

        if(begin_time.val() > moment(end_time.val()).unix())
        {
            addErrorNotify(WORK_DAY,WORK_DAY_ERROR_END_TIME);
            return false;
        }

        if(moment().endOf('day').unix() < moment(end_time.val()).unix())
        {
            addErrorNotify(WORK_DAY,WORK_DAY_ERROR_END_TIME);
            return false;
        }


        // submit form
        $.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            success: function (res) {
                if(res.model != '')
                {
                    addSuccessNotify(WORK_DAY,WORK_DAY_ENDED);
                    location.reload();
                }else{
                    addErrorNotify(WORK_DAY,WORK_DAY_ERROR);
                }
            }
        });
        return false;
    });
    $('body').on('beforeSubmit', 'form#work_form_continue', function () {
        var form = $(this);
        // return false if form still have some validation errors
        if (form.find('.has-error').length) {
            return false;
        }
        // submit form
        $.ajax({
            url: form.attr('action'),
            type: 'post',
            data: form.serialize(),
            success: function (res) {
                if(res.model != '')
                {
                    addSuccessNotify(WORK_DAY,WORK_DAY_CONTINUE);
                    location.reload();
                }else{
                    addErrorNotify(WORK_DAY,WORK_DAY_ERROR);
                }
            }
        });
        return false;
    });


});