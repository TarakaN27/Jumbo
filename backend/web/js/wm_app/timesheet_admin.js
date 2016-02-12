/**
 * Created by Yauheni Motuz on 12.2.16.
 */
var
    cb = function(start, end){
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    },
    loadTimeSheet = function(picker)
    {
        var
            contener = $('#main-contener'),
            typeLoad = contener.attr('data-type'),
            userID = contener.attr('data-user-id');

        contener.html('<div class="loader mrg-auto"></div>');

        if(typeLoad == 'one_user') {
            $.post(URL_GET_TIMESHEET, {
                startDate: picker.startDate.format('YYYY-MM-DD'),
                endDate: picker.endDate.format('YYYY-MM-DD'),
                user: userID
            })
                .done(function (data) {
                    if (data.content != undefined) {
                        contener.html(data.content);
                    } else {
                        contener.html('');
                    }
                })
                .error(function (error) {
                    contener.html('');
                    addErrorNotify(TIMESHEET_TITLE, TIMESHEET_ERROR_LOAD);
                })
            ;
        }else{
            $.post(URL_GET_USER_TIMESHEET, {
                startDate: picker.startDate.format('YYYY-MM-DD'),
                endDate: picker.endDate.format('YYYY-MM-DD')
            })
                .done(function (data) {
                    if (data.content != undefined) {
                        contener.html(data.content);
                    } else {
                        contener.html('');
                    }
                })
                .error(function (error) {
                    contener.html('');
                    addErrorNotify(TIMESHEET_TITLE, TIMESHEET_ERROR_LOAD);
                })
            ;
        }

    },
    initAtReady = function() {
        cb(moment().startOf('month'), moment().endOf('month')); //устанавливаем по умолчанию текущий месяц
        $('#reportrange').daterangepicker({     //вешаем плагин выбора периода
            ranges: {
                'Сегодня': [moment(), moment()],
                'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Последние 7 дней': [moment().subtract(6, 'days'), moment()],
                'Последние 30 дней': [moment().subtract(29, 'days'), moment()],
                'Текущий месяц': [moment().startOf('month'), moment().endOf('month')],
                'Предыдущий месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            startDate: moment().startOf('month'), endDate: moment().endOf('month'),
            locale: {
                format: 'MM/DD/YYYY',
                separator: ' - ',
                applyLabel: 'Применить',
                cancelLabel: 'Отменить',
                weekLabel: 'W',
                customRangeLabel: 'Задать период',
                daysOfWeek: moment.weekdaysMin(),
                monthNames: moment.monthsShort(),
                firstDay: moment.localeData().firstDayOfWeek()
            }
        }, cb);

        loadTimeSheet($('#reportrange').data('daterangepicker'));   //грузим timesheet
    },
    onApplayedDaterenge = function(ev, picker){
        loadTimeSheet(picker);
    };

//вешаем обработчики событий
$( document ).ready(initAtReady);
$('#reportrange').on('apply.daterangepicker',onApplayedDaterenge);
$('#toListID').on('click',function(){
    $(this).addClass('hide');
    var
        contener = $('#main-contener');
    contener.attr('data-type','users');
    contener.attr('data-user-id','');
    loadTimeSheet($('#reportrange').data('daterangepicker'));
});
$('#main-contener').on('click','.ts-user-col',function(){
    var
        contener = $('#main-contener');
    contener.attr('data-type','one_user');
    contener.attr('data-user-id',$(this).attr('data-id'));

    loadTimeSheet($('#reportrange').data('daterangepicker'));
    $('#toListID').removeClass('hide');
});