/* <pre><nowiki> */

// ============================================================
// BEGIN Dynamic Navigation Bars (experimantal)
// This is used in Template:Showhide and similar.
// Taken from http://en.wikipedia.org/wiki/MediaWiki:Monobook.js
 function addLoadEvent(func) 
{
  if (window.addEventListener) 
    window.addEventListener("load", func, false);
  else if (window.attachEvent) 
    window.attachEvent("onload", func);
}

// set up the words in your language
var NavigationBarHide = '[ Hide ]';
var NavigationBarShow = '[ Show ]';

// set up max count of Navigation Bars on page,
// if there are more, all will be hidden
// NavigationBarShowDefault = 0; // all bars will be hidden
// NavigationBarShowDefault = 1; // on pages with more than 1 bar all bars will be hidden
var NavigationBarShowDefault = 0;


// shows and hides content and picture (if available) of navigation bars
// Parameters:
//     indexNavigationBar: the index of navigation bar to be toggled
function toggleNavigationBar(indexNavigationBar)
{
   var NavToggle = document.getElementById("NavToggle" + indexNavigationBar);
   var NavFrame = document.getElementById("NavFrame" + indexNavigationBar);

   if (!NavFrame || !NavToggle) {
       return false;
   }

   // if shown now
   if (NavToggle.firstChild.data == NavigationBarHide) {
       for (
               var NavChild = NavFrame.firstChild;
               NavChild != null;
               NavChild = NavChild.nextSibling
           ) {
           if (NavChild.className == 'NavPic') {
               NavChild.style.display = 'none';
           }
           if (NavChild.className == 'NavContent') {
               NavChild.style.display = 'none';
           }
       }
   NavToggle.firstChild.data = NavigationBarShow;

   // if hidden now
   } else if (NavToggle.firstChild.data == NavigationBarShow) {
       for (
               var NavChild = NavFrame.firstChild;
               NavChild != null;
               NavChild = NavChild.nextSibling
           ) {
           if (NavChild.className == 'NavPic') {
               NavChild.style.display = 'block';
           }
           if (NavChild.className == 'NavContent') {
               NavChild.style.display = 'block';
           }
       }
   NavToggle.firstChild.data = NavigationBarHide;
   }
}

// adds show/hide-button to navigation bars
function createNavigationBarToggleButton()
{
   var indexNavigationBar = 0;
   // iterate over all < div >-elements
   for(
           var i=0; 
           NavFrame = document.getElementsByTagName("div")[i]; 
           i++
       ) {
       // if found a navigation bar
       if (NavFrame.className == "NavFrame") {

           indexNavigationBar++;
           var NavToggle = document.createElement("a");
           NavToggle.className = 'NavToggle';
           NavToggle.setAttribute('id', 'NavToggle' + indexNavigationBar);
           NavToggle.setAttribute('href', 'javascript:toggleNavigationBar(' + indexNavigationBar + ');');
           
           var NavToggleText = document.createTextNode(NavigationBarHide);
           NavToggle.appendChild(NavToggleText);
           // Find the NavHead and attach the toggle link (Must be this complicated because Moz's firstChild handling is borked)
           for(
             var j=0; 
             j < NavFrame.childNodes.length; 
             j++
           ) {
             if (NavFrame.childNodes[j].className == "NavHead") {
               NavFrame.childNodes[j].appendChild(NavToggle);
             }
           }
           NavFrame.setAttribute('id', 'NavFrame' + indexNavigationBar);
       }
   }
   // if more Navigation Bars found than Default: hide all
   if (NavigationBarShowDefault < indexNavigationBar) {
       for(
               var i=1; 
               i<=indexNavigationBar; 
               i++
       ) {
           toggleNavigationBar(i);
       }
   }

}

addLoadEvent(createNavigationBarToggleButton);


// END Dynamic Navigation Bars
// ============================================================

/* Customized versions of several routines used by the sortable table feature
   These functions (ts_*) will override the default versions found in wikibits.js
   Feature include:
   - sorting of tables with rowspan or colspan
   - descending order allowed as column default
   - specify format of column data
*/

