window.g_EsoSkillPopupTooltip = null;
window.g_EsoSkillPopupIsVisible = false;


function CreateEsoSkillPopupTooltip()
{
	g_EsoSkillPopupTooltip = $('<div />').addClass('esoSkillPopupTooltip').hide();
	$('body').append(g_EsoSkillPopupTooltip);
}


window.ShowEsoSkillPopupTooltip = function (parent, skillId, level, health, magicka, stamina, spellDamage, weaponDamage, skillLine)
{
	var linkSrc = "//esolog.uesp.net/skillTooltip.php";
	var dataOk = false;
	var params = "embed";
	
	if (skillLine) params += "&skillline=" + skillLine;
	if (skillId) { params += "&id=" + skillId; dataOk = true; }
	if (level) params += "&level=" + level;
	if (health) params += "&health=" + health;
	if (magicka) params += "&magicka=" + magicka;
	if (stamina) params += "&stamina=" + stamina;
	if (spellDamage) params += "&spelldamage=" + spellDamage;
	if (weaponDamage) params += "&weapondamage=" + weaponDamage;
	
	if (!dataOk) return false;
	
	//params = encodeURIComponent(params);
	
	if (g_EsoSkillPopupTooltip == null) CreateEsoSkillPopupTooltip();
	
	var position = $(parent).offset();
	var width = $(parent).width();
	g_EsoSkillPopupTooltip.css({ top: position.top-50, left: position.left + width });
	
	g_EsoSkillPopupIsVisible = true;

	g_EsoSkillPopupTooltip.load(linkSrc, params, function() {
	
		if (g_EsoSkillPopupIsVisible)
		{
			g_EsoSkillPopupTooltip.show();
			AdjustEsoItemLinkTooltipPosition(g_EsoSkillPopupTooltip, $(parent));
		}

	});
}


function AdjustEsoSkillPopupTooltipPosition(tooltip, parent)
{
     var windowWidth = $(window).width();
     var windowHeight = $(window).height();
     var toolTipWidth = tooltip.width();
     var toolTipHeight = tooltip.height();
     var elementHeight = parent.height();
     var elementWidth = parent.width();
     
     var top = parent.offset().top - toolTipHeight/2 + elementHeight/2;
     var left = parent.offset().left + parent.outerWidth() + 3;
     
     tooltip.offset({ top: top, left: left });
     
     var viewportTooltip = tooltip[0].getBoundingClientRect();
     
     if (viewportTooltip.bottom > windowHeight) 
     {
    	 var deltaHeight = viewportTooltip.bottom - windowHeight + 10;
         top = top - deltaHeight
     }
     else if (viewportTooltip.top < 0)
     {
    	 var deltaHeight = viewportTooltip.top - 10;
         top = top - deltaHeight
     }
         
     if (viewportTooltip.right > windowWidth) 
     {
         left = left - toolTipWidth - parent.width() - 28;
     }
     
     tooltip.offset({ top: top, left: left });
     viewportTooltip = tooltip[0].getBoundingClientRect();
     
     if (viewportTooltip.left < 0 )
     {
    	 var el = $('<i/>').css('display','inline').insertBefore(parent[0]);
         var realOffset = el.offset();
         el.remove();
         
         left = realOffset.left - toolTipWidth - 3;
         tooltip.offset({ top: top, left: left });
     }
     
}


function HideEsoSkillPopupTooltip()
{
	g_EsoSkillPopupTooltip.hide();
	g_EsoSkillPopupIsVisible = false;
}


function OnEsoSkillLinkEnter()
{
	ShowEsoSkillPopupTooltip(this, $(this).attr('skillid'), $(this).attr('level'), $(this).attr('health'), $(this).attr('magicka'), $(this).attr('stamina'), $(this).attr('spelldamage'), $(this).attr('weapondamage'), $(this).attr('skillline'));
}


function OnEsoSkillLinkLeave()
{
	HideEsoSkillPopupTooltip();
}


$( document ).ready(function() {
	$('.esoSkillTooltipLink').hover(OnEsoSkillLinkEnter, OnEsoSkillLinkLeave);
});