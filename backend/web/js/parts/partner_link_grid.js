/**
 * Created by zhenya on 15.4.16.
 */
$(function(){
    $('.archive-btn').on('click',function(){
        var
            dataVal = $(this).attr('data-value'),
            title = $(this).attr('data-original-title');

        var obj = $.confirm({
            title: title,
            content: 'Дата применения: <input  class="form-control datePicker" type="text" />', // You can also LOAD the html data using Ajax,
            confirm: function(){
                var
                    date = this.$content.find('input.datePicker').val();
                if(date == '' || date == undefined)
                    $.alert({
                        title: title,
                        content: 'Ошибка. Не удалось получить дату!',
                        confirm: function(){

                        }
                    });

                $.ajax({


                });

                return true;
            },
            onOpen: function(){
                this.$content.find('input.datePicker').daterangepicker({
                    singleDatePicker: true,
                    calender_style: "picker_2",
                    locale :{
                        format: 'DD.MM.YYYY',
                    }
                });
            },
            confirmButtonClass: "btn-success ",
            confirmButton: "Да",
            cancelButton: "Нет",
            closeIcon: true,
        });
    });
});