function ts_makeSortable(table) {
	ts_setMultiRows(table);
	var headRow = new Array();
	if (table.rows && table.rows.length > 0) {
		if (table.tHead && table.tHead.rows.length > 0) {
			headRow = table.tHead.rows;
		} else {
			for (var j=0; j<table.rows.length; j++) {
				if (table.rows[j].baserow+j>0)
					break;
				headRow.push (table.rows[j]);
			}
		}
	}
	if (!headRow.length) return;
	table.rowStart = headRow.length;

	// We have a first row: assume it's the header, and make its contents clickable links
        for (var j=0; j<headRow.length; j++) {
		for (var i=0; i<headRow[j].sortrowd.length; i++) {
			jcell = headRow[j].sortrowd[i]+j;
			icell = headRow[j].sortcol[i];
			var cell = headRow[jcell].cells[icell];
	                var spans = getElementsByClassName(cell, "span", "sortarrow");
			if (cell.colSpan == 1 &&
                            (" "+cell.className+" ").indexOf(" unsortable ") == -1 &&
                            spans.length==0) {
				cell.basecol = i;
                                if (cell.innerHTML.search(/<\s*br\s*\/?>\s*$/)<0) {
                                    cell.innerHTML += '<br>';
                                }
				cell.innerHTML += '<a href="#" class="sortheader" onclick="ts_resortTable(this);return false;"><span class="sortarrow"><img src="' + ts_image_path + ts_image_none + '" alt=&darr;"/></span></a>';
			}
		}
	}

	for (var j=0; j<table.rows.length; j++) {
		table.rows[j].setAttribute('origindex', j);
	}

	if (ts_alternate_row_colors)
		ts_alternate(table);
}

/* New function: determine layout of tables with rowspans or colspans */
function ts_setMultiRows (table) {
	var ieff = 0;
	var imax = 0;
	var jbase = 0;
	var jmax = 0;
	var src_i = new Array();
	var src_j = new Array();
	for (var j=0; j<table.rows.length; j++) {
		jmax = Math.max(jmax, j);
		if (!src_j[j]) {
			src_j[j] = new Array();
			src_i[j] = new Array();
		}

		for (var i = ieff = 0; i<table.rows[j].cells.length; ieff++, i++) {
			while (!isNaN(src_j[j][ieff]))
				ieff++;
			var cell = table.rows[j].cells[i];
			imax = Math.max(imax, ieff+cell.colSpan);
			jmax = Math.min(Math.max(jmax, j+cell.rowSpan-1),table.rows.length-1);
			for (var jd=0; jd<cell.rowSpan; jd++) {
				if (!src_j[j+jd]) {
					src_j[j+jd] = new Array();
					src_i[j+jd] = new Array();
				}
				for (id=0; id<cell.colSpan; id++) {
					src_i[j+jd][ieff+id] = i;
					src_j[j+jd][ieff+id] = j;
				}
			}
		}
		if (jmax == j) {
			var full;
			if (jbase==j && imax>1 && table.rows[j].cells.length==1 && table.rows[j].cells[0].colspan==imax) {
				full = true;
			}
			else {
				full = false;
			}
			var sortrow = -1;
			for (var ja=jbase; ja<=jmax; ja++) {
				table.rows[ja].fullcolspan = full;
				table.rows[ja].baserow = jbase-ja;
				table.rows[ja].sortrowd = new Array();
				table.rows[ja].sortcol = new Array();
				if ((" "+table.rows[ja].className+" ").indexOf(" sort_keyrow ") != -1) {
					sortrow = ja;
				}
			}
			for (var i=0; i<imax; i++) {
				var csortrow = -1;
				if (sortrow>=0 &&
                                    !isNaN(src_j[sortrow][i]) &&
                                    table.rows[src_j[sortrow][i]].cells[src_i[sortrow][i]].colSpan==1) {
					csortrow = sortrow;
				}
				for (var ja=jbase; ja<=jmax; ja++) {
					if (csortrow>=0) break;
					if (!isNaN(src_j[ja][i]) &&
                                            table.rows[src_j[ja][i]].cells[src_i[ja][i]].colSpan==1) {
						csortrow = j;
					}
				}
				for (var ja=jbase; ja<=jmax; ja++) {
					if (csortrow>=0) {
						table.rows[ja].sortrowd[i] = src_j[csortrow][i]-ja;
						table.rows[ja].sortcol[i] = src_i[csortrow][i];
					}
					else {
						table.rows[ja].sortrowd[i] = 0;
						table.rows[ja].sortcol[i] = 0;
					}
				}
			}	
			jbase = j+1;
		}
	}
}

