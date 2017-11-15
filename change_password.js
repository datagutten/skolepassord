"use strict";

function change_password(object,dn,username,name,queue=false)
{
	//object.textContent=user;
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("GET", "change_password.php?dn=" + dn + "&username=" + username + "&name=" + name, true);
	object.textContent='Endrer passord, vennligst vent...';
	object.setAttribute('class','reset_link_used');
	xmlhttp.send();
	xmlhttp.onreadystatechange = function() 
	{
		if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
		{
			object.textContent=xmlhttp.responseText;
		}
	};
}
function reset_all(object)
{
	var tbody = object.parentElement.parentElement;
	//var users = tbody.getElementsByClassName('reset_link');
	var users = tbody.getElementsByTagName('tr');
	//start queue
	var userlist = [];
	var columns;
	var userinfo;
	for (var i=0; i<users.length; i++)
	{
		if (users[i].hasAttribute('data-dn'))
		{
			columns = users[i].getElementsByTagName('td');
			userinfo = [columns[0].textContent, //Name
						columns[1].textContent, //Username
						users[i].getAttribute('data-dn') //DN
					   ];
			userlist.push(userinfo);
			columns[3].textContent='';
		}
	}

	var xmlhttp = new XMLHttpRequest();
	xmlhttp.open("POST", "change_password.php?group="+tbody.parentElement.getAttribute('data-groupname'));
	object.textContent='Endrer passord, vennligst vent...';
	object.setAttribute('class','reset_link_used');
	xmlhttp.send(JSON.stringify(userlist));
	xmlhttp.onreadystatechange = function() 
	{
		if (xmlhttp.readyState === 4 && xmlhttp.status === 200)
		{
			object.textContent=xmlhttp.responseText;
		}
	};	
}