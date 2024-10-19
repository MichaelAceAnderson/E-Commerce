// Ajouter une balise de style initial pour éviter le flash de contenu
document.write('<style>.loading * {'
	+ '-webkit-transition: none !important;'
	+ '-moz-transition: none !important;'
	+ '-ms-transition: none !important;'
	+ '-o-transition: none !important;'
	+ 'transition: none !important;'
	+ '}</style>');

// Attendre la fin du chargement du DOM pour réactiver les transitions
document.addEventListener("DOMContentLoaded", function () {
	// Pour tous les éléments avec la classe "loading", supprimer la classe "loading"
	node = document.querySelectorAll('.loading');
	for (var i = 0; i < node.length; i++) {
		if (node[i].classList.contains('loading')) {
			node[i].classList.remove('loading');
		}
	}
});