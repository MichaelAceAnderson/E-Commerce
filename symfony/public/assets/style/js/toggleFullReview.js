/**
* Cacher ou montrer le contenu de la div parente d'un élément cliqué
* @param {Event} event Source de l'événement
*/
function toggleContent(event) {
	// On empêche le comportement par défaut du lien
	event.preventDefault();
	
	// On récupère les éléments lien cliqué et son parent
	const toggle = event.currentTarget;
	const parent = event.currentTarget.parentElement;

	if (!parent.classList.contains('open')) {
		parent.classList.add('open');

		for (let i = 0; i < toggle.childNodes.length; i++) {
			if (toggle.childNodes[i].nodeType === Node.TEXT_NODE) {
				toggle.childNodes[i].textContent = '- Voir moins';
				break;
			}
		}
	} else {
		parent.classList.remove('open');
		for (let i = 0; i < toggle.childNodes.length; i++) {
			if (toggle.childNodes[i].nodeType === Node.TEXT_NODE) {
				toggle.childNodes[i].textContent = '+ Voir plus';
				break;
			}
		}
	}
}

/**
 * Ajoute un lien pour voir le contenu complet d'un conteneur
 * 
 * @param {string} selector Sélecteur du conteneur (ex: '.review')
 * @param {string} bottomElementSelector Sélecteur de l'élément en bas du conteneur (ex: '.source')
 * @returns {void}
 * @example initializeContent('.review', '.source');
 */
function initializeContent(selector, bottomElementSelector) {
	document.querySelectorAll(selector).forEach((element) => {

		// Dans un premier temps, on retire la classe "open" des éléments pour
		// pouvoir calculer correctement si le contenu dépasse de l'élément
		if(element.classList.contains('open')) {
			element.classList.remove('open');
		}

		elementFrameBottom = element.getBoundingClientRect().bottom
		elementContentBottom = element.querySelector(bottomElementSelector).getBoundingClientRect().bottom
		
		// Si le contenu dépasse du cadre, on ajoute un lien pour le voir en entier
		if (elementContentBottom > elementFrameBottom) {
			const link = document.createElement('a');
			link.href = '#';
			link.classList.add('detail-toggle');

			const linkText = document.createTextNode('+ Voir plus');
			
			link.appendChild(linkText);
			element.appendChild(link);

			// À chaque clic sur le lien, on appelle la fonction toggleContent
			link.addEventListener('click', (event) => {
				toggleContent(event);
			});
		}
	});
}

// Au chargement de la page, on ajoute un lien pour voir le contenu complet des cadres
document.addEventListener('DOMContentLoaded', () => 
{
	initializeContent('.review', '.source');
});