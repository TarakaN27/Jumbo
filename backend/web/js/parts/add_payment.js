/**
 * Created by zhenya on 7.7.16.
 */
'use strict';
/**
 * 
 */
function countASumm()
{
    var
        aSumm = $("#aSumm"),
        pSumm = $(".psumm"),
        aSummDispl = $("#aSummDispl"),
        tmpSumm = 0;

    $.each( pSumm, function( key, value ) {
        var
            val = $(value).val();
        if(!val)
            val=0;
        tmpSumm+=convertAmountToValid(val);
    });

    var
        $tmp = aSumm.val() - tmpSumm;
    aSummDispl.html(convertAmountToInvalid((aSumm.val() - tmpSumm))+' '+sCurrn);
    if($tmp < 0)
    {
        aSummDispl.removeClass("green");
        aSummDispl.removeClass("yellow");
        if(!aSummDispl.hasClass("red"))
            aSummDispl.addClass("red");
    }
    if($tmp == 0)
    {
        aSummDispl.removeClass("red");
        aSummDispl.removeClass("yellow");
        if(!aSummDispl.hasClass("green"))
            aSummDispl.addClass("green");
    }
    if($tmp > 0)
    {
        aSummDispl.removeClass("red");
        aSummDispl.removeClass("green");
        if(!aSummDispl.hasClass("yellow"))
            aSummDispl.addClass("yellow");
    }
}
/**
 * 
 */
function initBehavior()
{
    $(".psumm").on("change",function(){
        countASumm();
    });
    $(".psumm").on("keyup",function(){
        countASumm();
    });
}
/**
 * 
 * @returns {boolean}
 */
function validateFormLogic()
{
    var
        aSumm = $("#aSumm"),
        pSumm = $(".psumm"),
        tmpSumm = 0;

    $.each( pSumm, function( key, value ) {
        var
            val = $(value).val();
        tmpSumm+=convertAmountToValid(val);
    });

    if(parseFloat(aSumm.val()).toFixed(2) != tmpSumm.toFixed(2))
    {
        addErrorNotify(addPErrorTitle,addPErrorTextFullAmount);
        return false;
    }
    return true;
}
/**
 *
 * @param $this
 * @returns {boolean}
 */
function findCondition($this){

    var
        serviceID = $($this).val(),
        lineID = $($this).attr("id"),
        lPID = iLegalPersonId,
        contrID = iContractorId,
        amount = $("#" + lineID.replace(/-service/gi,"-summ")).val(),
        condID = lineID.replace(/-service/gi,"-condid"),
        condContainer = $(".field-"+condID);

    if(serviceID == "" || amount == " " || amount == undefined || amount == "")
    {
        $("#"+condID).val("");
        $($this).val("");
        addErrorNotify(addPErrorTitle,addPErrorTextFullSetAmountAndService);
        return false;
    }
    let
        preloader = getPreloaderEntity(lineID+"preloader");

    condContainer.append(preloader);
    condContainer.find("select").addClass("hide");
    $.ajax({
        type: "POST",
        cache: false,
        url: urlFindCondition,
        dataType: "json",
        data: {iServID:serviceID,iContrID:contrID,lPID:lPID,amount:convertAmountToValid(amount),prID:iPaymentRequestId},
        success: function(msg){
            showOptions(msg.visable,"#"+condID);
            /*
             if(msg.default != "" && msg.default  != null)
             {
             $("#"+condID).val(msg.default);
             boundsCheckingConditions("#"+condID);
             }
             */
            var
                lineIDCT = lineID.replace(/-service/gi,"-condtype"),
                lineIDCP = lineID.replace(/-service/gi,"-customproduction");
            $("#"+lineIDCT).val(iPayCondTypeUsual);
            $("#"+lineIDCP).attr("disabled","disabled");
            $("#"+lineIDCP).val("");
            $("#"+lineID+"preloader").remove();
            condContainer.find("select").removeClass("hide");
        },
        error: function(msg){
            addErrorNotify(addPErrorTitleCond,addPErrorTextServerErr);
            $("#"+condID).val("");
            $("#"+lineID+"preloader").remove();
            condContainer.find("select").removeClass("hide");
            return false;
        }
    });
}
/**
 * 
 */
function initPayment()
{
    var
        aSumm = $("#aSumm"),
        count = 0,
        pSumm = $(".psumm");


    $.each( pSumm, function( key, value ) {
        count++;
    });

    if(count == 1)
    {
        pSumm.val(aSumm.val());
        countASumm();
    }else{
        if(pSumm.val() == aSumm.val())
        {
            pSumm.val("");
            countASumm();
        }
    }
}


// Проверка суммы на соотвествие границам условия.
function boundsCheckingConditions($this)
{
    if(typeof $this === "number")
    {
        var
            ID = $this;
    }else{
        var
            ID = parseNum($($this).attr("id"));
    }

    if((ID == undefined || ID == "") && ID != 0)
        return false;

    var
        iCondID = $("#addpaymentform-"+ID+"-condid").val(),
        iSumm = $("#addpaymentform-"+ID+"-summ").val();

    if(iCondID == undefined || iCondID == "" || iSumm == undefined || iSumm == "" )
        return false;

    $.ajax({
        type: "POST",
        cache: false,
        url: urlBoundsCheckingConditions,
        dataType: "json",
        data: {iCondID:iCondID,iSumm:convertAmountToValid(iSumm),iCurr:iCurrencyId,payDate:sPayDate},
        success: function(msg){
            if(msg)
            {
                addWarningNotify(addPErrorTitleBoundCheckCond,addPErrorTextBoundCheckCond);
            }else{
            }
        },
        error: function(msg){
            addErrorNotify(addPErrorTitleBoundCheckCond,addPErrorTextServerErr);
            return false;
        }
    });
}
/**
 * @param condID
 * @param lineID
 */
