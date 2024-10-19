/**
 * Appliquer le thème voulu
 * 
 * @param {boolean} dark Thème sombre ou non
 * 
 * @returns {void}
 */
function toggleDarkTheme(event) {
	// On récupère la valeur de la checkbox
	const dark = event.target.checked;
	// On applique le thème
	applyTheme(dark);
	// On met à jour la checkbox
	event.target.checked = dark;
	// On enregistre la préférence de l'utilisateur
	localStorage.setItem('dark', dark);
}

/**
 * Appliquer le thème voulu
 * 
 * @param {boolean} dark Thème sombre ou non
 */
function applyTheme(dark) {
	if (dark) {
		document.body.classList.add('dark-theme');
	} else {
		document.body.classList.remove('dark-theme');
	}
}

/**
 * Récupérer le thème préféré de l'utilisateur
 * 
 * @returns {boolean} Thème sombre ou non
 */
function getUserThemePreference() {
	// Si le localStorage est vide, on retourne le thème préféré du navigateur
	if (localStorage.getItem('dark') === null) {
		const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
		return prefersDarkScheme.matches;
	} else {
		// Sinon, on retourne la préférence de l'utilisateur
		return JSON.parse(localStorage.getItem('dark'));
	}
}

/**
 * Créer une checkbox
 * 
 * @param {HTMLElement} parent Parent de la checkbox
 * @param {string} id Id de la checkbox
 */
function createCheckbox(parent, id) {
	// On crée ces éléments dans le DOM:
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

// On ajoute un écouteur d'événement sur le chargement de la page
window.addEventListener('DOMContentLoaded', () => {
	// Lorsque la page est chargée, rajoute une checkbox au user-menu
	const userMenu = document.getElementById('userMenu');
	if (!userMenu) {
		console.error('Le menu utilisateur est introuvable.');
		return;
	}
	const checkboxId = 'themeSwitch';
	// On crée la checkbox
	createCheckbox(userMenu, checkboxId);

	// On récupère la checkbox
	const checkbox = document.getElementById(checkboxId);
	if (!checkbox) {
		console.error('La checkbox vouée à changer le thème est introuvable.');
		return;
	}


	// On applique le thème stocké dans les préférences de l'utilisateur (ou le thème par défaut)
	const dark = getUserThemePreference();
	// On coche ou non la checkbox en fonction du thème 
	checkbox.checked = dark;
	// On applique le thème
	applyTheme(dark);
	// On ajoute un écouteur d'événement sur le changement de valeur de la checkbox
	checkbox.addEventListener('change', toggleDarkTheme);
});