$(function() {
    $('.exchange-rates-link').on('click',function(){
        var
            containter = $('#exchange-rates-modal');

        if(containter && containter.hasClass('loaded'))
        {
            containter.modal();
        }else
        if(containter && !containter.hasClass('loaded'))
        {
            $.ajax({
                url: containter.attr('data-url'),
                method: "POST",
                data: {},
                dataType: "json",
                success: function(msg){
                    if(msg)
                    {
                        containter.find('.modal-body').html(msg);
                        containter.addClass('loaded');
                        containter.modal();
                    }
                },
                error: function(msgError){

                }
            });
        }


    })
});