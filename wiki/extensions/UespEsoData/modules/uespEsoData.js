

window.uespEsoDataOnDocumentLoaded = function()
{
	var serverStatusDivs = $(".uespEsoServerStatus");
	
	if (serverStatusDivs.length > 0)
	{
		$.ajax({
			url: "https://esolog.uesp.net/getEsoServerStatus.php",
			success: window.uespEsoDataOnReceiveServerStatus,
		});
	
	}
}


window.uespEsoDataOnReceiveServerStatus = function (data)
{
	var serverStatusDivs = $(".uespEsoServerStatus");
	
	//console.log("uespEsoDataOnReceiveServerStatus", data);
	serverStatusDivs.html(serverStatusDivs.html() + data);
}


$(uespEsoDataOnDocumentLoaded);