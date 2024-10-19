/**
 * Crée un nouvel élément de formulaire pour un média
 * 
 * @param {*} formContainer Le conteneur du formulaire contenant le prototype
 * 
 * @return void
 */
function createMediaInput(formContainer) {
	// Récupérer le nombre d'éléments dans le conteneur
	var index = formContainer.children.length;
	// Récupérer le prototype
	var prototype = formContainer.getAttribute('data-prototype');

	// Créer un nouvel élément à partir du prototype
	// en remplaçant __name__label__ par le numéro de l'élément et __name__ par l'index
	var newElementHTML = prototype.replace(/__name__label__/g, 'Média n°' + (index));
	newElementHTML = newElementHTML.replace(/__name__/g, index);

	// Ajouter le nouvel élément à la collection
	var newElement = document.createElement('div');
	newElement.innerHTML = newElementHTML;
	// Ajouter le nouvel élément au conteneur
	formContainer.appendChild(newElement);

	// S'il s'agit du premier élément, ajouter un bouton de suppression
	if (index === 0) {
		addRemoveButton(formContainer, newElement);
	}
}

/**
 * Ajoute un bouton de suppression à un élément de formulaire
 * 
 * @param {*} formContainer Le formulaire à la suite duquel ajouter le bouton
 * 
 * @return void
 */
function addRemoveButton(formElement) {
	
	var removeButton = document.createElement('button');
	removeButton.textContent = 'Supprimer le média';
	removeButton.type = 'button';
	removeButton.classList.add('btn', 'btn-danger');
	// Ajouter le bouton après le conteneur
	formElement.parentElement.appendChild(removeButton);

	// À chaque clic sur le bouton, supprimer le dernier élément
	removeButton.addEventListener('click', function() {
		// Récupérer le dernier élément
		var lastElement = formElement.lastElementChild;
		// Supprimer le dernier élément
		formElement.removeChild(lastElement);
		// S'il n'y a plus d'élément, supprimer le bouton de suppression
		if (formElement.children.length === 0) {
			formElement.parentElement.removeChild(removeButton);
		}
	});
}

// Attendre que la page soit chargée
window.addEventListener('load', function() {
	// Récupérer le conteneur du formulaire
	var productEditForm = document.getElementById('product_edit_medias');
	// Créer un bouton d'ajout à la fin du conteneur
	var addButton = document.createElement('button');
	addButton.textContent = 'Ajouter un média';
	addButton.type = 'button';
	addButton.classList.add('btn', 'btn-primary');
	// Ajouter le bouton après le conteneur
	productEditForm.parentElement.appendChild(addButton);

	// À chaque clic sur le bouton, ajouter un nouvel élément
	addButton.addEventListener('click', function() {
		createMediaInput(productEditForm);
	});

	// Si le formulaire contient déjà des éléments, ajouter un bouton de suppression
	if (productEditForm.children.length !== 0) {
		addRemoveButton(productEditForm);
	}
});