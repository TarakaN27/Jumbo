/**
 * Created by Yauheni Motuz on 11.2.16.
 */
var
    cb = function(start, end){
        $('#reportrange span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
    },
    loadTimeSheet = function(picker)
    {
        var
            contener = $('#main-contener');
        contener.html('<div class="loader mrg-auto"></div>');
        $.post(URL_GET_TIMESHEET,{
            startDate: picker.startDate.format('YYYY-MM-DD'),
            endDate: picker.endDate.format('YYYY-MM-DD'),
            user: USER_ID
        })
            .done(function(data) {
                if(data.content != undefined)
                {
                    contener.html(data.content);
                }else{
                    contener.html('');
                }
            })
            .error(function(error){
                contener.html('');
                addErrorNotify(TIMESHEET_TITLE,TIMESHEET_ERROR_LOAD);
            })
        ;
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