function ts_resortTable(lnk) {
	// get the span
	var span = lnk.getElementsByTagName('span')[0];

	var td = lnk.parentNode;
	var tr = td.parentNode;
	var column = td.basecol;

	var table = tr.parentNode;
	while (table && !(table.tagName && table.tagName.toLowerCase() == 'table'))
		table = table.parentNode;
	if (!table) return;

	// Work out a type for the column
	if (table.rows.length <= 1 ) return;

	// Skip the first row if that's where the headings are
	var rowStart = table.rowStart;

	sortfn = ts_sort_caseinsensitive;
	if ((" "+td.className+" ").indexOf(" sort_num ") != -1) {
		sortfn = ts_sort_numeric;
	}
	else if ((" "+td.className+" ").indexOf(" sort_date ") != -1) {
		sortfn = ts_sort_date;
	}
	else if ((" "+td.className+" ").indexOf(" sort_str ") == -1) {
		var itm = "";
		for (var j = rowStart; j < table.rows.length; j++) {
			if (table.rows[j].cells.length > column) {
				var sortrow = table.rows[j].sortrowd[column] + j;
				var sortcol = table.rows[j].sortcol[column];
				itm = ts_getInnerText(table.rows[sortrow].cells[sortcol]);
				itm = itm.replace(/^[\s\xa0]+/, "").replace(/[\s\xa0]+$/, "");
				if (itm != "") break;
			}
		}

		itm = ts_firstWord(itm);
		if (itm.match(/^\d\d[\/\.\-][a-zA-z]{3}[\/\.\-]\d{2,4}$/))
			sortfn = ts_sort_date;
		else if (itm.match(/^\d\d[\/\.\-]\d\d[\/\.\-]\d{2,4}$/))
			sortfn = ts_sort_date;
		else if (itm.match(/^[\u00a3$\u20ac\u00a5]/)) // pound dollar euro yen
			sortfn = ts_sort_numeric;
		else if (itm.match(/^[\-\+]?[\d\.\,]+([eE][\-\+]?\d+)?\%?$/))
			sortfn = ts_sort_numeric;
	}

	var sortdir = 'down';
	var reverse = 0;
	if ((" "+td.className+" ").indexOf(" sort_desc ") != -1) {
		// order for default=descending is 'down', then 'up', then 'none'
		if (span.getAttribute("sortdir") == 'down') {
			sortdir = 'up';
		}
		else if (span.getAttribute("sortdir") == 'up')
			sortdir = 'none';
		else
			sortdir = 'down';
	}
	else {
		//standard order is 'up', then 'down', then 'none'
		if (span.getAttribute("sortdir") == 'down')
			sortdir = 'none';
		else if (span.getAttribute("sortdir") == 'up') {
			sortdir = 'down';
		}
		else
			sortdir = 'up';
	}

	var newRows = new Array();

	if (sortdir == 'none')
		sortfn = ts_sort_numeric;
		
	for (var j=rowStart ; j < table.rows.length; j++) {
		var row = table.rows[j];
		if (sortdir == 'none')
			var keyText = row.getAttribute('origindex');
		else {
			var sortrow = table.rows[j].sortrowd[column] + j;
			var sortcol = table.rows[j].sortcol[column];
			var keyText = ts_getInnerText(table.rows[sortrow].cells[sortcol]);
		}
		var oldIndex = ((sortdir == 'down') ? -j : j);

		newRows[newRows.length] = new Array(row, keyText, oldIndex);
	}
	newRows.sort(sortfn);
        if (sortdir == 'down')
		newRows.reverse();

	var arrowHTML;
	if (sortdir == 'down')
		arrowHTML = '<img src="'+ ts_image_path + ts_image_down + '" alt="&darr;"/>';
	else if (sortdir == 'up')
		arrowHTML = '<img src="'+ ts_image_path + ts_image_up + '" alt="&uarr;"/>';
	else
		arrowHTML = '<img src="'+ ts_image_path + ts_image_none + '" alt="&darr;"/>';

	// We appendChild rows that already exist to the tbody, so it moves them rather than creating new ones
	// don't do sortbottom rows
	for (var i=0; i<newRows.length; i++) {
		if ((" "+newRows[i][0].className+" ").indexOf(" sortbottom ") == -1) {
			table.tBodies[0].appendChild(newRows[i][0]);
		}
	}
	// do sortbottom rows only
	for (i=0; i<newRows.length; i++) {
		if ((" "+newRows[i][0].className+" ").indexOf(" sortbottom ") != -1) {
			table.tBodies[0].appendChild(newRows[i][0]);
		}
	}
	// Delete any other arrows that may be showing
	var spans = getElementsByClassName(table, "span", "sortarrow");
	for (var i=0; i<spans.length; i++) {
		spans[i].innerHTML = '<img src="'+ ts_image_path + ts_image_none + '" alt="&darr;"/>';
		spans[i].setAttribute('sortdir',"none");
	}
	span.innerHTML = arrowHTML;
	span.setAttribute('sortdir',sortdir);
	ts_alternate(table);
}

