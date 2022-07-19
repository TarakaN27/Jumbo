$(function() {
    $('.exchange-rates-link').on('click',function(){
        getCurRates();
    });
    $('#curr-date').on('change',function(){
        var
            containter = $('#exchange-rates-modal');
        containter.removeClass('loaded');

        getCurRates();
    });
});

function getCurRates(){
    var
        containter = $('#exchange-rates-modal'),
        currDate = $('#curr-date').val();

    if(containter && containter.hasClass('loaded'))
    {
        containter.modal();
    }else
    if(containter && !containter.hasClass('loaded'))
    {
        $.ajax({
            url: containter.attr('data-url'),
            method: "POST",
            data: {date:currDate},
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
}