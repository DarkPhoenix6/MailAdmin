/**
 * @author Chris
 * @notes requires 'https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js'
 */
$(function() {
	$(document).ready(function() {
		var close = document.getElementsByClassName("closebtn");
		var i;

		for ( i = 0; i < close.length; i++) {
			close[i].onclick = function() {
				var div = this.parentElement;
				div.style.opacity = "0";
				setTimeout(function() {
					div.style.display = "none";
				}, 600);
			};
		}
	});
});

