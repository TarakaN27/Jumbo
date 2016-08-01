/**
 * Created by zhenya on 7.7.16.
 */
'use strict';
/**
 * 
 */
function condTypeAction()
{
    var
        condTypes = arCondTypes,
        condID = $("#payments-condition_id").val();

    if(condTypes[condID] == '.\common\models\PaymentCondition::TYPE_CUSTOM.')
    {
        $("#payments-customprod").removeAttr("disabled");
    }else{
        $("#payments-customprod").val("");
        $("#payments-customprod").attr("disabled","disabled");
    }
}
/**
 *
 * @returns {boolean}
 */
function findCondition()
{
    var
        iServ = $("#payments-service_id").val(),
        iCuser = $("#payments-cuser_id").val(),
        amount = $("#payments-pay_summ").val(),
        iCurr = $("#payments-currency_id").val(),
        payDate = $("#payments-pay_date").val(),
        iLP = $("#payments-legal_id").val(),
        condContainer = $(".field-payments-condition_id");

    if(iServ == "" || iLP == "" || iCuser == "")
    {
        $("#payments-condition_id").val("");
        return false;
    }

    let
        preloader = getPreloaderEntity("preloader");
    condContainer.append(preloader);
    condContainer.find("select").addClass("hide");
    $.ajax({
        type: "POST",
        cache: false,
        url: urlFindCondition,
        dataType: "json",
        data: {iServID:iServ,iContrID:iCuser,lPID:iLP,amount:convertAmountToValid(amount),iCurr:iCurr,payDate:payDate},
        success: function(msg){
            showOptions(msg.visable,"#payments-condition_id");
            $("#preloader").remove();
            condContainer.find("select").removeClass("hide");
            /*
             if(msg.default != "" && msg.default  != null)
             {
             $("#payments-condition_id").val(msg.default);
             boundsCheckingConditions("#"+condID);
             condTypeAction();
             }
             */
        },
        error: function(msg){
            addErrorNotify(titleCondFind,errorCondFind);
            $("#preloader").remove();
            condContainer.find("select").removeClass("hide");
            return false;
        }
    });
}

// Проверка суммы на соотвествие границам условия.
function boundsCheckingConditions()
{

    var
        payDate = $("#payments-pay_date").val(),
        iCondID = $("#payments-condition_id").val(),
        iSumm = $("#payments-pay_summ").val(),
        iCurr = $("#payments-currency_id").val();

    if(iCondID == undefined || iCondID == "" || iSumm == undefined || iSumm == "" || iCurr == undefined || iCurr == "" || payDate == undefined || payDate == "" )
        return false;

    $.ajax({
        type: "POST",
        cache: false,
        url: urlBoundsCheckingConditions,
        dataType: "json",
        data: {iCondID:iCondID,iSumm:convertAmountToValid(iSumm),iCurr:iCurr,payDate:strtotime(payDate)},
        success: function(msg){
            if(msg)
            {
                addWarningNotify(titleBoundsCheck,errorBoundsCheck);
            }
        },
        error: function(msg){
            addErrorNotify(titleBoundsCheck,addPErrorTextServerErr);
            return false;
        }
    });
}
/**
 *
 * @param condID
 * @param lineID
 */
function showOptions(condID,lineID)
{
    var
        select = $(lineID);
    select.val("");

    var
        showAll = $("#show_all_id").is(":checked");

    select.find("option:not([value=''])").remove();

    $.each(keys, function( index, key ) {
        var
            value = conditions[parseInt(key)];
        if(showAll || $.inArray(parseInt(key),condID) !== -1)
        {
            select.append("<option value='"+key+"'>"+value+"</option>")
        }
    });
}

// действия по клику
function showAllBtnActions()
{
    if($(this).is(":checked"))
    {
        showOptions(new Array(),"#payments-condition_id");
    }else{
        findCondition();
    }
}

function showByrInfo(this1)
{
    $(this1).siblings('.amountInfo').remove();
    if($('#payments-currency_id').val() != 2)
        return false;

    var
        amount = convertAmountToValid($(this1).val());
    $(this1).after( $('<div></div>',{class:'amountInfo'}).html(convertAmountToInvalid(amount*10000) + ' BYR'));
}

/**
 *
 */
function initDefaultCondition()
{
    var
        condID = arCondIdVisible;
    showOptions(condID,"#payments-condition_id");
    $("#payments-condition_id").val('.$model->condition_id.');
    condTypeAction();
}

jQuery(function(){
    $("#payments-cuser_id").on("change",findCondition);
    // по дефолту инициализирцем
    initDefaultCondition();
    $("#show_all_id").on("change",showAllBtnActions);
    $("#payments-condition_id").on("change",function(){
        condTypeAction();
    });
    $("#payments-pay_summ").on("change",function(){
        findCondition();
        amountFormatter(this);
        showByrInfo(this);
    });

    $("#payments-pay_date").on("change",function(){
        $.ajax({
            type: "POST",
            cache: false,
            url: urlGetCondition ,
            dataType: "json",
            data: {date:$(this).val()},
            success: function(msg){
                var
                    conditions = msg;
                $.each( $("#payments-condition_id").find("option:not([value=''])"), function( key1, value ) {
                    var
                        key = $(value).attr("value");

                    $(value).html(conditions[key]);
                });
            },
            error: function(msg){
                addErrorNotify(titleBoundsCheck,addPErrorTextServerErr);
                return false;
            }
        });
    });

    $('#payments-currency_id').on('change',function(){
        showByrInfo("#payments-pay_summ");
    });

    amountFormatter("#payments-pay_summ");
    showByrInfo("#payments-pay_summ");
});