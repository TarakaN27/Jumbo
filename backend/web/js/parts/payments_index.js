/**
 * Created by zhenya on 27.6.16.
 */
"use strict";
function deletePayments()
{

}


$(function () {
   $(".payDelete").click(function () {
       var
           element_id = $(this).attr('data-id'),
           post_data = {'id':element_id,'approve':false},
           result_string;
       $.ajax({
           type: "POST",
           url: 'delete',
           dataType: 'json',
           data: post_data,
           success: function(data){
               $.confirm({
                   title: 'Удаление платежей'+"<br>",
                   content: data+"<br>",
                   buttons: {
                       Ok: function () {
                           post_data = {'id':element_id,'approve':true};
                           $.ajax({
                               type: "POST",
                               url: 'delete',
                               dataType: 'json',
                               data: post_data,
                               success: function(data){
                                   if(data['approve'] == 'done'){
                                       $.alert({
                                           title: 'Успешно удалено',
                                           content: ''
                                       });

                                       $.ajax({
                                           type: "POST",
                                           url: 'delete',
                                           dataType: 'json',
                                           data: data
                                       });
                                   }
                                   console.log(data);
                               },
                               error: function(msg){
                                   console.log('Error');
                                   console.log(msg);
                                   console.log(msg['responseText']);
                               }
                           });
                       },
                       Cancel: function () {
                           $.alert({
                               title: '',
                               content:"Отменено"+"<br>"+"<br>"
                           });
                       }
                   }
               });

           },
           error: function(msg){
               console.log(msg);
               $.alert({
                   title: 'Ошибка',
                   content: msg['responseText']
               });
           }
       });
   });

    $(".promisePayDelete").click(function () {
        var
            element_id = $(this).attr('data-id'),
            post_data = {'id':element_id,'approve':false},
            result_string;
        $.ajax({
            type: "POST",
            url: 'delete',
            dataType: 'json',
            data: post_data,
            success: function(data){
                $.confirm({
                    title: 'Удаление обещанных платежей'+"<br>",
                    content: data+"<br>",
                    buttons: {
                        Ok: function () {
                            post_data = {'id':element_id,'approve':true};
                            $.ajax({
                                type: "POST",
                                url: 'delete',
                                dataType: 'json',
                                data: post_data,
                                success: function(data){
                                    if(data['approve'] == 'done'){
                                        $.alert({
                                            title: 'Успешно удалено',
                                            content: ''
                                        });

                                        $.ajax({
                                            type: "POST",
                                            url: 'delete',
                                            dataType: 'json',
                                            data: data
                                        });
                                    }
                                    console.log(data);
                                },
                                error: function(msg){
                                    console.log('Error');
                                    console.log(msg);
                                    console.log(msg['responseText']);
                                }
                            });
                        },
                        Cancel: function () {
                            $.alert({
                                title: '',
                                content:"Отменено"+"<br>"+"<br>"
                            });
                        }
                    }
                });

            },
            error: function(msg){
                console.log(msg);
                $.alert({
                    title: 'Ошибка',
                    content: msg['responseText']
                });
            }
        });
    });



});


