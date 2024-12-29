/**
 * Creates a new form element for a media
 * 
 * @param {*} formContainer The form container containing the prototype
 * 
 * @return void
 */
function createMediaInput(formContainer) {
	// Get the number of elements in the container
	var index = formContainer.children.length;
	// Get the prototype
	var prototype = formContainer.getAttribute('data-prototype');

	// Create a new element from the prototype
	// by replacing __name__label__ with the element number and __name__ with the index
	var newElementHTML = prototype.replace(/__name__label__/g, 'Media No.' + (index));
	newElementHTML = newElementHTML.replace(/__name__/g, index);

	// Add the new element to the collection
	var newElement = document.createElement('div');
	newElement.innerHTML = newElementHTML;
	// Add the new element to the container
	formContainer.appendChild(newElement);

	// If it is the first element, add a remove button
	if (index === 0) {
		addRemoveButton(formContainer, newElement);
	}
}

/**
 * Adds a remove button to a form element
 * 
 * @param {*} formContainer The form to which the button will be added
 * 
 * @return void
 */
function addRemoveButton(formElement) {
	var removeButton = document.createElement('button');
	removeButton.textContent = 'Remove media';
	removeButton.type = 'button';
	removeButton.classList.add('btn', 'btn-danger');
	// Add the button after the container
	formElement.parentElement.appendChild(removeButton);

	// On each click on the button, remove the last element
	removeButton.addEventListener('click', function() {
		// Get the last element
		var lastElement = formElement.lastElementChild;
		// Remove the last element
		formElement.removeChild(lastElement);
		// If there are no more elements, remove the remove button
		if (formElement.children.length === 0) {
			formElement.parentElement.removeChild(removeButton);
		}
	});
}

// Wait for the page to load
window.addEventListener('load', function() {
	// Get the form container
	var productEditForm = document.getElementById('product_edit_medias');
	// Create an add button at the end of the container
	var addButton = document.createElement('button');
	addButton.textContent = 'Add media';
	addButton.type = 'button';
	addButton.classList.add('btn', 'btn-primary');
	// Add the button after the container
	productEditForm.parentElement.appendChild(addButton);

	// On each click on the button, add a new element
	addButton.addEventListener('click', function() {
		createMediaInput(productEditForm);
	});

	// If the form already contains elements, add a remove button
	if (productEditForm.children.length !== 0) {
		addRemoveButton(productEditForm);
	}
});
