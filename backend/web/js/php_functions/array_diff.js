/**
 * Created by zhenya on 23.5.16.
 */
function array_diff (array) {
    var arr_dif = [], i = 1, argc = arguments.length, argv = arguments, key, key_c, found=false;
    for ( key in array ){
        for (i = 1; i< argc; i++){
            found = false;
            for (key_c in argv[i]) {
                if (argv[i][key_c] == array[key]) {
                    found = true;
                    break;
                }
            }
            if(!found){
                arr_dif[key] = array[key];
            }
        }
    }
    return arr_dif;
}
