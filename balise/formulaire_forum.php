<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2009                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;	#securite

include_spip('inc/acces');
include_spip('inc/texte');
include_spip('inc/forum');

/*******************************/
/* GESTION DU FORMULAIRE FORUM */
/*******************************/

// Contexte du formulaire
// Mots-cles dans les forums :
// Si la variable de personnalisation $afficher_groupe[] est definie
// dans le fichier d'appel, et si la table de reference est OK, proposer
// la liste des mots-cles

// #FORMULAIRE_FORUM seul calcule (objet, id_objet) depuis la boucle parente
// #FORMULAIRE_FORUM{#SELF} pour forcer l'url de retour
// #FORMULAIRE_FORUM{#SELF, article, 3} pour forcer l'objet et son identifiant

// http://doc.spip.org/@balise_FORMULAIRE_FORUM
function balise_FORMULAIRE_FORUM ($p) {
	/**
	 * On recupere $objet et $id_objet depuis une boucle englobante si possible
	 * Sinon, on essaie aussi de recuperer des id_xx dans l'URL qui pourraient indiquer
	 * sur quoi le formulaire porte.
	 * Enfin, on pourra aussi forcer objet et id_objet depuis l'appel du formulaire
	 */

	$i_boucle = $p->nom_boucle ? $p->nom_boucle : $p->id_boucle;
	$_id_objet = $p->boucles[$i_boucle]->primary;
	
	/**
	 * On essaye de trouver les forums en fonction de l'environnement
	 * pour cela, on recupere l'ensemble des id_xxx possibles dans l'env
	 */
	if (!$i_boucle) {
		$ids = forum_get_objets_depuis_env();
		$ids = array_values($ids);
	}

	$obtenir = array(
		'FORUM_TYPE_BOUCLE', // demande du type d'objet
		$_id_objet,
		'id_forum',
		'ajouter_mot',
		'ajouter_groupe',
		'afficher_texte'
	);
	
	if ($ids) {
		$obtenir = array_merge($obtenir, $ids);
	}

	$p = calculer_balise_dynamique($p,'FORMULAIRE_FORUM', $obtenir);

	// Ajouter le code d'invalideur specifique aux forums
	include_spip('inc/invalideur');
	if ($i = charger_fonction('code_invalideur_forums', '', true)) {
		$p->code = $i($p, $p->code);
	}

	return $p;
}

//
// Chercher le titre et la configuration d'un forum
// valeurs possibles : 'pos'teriori, 'pri'ori, 'abo'nnement
// Donner aussi la table de reference pour afficher_groupes[]

