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
  
  var tokenValue = localStorage.getItem('token');
  
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
      
      $('#loading-spinner').fadeOut(400, function(){
        $('#dataTable-invoices').fadeIn(400);
        displayTable(data, status);
      });  
    },
    error: function() {
      // @TODO: display error
      console.log('Error while getInvoiceList');
    },
  });
}

/**
 * API call to approve or cancel an invoice
 */
function invoiceAction(fquuid, action) {
  
  var tokenValue = localStorage.getItem('token');
  
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
      
      console.log(data);
      
      // @TODO: check if data is valid, not just a status 200
      
      // hide row
      $('#' + data.content.fquuid).hide();
      // close the modal
      $('#confirm-modal').modal('hide');
    },
    error: function() {
      // @TODO: display error
      console.log('Error while invoiceAction');
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
  
  // @TODO: check if data is valid, not just a status 200
  // this data can be empty
  
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
  
  /**
   * Add button event to approve an invoice
   */
  // $(".accept-button").on("click", function(event) {  
  //   var fquuid = $(this).attr('data-fquuid');
  //   invoiceAction(fquuid, 'accept');
  // });
  
  /**
   * Add button event to cancel an invoice
   */
  // $(".decline-button").on("click", function(event) {  
  //   var fquuid = $(this).attr('data-fquuid');
  //   invoiceAction(fquuid, 'decline');
  // });

}

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

$('#btnConfirm').on("click", function(event){
  var fquuid = $(this).attr('data-fquuid');
  var action = $(this).attr('data-action');
  invoiceAction(fquuid, action);
});
