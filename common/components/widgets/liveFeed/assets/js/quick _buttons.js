/**
 * Created by zhenya on 6.6.16.
 */
"use strict";
/**
 * @returns {boolean}
 */
function clickEventsCmpListChangeTaskStatus()
{
    var
        taskId = $(this).attr('data-id');

    if(customEmpty(taskId))
    {
        addErrorNotify('Изменение статуса','Не удалось получить ID задачи');
        return false;
    }
    $('#cmp-task-status-id').val(taskId);
    $.ajax({
        type: "POST",
        cache: false,
        url:'/service/ajax-service/get-task-info',
        dataType: "json",
        data: {taskId:taskId},
        success: function(objTask){
            var
                modalWindow = $('#cmp-list-task-upd-status');

            modalWindow.find('.task_name').html(objTask.model.title);
            modalWindow.find('.task_status').html(objTask.statusStr);
            modalWindow.find('.task_status').val(objTask.model.status);

            var
                selectOptions = modalWindow.find('#crm_list_task_status option');

            selectOptions.each(function(index,value){
                if(jQuery.inArray(parseInt($(value).val()),objTask.availableStatus) == -1)
                {
                    $(value).attr('disabled','disabled')
                }else{
                    $(value).removeAttr('disabled');
                }
            });
            modalWindow.modal();
        },
        error: function(msg){
            addErrorNotify('Изменение статуса','Не удалось выполнить запрос');
            return false;
        }
    });
}
/**
 *
 */
function cmpEventSaveNewTaskStatus()
{
    var
        modalWindow = $('#cmp-list-task-upd-status'),
        taskId = $('#cmp-task-status-id').val(),
        newStatus = $('#crm_list_task_status').val();

    $.ajax({
        type: "POST",
        cache: false,
        url:'/service/ajax-service/change-task-status',
        dataType: "json",
        data: {taskId:taskId,newStatus:newStatus},
        success: function(objTask){
            addSuccessNotify('Изменение статуса задачи','Задача: '+objTask.model.title+'<br/>'+'Статус задачи изменен на '+objTask.statusStr);
            modalWindow.find('.close').trigger('click');
        },
        error: function(msg){
            addErrorNotify('Изменение статуса','Не удалось выполнить запрос');
            return false;
        }
    });
}











//bind events after window is ready
$(function(){
    $('.messages').on('click','.cmp_list_change_task_status',clickEventsCmpListChangeTaskStatus);
    $('#cmp-list-task-upd-status').on('click','.cmp-task-save-status',cmpEventSaveNewTaskStatus);
});