// http://doc.spip.org/@balise_FORMULAIRE_FORUM_stat
function balise_FORMULAIRE_FORUM_stat($args, $context_compil) {

	// un arg peut contenir l'url sur lequel faire le retour
	// exemple dans un squelette article.html : [(#FORMULAIRE_FORUM{#SELF})]
	// recuperer les donnees du forum auquel on repond.
	// deux autres a la suite pour forcer objet et id_objet
	// [(#FORMULAIRE_FORUM{#SELF, article, 8})]
	//
	// $args = (obtenir) + (ids) + (url, objet, id_objet)
	$nb_args_debut = 6;
	list ($_objet, $ido, $idf, $am, $ag, $af) = $args;

		$id_objet = intval(array_pop($args));
		$objet    = array_pop($args);
		$url      = array_pop($args);
		$idf      = intval($idf);

	// pas d'objet force ? on prend le type de boucle calcule
	if (!$objet) {
		$objet = $_objet;	
		$id_objet = intval($ido); 
	} else {
		$id_objet = intval($id_objet); 
	}
	unset($_objet, $ido);
	
	// pas dans une boucle ? on generera une erreur ?
	if ($objet == 'balise_hors_boucle') {
		$objet = '';
		$id_objet   = '';
	} else {		
		$objet = objet_type($objet);
	}

	// on tente de prendre l'objet issu de l'environnement si un n'a pas pu etre calcule
	if (!$objet) {
		$objets = forum_get_objets_depuis_env();
		$ids = array(); $i = 0;
		foreach ($objets as $o => $ido) {
			if ($id = $args[$nb_args_debut + $i]) {
				$ids[$o] = $id;
			}
			$i++;
		}
		spip_log($ids, 'forums');
		if (count($ids)>1) {
			if (isset($ids['rubrique'])) {
				unset($ids['rubrique']);
			}
		}
		if (count($ids) == 1) {
			$objet = key($ids);
			$id_objet = array_shift($ids);
		}
	}
	unset ($i, $nb_args_debut);

	
	if (table_objet($objet) == 'syndic') {
		$objet = 'site'; // eviter 'syndication' ... quelle *#@*&! de vilainerie 
	}

	// et si on n'a toujours pas ce qu'on souhaite, on tente de le trouver dans un forum existant...
	if (!$id_objet and $idf){
		if ($objet = sql_fetsel(array('id_objet', 'objet'), 'spip_forum', 'id_forum=' . sql_quote($idf))) {
			$id_objet = $objet['id_objet'];
			$objet = $objet['objet'];
		} else {
			return false;
		}
	}
	// vraiment la... faut pas exagerer !
	if (!$id_objet) {
		return false;
	}

	// ca s'apparenterait presque a une autorisation...
	// si on n'avait pas a envoyer la valeur $accepter_forum au formulaire
	$accepter_forum = substr($GLOBALS['meta']["forums_publics"], 0, 3);
	// il y a un cas particulier pour l'acceptation de forum d'article...
	if ($f = charger_fonction($objet . '_accepter_forums_public', 'inc', true)){
		$accepter_forum = $f($id_objet);
	}
	
	if ($accepter_forum == 'non') {
		return false;
	}

	if ($titre = forum_recuperer_titre($objet, $id_objet)) {
		$titre = supprimer_numero($titre);
	} else {
		return false; // inexistant ou non public
	}
	
	
	if ($idf > 0) {
		$titre_m = sql_fetsel('titre', 'spip_forum', "id_forum = " . sql_quote($idf));
		if (!$titre_m) {
			return false; // URL fabriquee
		}
		$titre = $titre_m['titre'];
	}

	$primary = id_table_objet($objet);
	$table = table_objet($objet);
	
	if ($GLOBALS['meta']["mots_cles_forums"] != "oui") {
		$table = '';
	}

	

	// Sur quelle adresse va-t-on "boucler" pour la previsualisation ?
	// si vide : self()
	$script = '';

	return
		array($titre, $table, $accepter_forum, $objet, $primary, $script,
		$id_objet, $idf, $am, $ag, $af, $url);
}


// balise type_boucle de Rastapopoulos dans le plugin etiquettes
// present aussi dans plugin ajaxforms, notations, et bien d'autres...
function balise_FORUM_TYPE_BOUCLE($p) {
	$type = $p->boucles[$p->id_boucle]->id_table;
	$p->code = $type ? "'$type'" : "'balise_hors_boucle'";
	return $p;  
}


function forum_recuperer_titre($objet, $id_objet) {
	$titre = "";
	
	if ($f = charger_fonction($objet.'_forum_extraire_titre', 'inc', true)){
		$titre = $f($id_objet);
	} else {
		// retrouver le titre grace a table_titre...
		$trouver_table = charger_fonction('trouver_table', 'base');
		$desc = $trouver_table(table_objet_sql($objet));
		$_titre = $desc['titre'] ? $desc['titre'] : ($desc['field']['titre'] ? 'titre' : '');
		$_table = $desc['table'];
		$_primary = id_table_objet($_table);
		if ($_titre and $res = sql_fetsel($_titre, $_table, array(
			"statut = " . sql_quote('publie'),
			"$_primary = ". sql_quote($id_objet))
		)) {
			$titre = $res['titre'];
		}
	}
	
	return $titre;
}



function inc_article_accepter_forums_public_dist($id_objet) {
	$accepter_forum = substr($GLOBALS['meta']["forums_publics"],0,3);
	$art_accepter_forum = sql_getfetsel('accepter_forum', 'spip_articles', array(
		"statut = ". sql_quote('publie'),
		"id_article = ". sql_quote($id)
	));
	if ($art_accepter_forum) {
		$accepter_forum = $art_accepter_forum;
	}
	return $accepter_forum;
}


// recuperer tous les objets dont on veut pouvoir obtenir l'identifiant
// directement dans l'environnement
function forum_get_objets_depuis_env() {
	static $objets = null;
	if ($objets === null) {
		// on met une cle (le type d'objet) pour qu'un appel du pipeline
		// puisse facilement soustraire un objet qu'il ne veut pas avec
		// unset($objets['rubrique']) par exemple.
		$objets = pipeline('forum_objets_depuis_env', array(
			'article' => id_table_objet('article'),
			'rubrique' => id_table_objet('rubrique'),
			'site' => id_table_objet('site'),
			'breve' => id_table_objet('breve')
		));
		asort($objets);
	}

	return $objets;
}

?>
