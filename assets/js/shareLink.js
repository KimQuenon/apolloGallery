// paste link to clipboard
function copyLinkToClipboard(url) {
	var input = document.createElement('input');
	input.setAttribute('value', url);
	document.body.appendChild(input);
	input.select();
	document.execCommand('copy');
	document.body.removeChild(input);

	// display successMessage
	const copyMessage = document.getElementById('copyMessage');
	if (copyMessage) {
		copyMessage.style.display = 'block';
		setTimeout(() => {
			copyMessage.style.display = 'none';
		}, 1500);
	}
}

// add event
const shareLinkButton = document.getElementById('shareLinkButton');
if (shareLinkButton) {
	shareLinkButton.addEventListener('click', function() {
		copyLinkToClipboard(window.location.href);
	});
}