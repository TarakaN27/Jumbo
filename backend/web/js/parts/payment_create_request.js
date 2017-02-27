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
                    if(data.banks){
                        window.banks = data.banks;
                        $.each(data.banks, function(key,value){
                            $(".legal_banks").each(function(){
                                if($("select",this).find('option[value="'+value +'"]').length>0){
                                    $("select",this).val(value);
                                }
                            });
                        });
                    }


                }, "json")
                .fail(function() {
                    addErrorNotify(errorTitle,errorText)
                });
        }
    });
    checkResident();
    $(".form-payment-request").on("change","#paymentrequest-is_unknown",checkResident);
    $("#paymentrequest-pay_summ").on('change',function(){
        amountFormatter(this);
        var amount = convertAmountToValid($(this).val());
        $(this).siblings('.amountInfo').remove();
        $(this).after( $('<div></div>',{class:'amountInfo'}).html(convertAmountToInvalid(amount*10000) + ' BYR'));
    });
    amountFormatter('#paymentrequest-pay_summ');

    var defaultBankSelect = $("#paymentrequest-legal_id").val();
    $("#bank"+defaultBankSelect).show();

    $("#paymentrequest-legal_id").on("change", function(){
        $(".legal_banks").hide();
        $("#bank"+$(this).val()).show();
    });

    $(".legal_banks select").on("change", function(){
        var selectedBank = $(this).val();
        var legalId = $("#paymentrequest-legal_id").val();
        $("#invalidBank").hide();
        if(window.banks){
            if(window.banks[legalId] != selectedBank){
                $("#invalidBank").show();
            }
        }
    });

});