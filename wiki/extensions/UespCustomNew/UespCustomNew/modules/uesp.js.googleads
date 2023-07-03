
var googletag = googletag || {};
googletag.cmd = googletag.cmd || [];
(function() {
var gads = document.createElement('script');
gads.async = true;
gads.type = 'text/javascript';
var useSSL = 'https:' == document.location.protocol;
gads.src = (useSSL ? 'https:' : 'http:') + 
'//www.googletagservices.com/tag/js/gpt.js';
var node = document.getElementsByTagName('script')[0];
node.parentNode.insertBefore(gads, node);
})();

googletag.cmd.push(function() {
googletag.defineSlot('/9709870/Wiki_Bottom_Rectangle', [300, 250], 'div-gpt-ad-1344720487368-0').addService(googletag.pubads());
googletag.defineSlot('/9709870/WikiBottomAnonymous', [300, 250], 'div-gpt-ad-1344720487368-1').addService(googletag.pubads());
googletag.defineSlot('/9709870/WikiLeaderboardTop', [728, 90], 'div-gpt-ad-1344720487368-2').addService(googletag.pubads());
googletag.defineSlot('/9709870/WikiMiddleWideSkyscraper', [160, 600], 'div-gpt-ad-1344720487368-3').addService(googletag.pubads());
googletag.pubads().enableSingleRequest();
googletag.enableServices();
});

var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-1386039-1']);
_gaq.push(['_setDomainName', 'uesp.net']);
_gaq.push(['_trackPageview']);

(function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();


	//Uesp report ad helper functions
function findIFrameHtml (OuterDivID)
{
        if (OuterDivID == null) return;

        OuterDiv = document.getElementById(OuterDivID);
        if (OuterDiv == null) return;

        IFrames = OuterDiv.getElementsByTagName("iframe");
        if (IFrames == null) return;

        if (IFrames[0].contentDocument) return IFrames[0].contentDocument.body.innerHTML;
        return IFrames[0].contentWindow.document.body.innerHTML;
}

function submitReportAdForm(AdId, FormName)
{
        AdHtml = findIFrameHtml(AdId);

        Form = document.forms[FormName];
        if (Form == null) return;

        UespAdContent = Form.elements["UespAdContent"];
        if (AdHtml) UespAdContent.value = AdHtml;

        Form.submit();
}
