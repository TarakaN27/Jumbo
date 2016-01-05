/**
 * Created by Yauheni Motuz on 5.1.16.
 */
if (!window.Globalize) window.Globalize = {
    format: function(number, format) {
        number = String(this.parseFloat(number, 10) * 1);
        format = (m = String(format).match(/^[nd](\d+)$/)) ? m[1] : 2;
        for (i = 0; i < format - number.length; i++)
            number = '0'+number;
        return number;
    },
    parseFloat: function(number, radix) {
        return parseFloat(number, radix || 10);
    }
};

(function($){
    window.durationPicker = {
        instances: {},
        init: function(id, secs) {
            var container = $('#'+id);
            if (!container[0]) {
                console.log('No such ID: '+id);
                return false;
            }
            container.empty();
            this.instances[id] = {
                container: container,
                hrs: $('<input id="'+id+'_h" value="0" style="text-align:right; width:44px;" />').appendTo(container),
                mins: $('<input id="'+id+'_m" value="0" style="text-align:right; width:22px;" />').appendTo(container),
                secs: $('<input id="'+id+'_s" value="0" style="text-align:right; width:22px;" />').appendTo(container),
                total: $('<input id="'+id+'_t" type="hidden" name="'+id+'" value="0" />').appendTo(container)
            };
            this.instances[id].hrs.after(':');
            this.instances[id].mins.after(':');
            this.instances[id].secs.spinner({
                numberFormat: 'd2',
                spin: function(event, ui) {
                    if (ui.value >= 60) {
                        $(this).spinner('value', ui.value - 60);
                        durationPicker.instances[id].mins.spinner('stepUp');
                        durationPicker.spinMins(id, durationPicker.instances[id].mins.spinner('value'));
                        return false;
                    }
                    else if (ui.value < 0) {
                        if (durationPicker.instances[id].mins.spinner('value') || durationPicker.instances[id].hrs.spinner('value')) {
                            $(this).spinner('value', ui.value + 60);
                            durationPicker.instances[id].mins.spinner('stepDown');
                            durationPicker.spinMins(id, durationPicker.instances[id].mins.spinner('value'));
                        }
                        else
                            $(this).spinner('value', 0);
                        return false;
                    }
                },
                stop: function() {durationPicker.tally(id);}
            });
            this.instances[id].mins.spinner({
                numberFormat: 'd2',
                spin: function(event, ui) {return durationPicker.spinMins(id, ui.value);},
                stop: function() {durationPicker.tally(id);}
            });
            this.instances[id].hrs.spinner({
                min: 0,
                stop: function() {durationPicker.tally(id);}
            });
            this.set(id, secs);
            return true;
        },
        spinMins: function(id, val) {
            if (val >= 60) {
                durationPicker.instances[id].mins.spinner('value', val - 60);
                durationPicker.instances[id].hrs.spinner('stepUp');
                return false;
            }
            else if (val < 0) {
                if (durationPicker.instances[id].hrs.spinner('value')) {
                    durationPicker.instances[id].mins.spinner('value', val + 60);
                    durationPicker.instances[id].hrs.spinner('stepDown');
                }
                else
                    durationPicker.instances[id].mins.spinner('value', 0);
                return false;
            }
            durationPicker.tally(id);
        },
        tally: function(id) {
            this.instances[id].total.val(this.hmsToSecs(
                this.instances[id].hrs.spinner('value'),
                this.instances[id].mins.spinner('value'),
                this.instances[id].secs.spinner('value')
            ));
            return false; // important
        },
        hmsToSecs: function(h, m, s) {
            return  (parseInt(h) * 3600) + (parseInt(m) * 60) + parseInt(s);
        },
        secsToHms: function(seconds) {
            var hms = {total: parseInt(seconds)};
            hms.hrs = parseInt(hms.total / 3600);
            var mins = hms.total % 3600;
            hms.secs = mins % 60;
            hms.mins = parseInt(mins / 60);
            return hms;
        },
        set: function(id, seconds) {
            var hms = this.secsToHms(seconds);
            this.instances[id].hrs.spinner('value', hms.hrs);
            this.instances[id].mins.spinner('value', hms.mins);
            this.instances[id].secs.spinner('value', hms.secs);
            this.instances[id].total.val(hms.total);
        },
        setHms: function(id, h, m, s) {
            this.set(id, this.hmsToSecs(h, m, s));
        },
        get: function(id) {
            this.tally(id);
            return this.instances[id].total.val();
        },
        getHms: function(id) {
            return this.secsToHms(this.get(id));
        }
    };
})(jQuery);

