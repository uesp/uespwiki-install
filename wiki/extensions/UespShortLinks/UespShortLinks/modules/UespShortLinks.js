
window.uslOnShortLinkKeyup = function() {
	uslValidateShortLink();	
}


window.uslOnLinkKeyup = function() {
	uslValidateLink();
}


window.uslCheckShortLink = function(url) {
	var shortLinkInput = $("#uslShortLinkText");
	var params = {
			url: '/w/extensions/UespShortLinks/ajax.php',	// TODO: Wiki agnostic link?
			data: { action: 'checklink', link: shortLinkInput.val() },
			success: uslOnCheckShortLinkResult
	};
	
	$.ajax(params);
}


window.uslOnCheckShortLinkResult = function(data, test, xhr) {
	var shortLinkInput = $("#uslShortLinkText");
	
	if (data == null == null || data.isValid == null || !data.isValid || data.isError) {
		var errorMsg = "Short link is not valid or is already in use!";
		shortLinkInput.attr("title", errorMsg);
		shortLinkInput[0].setCustomValidity(errorMsg);
	}
	else {
		uslForceValidateShortLink();	
	}
	
	
	uslValidateForm();
}


window.uslUrlExists = function(url) {
	
		/* Run into issue with CORS */
	return true;
	
	$.ajax({
	    type: 'HEAD',
	    url: url,
	    success: uslOnLinkUrlValid, 
	    error: uslOnLinkUrlInvalid 
	  });
}


window.uslOnLinkUrlValid = function() {
	var linkInput = $("#uslLinkText");
	
	linkInput.attr("title", "");
	linkInput[0].setCustomValidity("");
	
	uslValidateForm();
}


window.uslOnLinkUrlInvalid = function() {
	var linkInput = $("#uslLinkText");
	var errorMsg = "Redirect URL is should point to a valid and accessible website!"
	
	linkInput.attr("title", errorMsg);
	linkInput[0].setCustomValidity(errorMsg);
	
	uslValidateForm();
}


window.uslValidateForm = function() {
	var shortLinkInput = $("#uslShortLinkText");
	var linkInput = $("#uslLinkText");
	var submitButton = $("#uslCreateButton");
	var isValid = true;
	
	isValid &= shortLinkInput[0].checkValidity();
	isValid &= linkInput[0].checkValidity();
	
	submitButton.attr("disabled", !isValid);	
}


window.uslValidateShortLink = function() {
	var shortLinkInput = $("#uslShortLinkText");
	var value = shortLinkInput.val();
	var errorMsg = "";
	
	if (value == "") {
		errorMsg = "Short link can't be blank!";
	}
	else {
		uslCheckShortLink(value);
	}
	
	shortLinkInput.attr("title", errorMsg);
	shortLinkInput[0].setCustomValidity(errorMsg);
	
	uslValidateForm();
	
	return errorMsg == "";
}


window.uslForceValidateShortLink = function() {
	var shortLinkInput = $("#uslShortLinkText");
	
	shortLinkInput.attr("title", "");
	shortLinkInput[0].setCustomValidity("");
	
	uslValidateForm();
}


window.uslValidateLink = function() {
	var linkInput = $("#uslLinkText");
	var value = linkInput.val();
	var errorMsg = "";
	
	if (value == "") {
		errorMsg = "Redirect link can't be blank!";
	}
	else if (!value.match(/(https?:\/\/)?([\w\-])+\.{1}([a-zA-Z]{2,63})([\/\w-]*)*\/?\??([^#\n\r]*)?#?([^\n\r]*)/)) {
		errorMsg = "Redirect link must be a valid URL!";
	}	
	else {	// Async URL check
		uslUrlExists(value);
	}

	linkInput.attr("title", errorMsg);
	linkInput[0].setCustomValidity(errorMsg);
	
	uslValidateForm();
	
	return errorMsg == "";	
}


window.uslOnCreateButtonClicked = function() {
	var submitButton = $("#uslCreateButton");
	
	submitButton.attr("disabled", false);
}


window.uslOnMakeRandomButtonClicked = function() {
	var shortLinkInput = $("#uslShortLinkText");
	var linkInput = $("#uslLinkText");
	
	var params = {
			url: '/w/extensions/UespShortLinks/ajax.php',	// TODO: Wiki agnostic link?
			data: { action: 'makerandom', link: linkInput.val() },
			success: uslOnMakeRandomResult
	};
	
	$.ajax(params);
}


window.uslOnMakeRandomResult = function(data, status, xhr) {
	var shortLinkInput = $("#uslShortLinkText");
	
	if (data == null) return;
	if (data.isValid == null || !data.isValid || data.isError) return;
	
	shortLinkInput.val(data.link);
	uslForceValidateShortLink();
}


window.uslOnPageLoad = function() {
	var shortLinkInput = $("#uslShortLinkText");
	var linkInput = $("#uslLinkText");
	var submitButton = $("#uslCreateButton");
	var makeRandomButton = $("#uslMakeRandomLinkButton");
		
	if (shortLinkInput.length) {
		uslValidateShortLink();
		uslValidateLink();
		
		shortLinkInput.on('keyup', uslOnShortLinkKeyup);
		linkInput.on('keyup', uslOnLinkKeyup);
		submitButton.on('click', uslOnCreateButtonClicked);
		makeRandomButton.on('click', uslOnMakeRandomButtonClicked);
	}		
}


$(document).ready(window.uslOnPageLoad);
