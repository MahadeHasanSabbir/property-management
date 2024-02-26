function giveinfo(){
	if (sessionStorage.getItem("visited") === null){
		alert("Please fill out all the information! Everything is required.\nSorry for the interrupt :)");
		sessionStorage.setItem("visited", "true");
	}
}

function givealert(){
	alert("Sorry, this feature is under development!");
	return false;
}

function permit(){
	if(!confirm("Do you want to Log out?")){
		return false;
	}
	else{
		return true;
	}
}

function apermit(){
	if(!confirm("Sure to visit users?")){
		return false;
	}
	else{
		return true;
	}
}

function permit1(){
	if(!confirm("Sure to edit your information?")){
		return false;
	}
	else{
		return true;
	}
}

function apermit1(){
	if(!confirm("Sure to edit user information?")){
		return false;
	}
	else{
		return true;
	}
}

function permit2(){
	if(!confirm("Sure to delete your information? This can not be undone and we will no longer have any information provided by you.")){
		return false;
	}
	else{
		return true;
	}
}

function apermit2(){
	if(!confirm("Sure to delete user information?")){
		return false;
	}
	else{
		return true;
	}
}

function permit3(){
	if(!confirm("Sure to delete this property information?")){
		return false;
	}
	else{
		return true;
	}
}

function permit4(){
	if(!confirm("Sure to clear information?")){
		return false;
	}
	else{
		return true;
	}
}