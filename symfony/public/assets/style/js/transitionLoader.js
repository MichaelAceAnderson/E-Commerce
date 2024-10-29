// Add an initial style tag to prevent content flash
document.write('<style>.loading * {'
	+ '-webkit-transition: none !important;'
	+ '-moz-transition: none !important;'
	+ '-ms-transition: none !important;'
	+ '-o-transition: none !important;'
	+ 'transition: none !important;'
	+ '}</style>');

// Wait for the DOM to finish loading to re-enable transitions
document.addEventListener("DOMContentLoaded", function () {
	// For all elements with the class "loading", remove the "loading" class
	let nodes = document.querySelectorAll('.loading');
	for (let i = 0; i < nodes.length; i++) {
		if (nodes[i].classList.contains('loading')) {
			nodes[i].classList.remove('loading');
		}
	}
});