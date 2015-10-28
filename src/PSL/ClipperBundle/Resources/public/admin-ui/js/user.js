/**
 * user.js
 * 
 * User related calls
 */

$(document).ready(function() {
  
  /**
   * Login button action
   */
  $("#login-btn").on("click", function(event) {
    
    event.preventDefault();
    clearMessage();
    
    // validation
    var username = $("#input-username").val().trim();
    var password = $("#input-password").val().trim();
    
    var error = false;
    if (!username || !password) {
      var error = "Username or password missing.";
    }
    
    if (error) {
      $("#message-area").addClass("alert alert-danger");
      $("#message-area").html(error);
    }
    else {
      login(username, password);
    }
  });
  
  /**
   * Login button action
   */
  $(".logout-button").on("click", function(event) {
    logout();
  });
  
});

/** ------------------------------------------------------------------------------------
 * API Calls
 * ------------------------------------------------------------------------------------ */

/**
 * API Call to log user
 */
function login(username, password) {
  
  // AJAX call to Clipper backend
  var url = config.apiBaseUrl + "/admin/login_check";
  var data = "username=" + username +"&password=" + password;

  $.ajax({
    url: url,
    type: 'POST',
    data: data,
    contentType: "application/x-www-form-urlencoded",
    success: function(data) {
      
      localStorage.setItem('token', data.token);
      
      //redirect to pages/invoices.html
      window.location.replace("pending-invoices.html");
    },
    error: function() {
      console.log(data);
      $("#message-area").html("Error logging in");
    }
  });
}

/** ------------------------------------------------------------------------------------
 * UI functions
 * ------------------------------------------------------------------------------------ */

/**
 * Log out and kill (yes murder) the session
 */
function logout() {
  // delete storage
  localStorage.clear();
  // redirects to front page
  window.location.replace("login.html");
}

/** ------------------------------------------------------------------------------------
 * helper functions
 * ------------------------------------------------------------------------------------ */

/**
 * Redirect to login if no session
 */
function verifyAuthentication() {
  if (localStorage.getItem('token') === null) {
    window.location.replace("login.html");
  }
}

/**
 * Clear message
 */
function clearMessage() {
  $("#message-area").html("");
  $("#message-area").removeAttr("class");
}
