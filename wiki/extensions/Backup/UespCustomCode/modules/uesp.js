/* Old Code, no longer used? */
(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

ga('create', 'UA-1386039-1', 'auto');
ga('send', 'pageview');

	// Curse Ad Header
window.factorem = {};
window.factorem.slotSizes =  [
	[[728, 90]],
	[[300,250]],
	[[300,250]],
	[[728,90]],
	[[728, 90]],
	[[300,250]],
	[[160,600]],
	[[160,600]],
];
var script = document.createElement('script');
var tstamp = new Date();
script.id = 'factorem';
script.src =  '//cdm.cursecdn.com/js/uesp/cdmfactorem_min.js?sec=home&misc=' + tstamp.getTime();
script.async = false;
script.type='text/javascript';
document.head.appendChild(script);

