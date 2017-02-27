/**
 * Created by zhenya on 11.5.16.
 */
$(function(){
    "use strict";
    $('#partnerwbookkeeperrequestmanager-legal_id').on('change',function(){
        $(".legal_banks").hide();
        $("#bank"+$(this).val()).show();
    });
});
