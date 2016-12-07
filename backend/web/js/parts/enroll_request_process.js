/**
 * Created by zhenya on 27.6.16.
 */
"use strict";
function countDelta()
{
    var
        delta = 0,
        amount =  $('#enrollprocessform-availableamount').val(),
        repay = convertAmountToValid($('#enrollprocessform-repay').val()),
        enroll = $('#enrollprocessform-enroll');
    delta = amount-repay;
    if(delta < 0)
    {
        enroll.val(0);
    }else{
        enroll.val(delta);
    }
    enroll.triggerHandler('change');
}

function initPromisTable(){
    $("#resortingTable").DataTable({
        rowReorder: true,
        "paging":   false,
        "ordering": true,
        "info":     false,
        "searching":     false,
    });
    $("#resortingTable input").on('change',function(){
        var sum = 0;
        $("#resortingTable input:checked").each(function(){
            sum = sum + parseFloat($(this).data('amount'));
        });
        var amount =  $('#enrollprocessform-availableamount').val();
        if(sum > amount){
            sum = amount;
        }
        $('#enrollprocessform-repay').val(sum);
        $('#enrollprocessform-repay').triggerHandler('change');
    });
}
//documents ready
$(function(){
    initPromisTable();
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
                initPromisTable();
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
                    repay.triggerHandler('change');
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
    $('#enrollprocessform-repay,#enrollprocessform-enroll').on('change',function(){
        amountFormatter(this);
    });
    amountFormatter('#enrollprocessform-repay');
    amountFormatter('#enrollprocessform-enroll');


    
});
