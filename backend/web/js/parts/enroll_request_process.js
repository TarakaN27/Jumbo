/**
 * Created by zhenya on 27.6.16.
 */
"use strict";
function countDelta()
{
    var
        delta = 0,
        amount =  $('#enrollprocessform-availableamount').val(),
        repay = $('#enrollprocessform-repay').val(),
        enroll = $('#enrollprocessform-enroll');

    delta = amount-repay;
    if(delta < 0)
    {
        enroll.val(0);
    }else{
        enroll.val(delta);
    }
}
//documents ready
$(function(){
    $('#enrollprocessform-repay').on('change',countDelta);
    $('#enrollprocessform-cuserop').on('change',function(){
        var
            this1 = $(this),
            value = this1.val();

        $.ajax({
            type: 'POST',
            url: urlGetPromisedPayment,
            data: { cuserID: erp_cuserID, cuserOP: value,servID:erp_servID},
            success: function(data){
                $('#promised-payment-table').html(data.grid);
                var ppAmount = parseFloat(data.amount);
                if(ppAmount > 0)
                {
                    var
                        amount =  parseFloat($('#enrollprocessform-availableamount').val()),
                        repay = $('#enrollprocessform-repay');
                    repay.removeAttr('disabled');

                    if(amount > ppAmount)
                    {
                        repay.val(ppAmount);
                    }else{
                        repay.val(amount);
                    }
                    countDelta();
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                addErrorNotify(erp_errorTitle,erp_errorText);
                this1.val('');
                var
                    repay = $('#enrollprocessform-repay');

                repay.val(0);
                repay.attr('disabled','disabled');
                countDelta();
            }
        });
    });
    
});
