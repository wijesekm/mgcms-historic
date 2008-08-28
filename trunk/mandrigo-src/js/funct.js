// <![CDATA[

function emailToJS(users,domains){
	em='mai'+'lto:';
	for (var i=0; i< users.length; i++) {
		em=em+users[i]+unescape('%40')+domains[i];
		if(i+1<users.length){
			em=em+';';
		}
	}
	return em;
}

function jsConfirm(question,location){
	var where_to= confirm(question);
	if (where_to== true){
		window.location=location;
	}
	else{
		window.location="#";
	}
}

// ]]>