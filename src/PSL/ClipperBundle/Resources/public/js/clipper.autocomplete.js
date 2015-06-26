(function($){
  $( document ).ready(function(){
    
    var clipperurl = "http://external.dev.csb.pslgroup.com";
    
    // brands
    $('#brands, .survey-brand-input').autocomplete({
      ajaxSettings: {
        dataType: 'json',
      },
      paramName: 'keyword',
      // serviceUrl: 'http://localhost:8000/clipper/ws/clipper/autocomplete?group=brands',
      serviceUrl: clipperurl + '/clipper/ws/clipper/autocomplete?group=brands',
      onSelect: function (suggestion) {
        alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
      },
      transformResult: function(response) {
        return {
          suggestions: $.map(response.content, function(dataItem) {
            return { value: dataItem, data: dataItem };
          })
        };
      }
    });
    
    // conditions
    $('#conditions').autocomplete({
      ajaxSettings: {
        dataType: 'json',
      },
      paramName: 'keyword',
      serviceUrl: clipperurl + '/clipper/ws/clipper/autocomplete?group=conditions',
      onSelect: function (suggestion) {
        alert('You selected: ' + suggestion.value + ', ' + suggestion.data);
      },
      transformResult: function(response) {
        return {
          suggestions: $.map(response.content, function(dataItem) {
            return { value: dataItem, data: dataItem };
          })
        };
      }
    });
  });
})(jQuery);


