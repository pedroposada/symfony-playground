/**
 * invoices.js
 * 
 * Invoice related calls
 */

$(document).ready(function() {
  // do somehting
});

/** ------------------------------------------------------------------------------------
 * API Calls
 * ------------------------------------------------------------------------------------ */

/**
 * API call to retrieve all projects
 */
function getInvoiceList(status) {
  // get token
  var tokenValue = getStoredValue('token');
  
  var url = config.apiBaseUrl + "/orders/admin";
  var data = "status=" + status;
  
  // AJAX call to Clipper backend
  $.ajax({
    url: url,
    type: 'GET',
    data: data,
    dataType: "json",
    contentType: "application/json; charset=utf-8",
    headers: {
      'Authorization': "Bearer " + tokenValue
    },
    beforeSend: function (xhr) {
      $('#dataTable-invoices').hide();
      $('#loading-spinner').show();
    },
    success: function(data) {
      $('#loading-spinner').fadeOut(200, function() {
        $('#dataTable-invoices').fadeIn(200);
        displayTable(data, status);
      });
    },
    error: function() {
      $('#loading-spinner').fadeOut(200, function() {
        displayError('Server side error retrieving list of orders.');
      });
    },
  });
}

/**
 * API call to approve or cancel an invoice
 */
function invoiceAction(fquuid, action) {
  // get token
  var tokenValue = getStoredValue('token');
  
  var url = config.apiBaseUrl + "/orders/adminprocesses";
  var data = "task=" + action + "&firstq_uuid=" + fquuid;
  
  // AJAX call to Clipper backend
  $.ajax({
    url: url,
    type: 'POST',
    data: data,
    contentType: "application/x-www-form-urlencoded",
    headers: {
      'Authorization': "Bearer " + tokenValue
    },
    success: function(data) {
      if(data.status == 200) {
        // hide row
        $('#' + data.content.fquuid).hide();
        // close the modal
        $('#confirm-modal').modal('hide');
      }
      else {
        $('#confirm-modal').modal('hide');
        displayError('Server side error during invoice action.');
      }
    },
    error: function() {
      $('#confirm-modal').modal('hide');
      displayError('Server side error during invoice action.');
    }
  });
}

/** ------------------------------------------------------------------------------------
 * UI functions
 * ------------------------------------------------------------------------------------ */

/**
 * Takes the row template and fills up the info
 */
function displayTable(data, status) {
  
  // get template and table content area
  var row_template = $('#invoice-row-template').html();
  var user_info_template = $('#invoice-user-info-template').html();
  var detail_template = $('#invoice-detail-template').html();
  var table_content = $('#table_rows');
  
  try {
    // For each row
    $.each(data.content, function(key, value) {
      
      // User info
      var userInfo = $(user_info_template);
      userInfo.find('.user-info-username').html(value.user_info.username);
      userInfo.find('.user-info-company').html(value.user_info.company);
      userInfo.find('.user-info-phone').html(value.user_info.phone);
      userInfo.find('.user-info-address').html(value.user_info.address);
      
      // Project detail
      var detailTemplate = $(detail_template);
      $.each(value.markets, function(iKey, iValue){
          detailTemplate.find('.project-detail-markets').append("<li>" + iValue + "</li>");
      });
      $.each(value.specialties, function(iKey, iValue){
          detailTemplate.find('.project-detail-specialties').append("<li>" + iValue + "</li>");
      });
      $.each(value.brands, function(iKey, iValue){
          detailTemplate.find('.project-detail-brands').append("<li>" + iValue + "</li>");
      });
      
      // Whole Row
      var row = $(row_template);
      row.attr('id', value.id);
      row.find('.client-info').html(userInfo);
      row.find('.project-number').html(value.project_number);
      row.find('.project-type').html(value.name);
      row.find('.project-title').html(value.title);
      row.find('.project-details').html(detailTemplate);
      row.find('.project-price').html(value.price);
      
      if (status == 'order_declined') {
        row.find('.project-user').html(value.processed_info.username);
        row.find('.project-time').html(value.processed_info.updated);
      }
      else {
        row.find('.project-time').html(value.updated);
      }
      row.find('.accept-button').attr('data-fquuid', value.id);
      row.find('.accept-button').attr('data-toggle', 'modal');
      row.find('.accept-button').attr('data-target', '#confirm-modal');
      row.find('.accept-button').attr('data-action', 'accept');
  
      row.find('.decline-button').attr('data-fquuid', value.id);
      row.find('.decline-button').attr('data-toggle', 'modal');
      row.find('.decline-button').attr('data-target', '#confirm-modal');
      row.find('.decline-button').attr('data-action', 'decline');
      table_content.hide();
      table_content.append(row);
      table_content.fadeIn();
      
    });
    
    // column definitions according to the status
    var columnDefs = [{ "targets": [6], "orderable": false },{ "targets": [ 5 ], "visible": false }];
    if (status == 'order_declined') {
      columnDefs = [{ "targets": [6], "orderable": false },{ "targets": [ 7 ], "visible": false }];
    }
    // Set the table
    $('#dataTable-invoices').DataTable({
      responsive: true,
      "columnDefs": columnDefs
    });
    
  }
  catch(err) {
    // display error
    $('#loading-spinner').fadeOut(200, function() {
      displayError('Server side error retrieving list of orders. <br/><br/>' + err);
    });
  }
}

/**
 * 
 */
function displayError(message) {
  var errorDiv = $('#error-message');
  errorDiv.html('<div class="alert alert-danger" role="alert"><i class="fa fa-times" id="close-message"></i> ' + message + '</div>');
}

/**
 * Confirm modal box
 */
$('#confirm-modal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget); // Button that triggered the modal
  var action = button.data('action'); // Extract info from data-* attributes
  var fquuid = button.data('fquuid');
  var confirmBody = 'Confirm ' + action + ' ?';
  
  var modal = $(this);
  modal.find('div.modal-body').html(confirmBody);

  $('#btnConfirm').attr('data-fquuid', fquuid);
  $('#btnConfirm').attr('data-action', action);

});

/**
 * Confirm modal box button
 */
$('#btnConfirm').on("click", function(event){
  var fquuid = $(this).attr('data-fquuid');
  var action = $(this).attr('data-action');
  invoiceAction(fquuid, action);
});

/**
 * Error message button
 */
$('#error-message').on('click', '#close-message', function(event){
  $('#error-message').html('');
});
