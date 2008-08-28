// <![CDATA[

function queryToURLType(urlbase){
	var elemq = document.getElementById('query');
	window.location=urlbase + 'q/' + elemq.value;
	return false;
}

// ]]>