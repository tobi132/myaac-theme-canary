function MouseOverBigButton(source) {
	if (source?.firstChild?.style) {
		source.firstChild.style.visibility = "visible";
	}
}
function MouseOutBigButton(source) {
	if (source?.firstChild?.style) {
		source.firstChild.style.visibility = "hidden";
	}
}
function BigButtonAction(path) {
	window.location = path;
}

function MouseOverLoginBoxText() {
	document.getElementById('LoginstatusText_2_1').style.visibility = 'hidden';
	document.getElementById('LoginstatusText_2_2').style.visibility = 'visible';
}

function MouseOutLoginBoxText() {
	document.getElementById('LoginstatusText_2_1').style.visibility = 'visible';
	document.getElementById('LoginstatusText_2_2').style.visibility = 'hidden';
}