function showOptions(condID,lineID)
{
    var
        select = $(lineID);
    select.val("");

    var
        showAll = $(lineID.replace(/-condid/gi,"-showall")).is(":checked");

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

// по дефолту инициализирцем
function initDefaultCondition()
{
    var
        defaultVal = $("#addpaymentform-0-condid").val(),
        condID = condIdVisible;
    showOptions(condID,"#addpaymentform-0-condid");
    if(defaultVal != undefined && defaultVal != "" && defaultVal != null )
    {
        $("#addpaymentform-0-condid").val(defaultVal);
    }
}

// действия по клику
function showAllBtnActions()
{
    if($(this).is(":checked"))
    {
        var
            lineID = $(this).attr("id").replace(/-showall/gi,"-condid");
        showOptions(new Array(),"#"+lineID);
    }else{
        var
            lineID = $(this).attr("id").replace(/-showall/gi,"-service");
        findCondition("#"+lineID);
    }
}
/**
 *
 */
function actionByCondType()
{
    var
        lineIDCT = $(this).attr("id").replace(/-condid/gi,"-condtype"),
        lineIDCP = $(this).attr("id").replace(/-condid/gi,"-customproduction"),
        value = $(this).val(),
        condType = condTypeMap;
    if(value == undefined || value == "")
    {
        $("#"+lineIDCT).val(iPayCondTypeUsual);
        $("#"+lineIDCP).attr("disabled","disabled");
        $("#"+lineIDCP).val("");

    }else{

        if(condType[value] == iPayCondTypeCustom)
        {
            $("#"+lineIDCT).val(iPayCondTypeCustom);
            $("#"+lineIDCP).removeAttr("disabled");
        }else{
            $("#"+lineIDCT).val(iPayCondTypeUsual);
            $("#"+lineIDCP).attr("disabled","disabled");
            $("#"+lineIDCP).val("");
        }
    }
}
/**
 *
 * @param this1
 * @returns {boolean}
 */
function isSaleCheck(this1)
{
    var
        block = $(this1).offsetParent().offsetParent().find(".maybesale"),
        lines = $(this1).attr("id"),
        check = $("#"+lines.replace(/-service/gi,"-issale")),
        saleUser = $("#"+lines.replace(/-service/gi,"-saleuser")),
        value = $(this1).val();

    if(value == undefined || value == "")
    {
        saleUser.val("");
        check.prop("checked",false);
        block.addClass("hide");
        return false;
    }

    $.ajax({
        type: "POST",
        cache: false,
        url: urlIsSale,
        dataType: "json",
        data: {iServID:value,iContrID:iContractorId,payDate:sPayDate},
        success: function(msg){
            if(msg)
            {
                block.removeClass("hide");
            }else{
                block.addClass("hide");
            }
        },
        error: function(msg){
            block.removeClass("hide");
            addErrorNotify(addPErrorTitleCheckIsSale,addPErrorTextServerErr);
            return false;
        }
    });
}
/**
 *
 */
function initAmountFormater()
{
    var
        arAmounts = $(".dynamicform_wrapper .psumm");
    $.each(arAmounts,function(idx,item){
        amountFormatter(item);
        showByrInfo(item)
    });
}

function showByrInfo(this1)
{
    if(iCurrencyId != 2)
        return false;

    var
        amount = convertAmountToValid($(this1).val());
    $(this1).siblings('.amountInfo').remove();
    $(this1).after( $('<div></div>',{class:'amountInfo'}).html(convertAmountToInvalid(amount*10000) + ' BYR'));
}

//document ready
jQuery(function(){
    countASumm();
    initBehavior();
    $(".dynamicform_wrapper").on("afterInsert", function(e, item) {
        $(item).find(".maybesale").addClass("hide");
        initBehavior();
        initPayment();
        var
            selectDrop = $(this).find(".selectDrop");

        selectDrop.select2("destroy");
        selectDrop.select2();

    });
    $(".dynamicform_wrapper").on("afterDelete", function(e) {
        countASumm();
    });
    $(document).on("submit", "form#dynamic-form", validateFormLogic);
    initPayment();
    initDefaultCondition();
    $(".dynamicform_wrapper").on("change",".showAllBtn",showAllBtnActions);
    $(".dynamicform_wrapper").on("change",".cond-class",actionByCondType);
    $(".dynamicform_wrapper").on("change",".psumm",function(){
        var
            lineID = $(this).attr("id"),
            service = "#" + lineID.replace(/-summ/gi,"-service");
        findCondition(service);
    });
    $(".selectDrop").select2();
    $(".dynamicform_wrapper").on("change",".psumm",function(){
        amountFormatter(this);
        showByrInfo(this);
    });
    initAmountFormater();
});
