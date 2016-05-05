/**
 * Created by zhenya on 29.4.16.
 */
'use strict';
function PWFindCondition()
{
        var
            bClear = true,
            line = $(this).attr('id');


        if(line != undefined)
        {
            let
                lineCopy = line,
                arrTmp = lineCopy.split('-'),
                servID,cntrID,arrTmp.pop();


                arrTmp.push('summ');





        }





        console.log(line);







}
//Bind events
$(function () {
    $('#dynamic-form').on('change','.change-event',PWFindCondition);
});


