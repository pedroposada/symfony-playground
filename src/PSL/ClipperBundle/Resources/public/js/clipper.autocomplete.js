(function($){
  $( document ).ready(function(){
    
    // var clipperurl = "http://localhost:8000";
    var clipperurl = "http://external.dev.csb.pslgroup.com/clipper-001/web";
    
    var autocomplete_options = {
      ajaxSettings: {
        dataType: 'json',
      },
      paramName: 'keyword',
      transformResult: function(response) {
        return {
          suggestions: $.map(response.content, function(dataItem) {
            return { value: dataItem, data: dataItem };
          })
        };
      }
    };
    
    // styles
    // <link rel="stylesheet" href="http://external.dev.csb.pslgroup.com/clipper-001/web/bundles/pslclipper/css/jquery.autocomplete.css">
    $('head').append('<link rel="stylesheet" type="text/css" href="' + clipperurl + '/bundles/pslclipper/css/jquery.autocomplete.css">');
    
    // conditions
    autocomplete_options.serviceUrl = clipperurl + '/clipper/ws/clipper/autocomplete?group=conditions';
    $('#conditions, #field_survey_patient_type').autocomplete(autocomplete_options);
    
    // brands
    autocomplete_options.serviceUrl = clipperurl + '/clipper/ws/clipper/autocomplete?group=brands';
    $('#brands, .survey-brand-input').autocomplete(autocomplete_options);
    $('#add-brand-button').on('click', function(){
      $('#brands, .survey-brand-input').autocomplete(autocomplete_options);
    });
    
  });
})(jQuery);