function ts_dateToSortKey(date) {
	if (date.match(/^\d\d[\/\.-][a-zA-z][a-zA-Z][a-zA-Z][\/\.-]\d{2,4}/)) {
		switch (date.substr(3,3).toLowerCase()) {
			case "jan": var month = "01"; break;
			case "feb": var month = "02"; break;
			case "mar": var month = "03"; break;
			case "apr": var month = "04"; break;
			case "may": var month = "05"; break;
			case "jun": var month = "06"; break;
			case "jul": var month = "07"; break;
			case "aug": var month = "08"; break;
			case "sep": var month = "09"; break;
			case "oct": var month = "10"; break;
			case "nov": var month = "11"; break;
			case "dec": var month = "12"; break;
			// default: var month = "00";
		}
		year = ts_yearToY2K(date.substr(7,4));		
		return year+month+date.substr(0,2);
	} else if (date.match(/^\d\d[\/\.-]\d\d[\/\.-]\d{2,4}/)) {
		year = ts_yearToY2K(date.substr(6,4));
		if (europeandate == false)
			return year+date.substr(0,2)+date.substr(3,2);
		else
			return year+date.substr(3,2)+date.substr(0,2);
	}
	return "00000000";
}

function ts_firstWord(string) {
	string = ''+string;
	var splitstring = string.split(" ");
	return splitstring[0];
}

function ts_parseFloat(num) {
	if (!num) return 0;
        var matches=num.match(/[\-\+]?\.?\d+[^\s]*/);
	if (!matches) return 0;
	num = matches[0];
	num = parseFloat(num.replace(/,/g, ""));
	return (isNaN(num) ? 0 : num);
}

function ts_sort_date(a,b) {
	var aa = ts_dateToSortKey(a[1]);
	var bb = ts_dateToSortKey(b[1]);
	return (aa < bb ? -1 : aa > bb ? 1 : a[2] - b[2]);
}

function ts_sort_currency(a,b) {
	var aa = ts_parseFloat(a[1].replace(/[^0-9.]/g,''));
	var bb = ts_parseFloat(b[1].replace(/[^0-9.]/g,''));
	return (aa != bb ? aa - bb : a[2] - b[2]);
}

function ts_sort_numeric(a,b) {
	var aa = ts_parseFloat(a[1]);
	var bb = ts_parseFloat(b[1]);
	return (aa != bb ? aa - bb : a[2] - b[2]);
}

function ts_sort_caseinsensitive(a,b) {
	var aa = a[1].toLowerCase();
	var bb = b[1].toLowerCase();
	return (aa < bb ? -1 : aa > bb ? 1 : a[2] - b[2]);
}

function ts_sort_default(a,b) {
	return (a[1] < b[1] ? -1 : a[1] > b[1] ? 1 : a[2] - b[2]);
}
function ts_alternate(table) {
	// Take object table and get all it's tbodies.
	var tableBodies = table.getElementsByTagName("tbody");
	// Loop through these tbodies
	for (var i = 0; i < tableBodies.length; i++) {
		// Take the tbody, and get all it's rows
		var tableRows = tableBodies[i].getElementsByTagName("tr");
		// Loop through these rows
		// Start at 1 because we want to leave the heading row untouched
		for (var j = 0, jv = -1; j < tableRows.length; j++) {
			// Only increment for visible rows and rows that are not grouped
			if (tableRows[j].style.display != 'none' && (isNaN(tableRows[j].baserow) || tableRows[j].baserow == 0))
				jv++;
			var oldClasses = tableRows[j].className.split(" ");
			var newClassName = "";
			for (var k = 0; k<oldClasses.length; k++) {
				if (oldClasses[k] != "" && oldClasses[k] != "even" && oldClasses[k] != "odd")
					newClassName += oldClasses[k] + " ";
			}
			// Check if j is even, and apply classes for both possible results
			tableRows[j].className = newClassName + (jv%2 == 0 ? "even" : "odd");
		}
	}
}
