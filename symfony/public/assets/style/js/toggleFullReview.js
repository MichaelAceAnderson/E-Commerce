/**
* Hide or show the content of the parent div of a clicked element
* @param {Event} event Source of the event
*/
function toggleContent(event) {
	// Prevent the default behavior of the link
	event.preventDefault();
	
	// Get the clicked link element and its parent
	const toggle = event.currentTarget;
	const parent = event.currentTarget.parentElement;

	if (!parent.classList.contains('open')) {
		parent.classList.add('open');

		for (let i = 0; i < toggle.childNodes.length; i++) {
			if (toggle.childNodes[i].nodeType === Node.TEXT_NODE) {
				toggle.childNodes[i].textContent = '- See less';
				break;
			}
		}
	} else {
		parent.classList.remove('open');
		for (let i = 0; i < toggle.childNodes.length; i++) {
			if (toggle.childNodes[i].nodeType === Node.TEXT_NODE) {
				toggle.childNodes[i].textContent = '+ See more';
				break;
			}
		}
	}
}

/**
 * Add a link to see the full content of a container
 * 
 * @param {string} selector Container selector (e.g., '.review')
 * @param {string} bottomElementSelector Selector of the element at the bottom of the container (e.g., '.source')
 * @returns {void}
 * @example initializeContent('.review', '.source');
 */
function initializeContent(selector, bottomElementSelector) {
	document.querySelectorAll(selector).forEach((element) => {

		// First, remove the "open" class from elements to
		// correctly calculate if the content overflows the element
		if(element.classList.contains('open')) {
			element.classList.remove('open');
		}

		elementFrameBottom = element.getBoundingClientRect().bottom
		elementContentBottom = element.querySelector(bottomElementSelector).getBoundingClientRect().bottom
		
		// If the content overflows the frame, add a link to see it in full
		if (elementContentBottom > elementFrameBottom) {
			const link = document.createElement('a');
			link.href = '#';
			link.classList.add('detail-toggle');

			const linkText = document.createTextNode('+ See more');
			
			link.appendChild(linkText);
			element.appendChild(link);

			// On each click on the link, call the toggleContent function
			link.addEventListener('click', (event) => {
				toggleContent(event);
			});
		}
	});
}

// On page load, add a link to see the full content of the frames
document.addEventListener('DOMContentLoaded', () => 
{
	initializeContent('.review', '.source');
});