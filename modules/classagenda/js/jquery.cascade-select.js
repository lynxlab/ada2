/*
* jQuery Cascading Select Lists plug-in 0.8
*
* Licensed under the "do whatever you want with it" licence.
*/

(function($) {
    $j.extend($j.fn, {
        cascade: function(options) {
            var dependendentDdl = $j('#' + options.cascaded);

            var options = $j.extend({}, $j.fn.cascade.defaults, {
                source: options.source, // Source's url
                cascaded: options.cascaded // The ddl element that depends on this list
            }, options);

            if (dependendentDdl.children().length == 0) {
                dependendentDdl.append('<option>' + options.dependentStartingLabel + '</option>');
                if (options.disableUntilLoaded) {
                    dependendentDdl.attr('disabled', 'disabled');
                }
            }

            return this.each(function() {
                var sourceDdl = $j(this);

                sourceDdl.change(function() {
                    var extraParams = {
                        timestamp: +new Date()
                    };

                    $j.each(options.extraParams, function(key, param) {
                        extraParams[key] = typeof param == "function" ? param() : param;
                    });
                    
                    var data = $j.extend({ selected: $j(this).val() }, extraParams);

                    dependendentDdl.empty()
                                    .attr('disabled', 'disabled')
                                    .append('<option>' + options.dependentLoadingLabel + '</option>');


                    if (options.spinnerImg) {
                        dependendentDdl.next('.' + options.spinnerClass).remove();

                        var spinner = $j('<img />').attr('src', options.spinnerImg);
                        $j('<span class="' + options.spinnerClass + '" />').append(spinner).insertAfter(dependendentDdl);
                    }

//                    $j.getJSON(options.source, data).   
                    $j.ajax({
                    	type : 'GET',
                    	url  : options.source,
                    	data : data,
                    	dataType : 'json',
                    	async : false                    	
                    })
                    .done ( function(response) {
                        dependendentDdl.empty().attr('disabled', null);
                        dependendentDdl.next('.' + options.spinnerClass).remove();
                        if (response.length > 0) {
                            $j.each(response, function(i, item) {
                                dependendentDdl.append('<option value=' + item.value + '>' + item.label + '</option>');
                            });
                        } else {
                            dependendentDdl.empty()
                                    .attr('disabled', 'disabled')
                                    .append('<option>' + options.dependentNothingFoundLabel + '</option>');
                        }
                        options.callback();
                    })
                    .always ( function() {
                    	
                    });
                });
            });
        }
    });

    $j.fn.cascade.defaults = {
        sourceStartingLabel: "Select one first",
        dependentNothingFoundLabel: "No elements found",
        dependentStartingLabel: "Select one",
        dependentLoadingLabel: "Loading options",
        disableUntilLoaded: true,
        spinnerClass: "cascading-select-spinner",
        extraParams: {}
    }
})(jQuery);