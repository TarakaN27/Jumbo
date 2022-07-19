/**
 * Created by zhenya on 7.7.16.
 */
'use strict';
/**
 * 
 */
function findCondition($this){

    var
        serviceID = $($this).val(),
        lineID = $($this).attr("id"),
        lPID = $("#" + lineID.replace(/-service_id/gi,"-legal_id")).val(),
        contrID = $("#" + lineID.replace(/-service_id/gi,"-cntr_id")).val(),
        amount = $("#" + lineID.replace(/-service_id/gi,"-pay_summ")).val(),
        condID = lineID.replace(/-service_id/gi,"-condition_id"),
        condContainer = $(".field-"+condID);

    let
        preloader = getPreloaderEntity(lineID+"preloader");

    condContainer.append(preloader);
    condContainer.find("select").addClass("hide");
    $.ajax({
        type: "POST",
        cache: false,
        url: urlFindCondition,
        dataType: "json",
        data: {iServID:serviceID,iContrID:contrID,lPID:lPID,amount:convertAmountToValid(amount)},
        success: function(msg){
            showOptions(msg.visable,"#"+condID);

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

function showOptions(condID,lineID)
{
    var
        select = $(lineID);
    select.val("");

    select.find("option:not([value=''])").remove();

    $.each(keys, function( index, key ) {
        var
            value = conditions[parseInt(key)];
        if($.inArray(parseInt(key),condID) !== -1)
        {
            select.append("<option value='"+key+"'>"+value+"</option>")
        }
    });
}