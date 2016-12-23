/** ----------------------------------------------------------------------------------------------------------------
 * Helper Functions
 * ----------------------------------------------------------------------------------------------------------------- */

/**
 * determine if user is logged in
 */
function isUserLoggedIn() {
  return getStoredValue("token");
}

/**
 * Test if browser has access to local storage
 * Following Modernizr's method
 */ 
function localStorageTest() {
  var test = "test";
  try {
    localStorage.setItem(test, test);
    localStorage.removeItem(test);
    return true;
  } catch(e) {
    return false;
  }
}

/**
 * Return a single value from the storage
 * 
 * @param string key
 */ 
function getStoredValue(key) {
  if(localStorageTest()) {
    // return from local storage
    if (localStorage.getItem(key) !== null) {
      return localStorage.getItem(key);
    }
  }
  else {
    // return from cookie
    var name = key + "=";
    var ca = document.cookie.split(";");
    for (var i=0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) === " ") { c = c.substring(1); }
      if (c.indexOf(name) === 0) { return c.substring(name.length,c.length); }
    }
  }
  
  return false;
}

/**
 * Set a single value in storage
 * 
 * @param string key
 * @param {Object} value
 */ 
function setStoredValue(key, value) {
  if(localStorageTest()) {
    // set in local storage
    localStorage.setItem(key, value);
  }
  else {
    // set in cookie
    var d = new Date();
    d.setTime(d.getTime() + (360*24*60*60*1000));
    var expires = "expires="+d.toUTCString();
    document.cookie = key + "=" + value + "; " + expires;
  }
  
  return value;
}

/**
 * Clears a single value in storage
 * 
 * @param string key
 * @param {Object} value
 */ 
function clearStoredValue(key) {
  if(localStorageTest()) {
    // remove in local storage
    localStorage.removeItem(key);
  }
  else {
    // remove in cookie
    document.cookie = key + "=; expires=Thu, 01 Jan 1970 00:00:01 GMT;";
  }
}

/**
 * Clears all values in storage
 * 
 * @param string key
 * @param {Object} value
 */ 
function clearAllStoredValues() {
  if(localStorageTest()) {
    // remove all in local storage
    localStorage.clear();
  }
  else {
    // remove all cookies
    var cookies = document.cookie.split(";");
    for (var i = 0; i < cookies.length; i++){   
        var spcook =  cookies[i].split("=");
        document.cookie = spcook[0] + "=;expires=Thu, 21 Sep 1979 00:00:01 UTC;";                                
    }
  }
}
