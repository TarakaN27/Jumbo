/**
 * Created by zhenya on 24.6.16.
 */
"use strict";
function checkResident()
{
    var
        sel = $('#paymentrequest-is_unknown').val();
    if(sel == 1)
    {
        $('.field-paymentrequest-user_name').removeClass('hide');
    }else{
        $('.field-paymentrequest-user_name').addClass('hide');
    }
}

//document ready
$(function(){
    $("#paymentrequest-cntr_id").on("change",function(){
        var
            cID = $(this).val();
        if(cID != "" && cID !=  undefined)
        {
            $.post( urlGetManager, { cID: cID }, function( data ) {
                    if(data.mID)
                    {
                        $("#paymentrequest-manager_id").val(data.mID).change();
                    }
                    else
                    {
                        $("#paymentrequest-manager_id").val("").change();
                    }
                }, "json")
                .fail(function() {
                    addErrorNotify(errorTitle,errorText)
                });
        }
    });
    checkResident();
    $(".form-payment-request").on("change","#paymentrequest-is_unknown",checkResident);
    $("#paymentrequest-pay_summ").on('change',function(){amountFormatter(this);});
    amountFormatter('#paymentrequest-pay_summ');
});