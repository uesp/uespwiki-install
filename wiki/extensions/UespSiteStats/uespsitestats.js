function addEvent(obj, evType, fn) { 

 if (obj.addEventListener){ 
   obj.addEventListener(evType, fn, false); 
   return true; 
 } else if (obj.attachEvent){ 
   var r = obj.attachEvent("on"+evType, fn); 
   return r; 
 } else { 
   return false; 
 } 
}

function GetXmlHttp() 
{
  if (window.XMLHttpRequest)
  {
    return new XMLHttpRequest();
  }
  else if (window.ActiveXObject)
  {
    return new ActiveXObject("Microsoft.XMLHTTP");
  }

  return null;
}

function getStat (ajaxpage, host, user, elementid) {
  var xmlhttp = GetXmlHttp();
  var StartTime = (new Date()).getTime();
  var page = "";

  page = UESP_BASE_URL + "/extensions/UespSiteStats/";
  page += ajaxpage + "?";
  if (host) page += "host=" + host + "&";
  if (user) page += "user=" + user + "&";

  xmlhttp.open("GET", page, true);
  xmlhttp.send(null);

  xmlhttp.onreadystatechange = function() {
    var StopTime = (new Date()).getTime();

    if (xmlhttp.readyState == 4) {
      var element = document.getElementById(elementid);
   
      if (element) {
        text = xmlhttp.responseText;
        text += "\n<small>(Server Response Time = ";
        text += StopTime - StartTime;
	text += "ms)</small>";
	element.innerHTML = text;
      }
    }
  }

}


function getMemory (host, elementid) {
  getStat("getmemory.php", host, null, elementid);
}

function getUptime (host, elementid) {
  getStat("getuptime.php", host, null, elementid);
}

function getDiskUsage (host, elementid) {
  getStat("getdiskusage.php", host, null, elementid);
}

function getMasterDBStatus (host, user, elementid) {
  getStat("getmasterdbstatus.php", host, user, elementid);
}

function getSlaveDBStatus (host, user, elementid) {
  getStat("getslavedbstatus.php", host, user, elementid);
}

function getIfConfig (host, elementid) {
  getStat("getifconfig.php", host, null, elementid);
}
