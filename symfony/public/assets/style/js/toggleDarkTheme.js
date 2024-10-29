/**
 * Apply the desired theme
 * 
 * @param {boolean} dark Dark theme or not
 * 
 * @returns {void}
 */
function toggleDarkTheme(event) {
	// Get the value of the checkbox
	const dark = event.target.checked;
	// Apply the theme
	applyTheme(dark);
	// Update the checkbox
	event.target.checked = dark;
	// Save the user's preference
	localStorage.setItem('dark', dark);
}

/**
 * Apply the desired theme
 * 
 * @param {boolean} dark Dark theme or not
 */
function applyTheme(dark) {
	if (dark) {
		document.body.classList.add('dark-theme');
	} else {
		document.body.classList.remove('dark-theme');
	}
}

/**
 * Get the user's preferred theme
 * 
 * @returns {boolean} Dark theme or not
 */
function getUserThemePreference() {
	// If localStorage is empty, return the browser's preferred theme
	if (localStorage.getItem('dark') === null) {
		const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
		return prefersDarkScheme.matches;
	} else {
		// Otherwise, return the user's preference
		return JSON.parse(localStorage.getItem('dark'));
	}
}

/**
 * Create a checkbox
 * 
 * @param {HTMLElement} parent Parent of the checkbox
 * @param {string} id Id of the checkbox
 */
function createCheckbox(parent, id) {
	// Create these elements in the DOM:
	// <li>
	//  <label class="switch">
	// 	 <input type="checkbox" id="themeSwitch">
	// 	 <span class="slider round"></span>
	//  </label>
	// </li>

	const li = document.createElement('li');

	const label = document.createElement('label');
	label.classList.add('switch');
	li.appendChild(label);

	const input = document.createElement('input');
	input.type = 'checkbox';
	input.id = id;
	label.appendChild(input);

	const span = document.createElement('span');
	span.classList.add('slider', 'round');
	label.appendChild(span);
	
	parent.appendChild(li);
}

// Add an event listener on page load
window.addEventListener('DOMContentLoaded', () => {
	// When the page is loaded, add a checkbox to the user-menu
	const userMenu = document.getElementById('userMenu');
	if (!userMenu) {
		console.error('The user menu is not found.');
		return;
	}
	const checkboxId = 'themeSwitch';
	// Create the checkbox
	createCheckbox(userMenu, checkboxId);

	// Get the checkbox
	const checkbox = document.getElementById(checkboxId);
	if (!checkbox) {
		console.error('The checkbox intended to change the theme is not found.');
		return;
	}

	// Apply the theme stored in the user's preferences (or the default theme)
	const dark = getUserThemePreference();
	// Check or uncheck the checkbox based on the theme
	checkbox.checked = dark;
	// Apply the theme
	applyTheme(dark);
	// Add an event listener on the checkbox value change
	checkbox.addEventListener('change', toggleDarkTheme);
});
