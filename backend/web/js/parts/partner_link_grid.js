/**
 * Created by zhenya on 15.4.16.
 */
$(function(){
    $('.archive-btn').on('click',function(){
        var
            btn = $(this),
            pk = $(this).attr('data-pk'),
            dataVal = $(this).attr('data-value'),
            title = $(this).attr('data-original-title');

        $.confirm({
            title: title,
            content: 'Дата применения: <input  class="form-control datePicker" type="text" />', // You can also LOAD the html data using Ajax,
            confirm: function(){
                var
                    date = this.$content.find('input.datePicker').val();
                if(date == '' || date == undefined)
                    addErrorNotify('Архивация связи','Ошибка. Не удалось получить дату!');
                
                $.ajax({
                    type: "POST",
                    cache: false,
                    url:URL_ARCHIVE_LINK,
                    dataType: "json",
                    data: {pk:pk,date:date,val:dataVal},
                    success: function(msg){
                        if(msg && msg != 0)
                        {
                            btn.addClass('red');
                        }else{
                            btn.removeClass('red');
                        }
                        btn.attr('data-value',msg);
                    },
                    error: function(msg){
                        addErrorNotify('Архивация связи',msg.status+'.Не удалось выполнить операцию. ');
                        return false;
                    }
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