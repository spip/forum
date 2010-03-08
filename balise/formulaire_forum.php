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

// http://doc.spip.org/@balise_FORMULAIRE_FORUM
function balise_FORMULAIRE_FORUM ($p) {
	/**
	 * Recuperation du type d'objet SPIP $objet
	 * et de sa cle primaire $primary
	 * depuis la boucle englobante
	 * Code emprunté de crayons
	 * Valable uniquement si l'on est dans une boucle
	 */
	$i_boucle = $p->nom_boucle ? $p->nom_boucle : $p->id_boucle;
	$table = $p->boucles[$i_boucle]->type_requete;
	$ids = array();

	if($table){
		$objet = $table[strlen($table) - 1] == 's' ?
			substr($table, 0, -1) :
			str_replace(
				array('syndication'),
				array('site'),
			$table);

		$primary = $p->boucles[$i_boucle]->primary;
	}
	/**
	 * Sinon on essaye de trouver les forums en fonction de l'environnement
	 */
	else{
		$objets = pipeline('forum_objet_accepte',array('id_article','id_rubrique','id_syndic','id_breve','id_ticket'));
		foreach($objets as $objet){
			if($val = _request($objet)){
				$ids[$objet] = $val;
			}
		}
		if(count($ids)>1){
			if(isset($ids['id_rubrique'])){
				unset($ids['id_rubrique']);
			}
		}

		if(count($ids) == 1){
			$primary = key($ids);
			$objet = str_replace('id_','',$primary);
			$table = table_objet($objet);
		}else{
			$primary = 'null';
			$objet = 'null';
			$table = 'null';
		}
	}

	$p = calculer_balise_dynamique($p,'FORMULAIRE_FORUM', array($primary,'id_forum', 'ajouter_mot', 'ajouter_groupe', 'afficher_texte'),array($objet,$table,$primary));

	// Ajouter le code d'invalideur specifique aux forums
	include_spip('inc/invalideur');
	if ($i = charger_fonction('code_invalideur_forums','',true))
		$p->code = $i($p, $p->code);

	return $p;
}

//
// Chercher le titre et la configuration d'un forum
// valeurs possibles : 'pos'teriori, 'pri'ori, 'abo'nnement
// Donner aussi la table de reference pour afficher_groupes[]

// http://doc.spip.org/@balise_FORMULAIRE_FORUM_stat
function balise_FORMULAIRE_FORUM_stat($args, $context_compil) {

	// le denier arg peut contenir l'url sur lequel faire le retour
	// exemple dans un squelette article.html : [(#FORMULAIRE_FORUM{#SELF})]
	// recuperer les donnees du forum auquel on repond.
	list ($ido, $idf, $am, $ag, $af, $url) = $args;
	$ido = intval($ido);
	$idf = intval($idf);

	$objet = $context_compil[5];
	$table = $context_compil[6];
	$primary = $context_compil[7];

	if(!$primary){
		if(intval($idf)){
			$objet = sql_fetsel('id_objet,objet','spip_forum','id_forum='.intval($idf));
			$ido = $objet['id_objet'];
			$objet = $objet['objet'];
			$primary = id_table_objet($objet);
			$table = table_objet($objet);
		}else{
			return false;
		}
	}

	$type = substr($GLOBALS['meta']["forums_publics"],0,3);

	if ($objet == 'article') {
		$titre = sql_fetsel('accepter_forum AS type, titre', 'spip_articles', "statut = 'publie' AND id_article = $ido");
		if ($titre) {
			if ($titre['type']) $type = $titre['type'];
		}
		if ($type == 'non') return false;
	} else {

		if ($type == 'non') return false;

		if ($objet == 'syndic') {
			$titre = sql_fetsel('nom_site AS titre', 'spip_syndic', "statut = 'publie' AND id_syndic = $ido");
		}else if($f = charger_fonction($objet.'_forum_extraire_titre','inc',true)){
			$titre = $f($ido);
		}else{
			$table_objet_sql = table_objet_sql($objet);
			$titre = sql_fetsel('titre', $table_objet_sql, "statut = 'publie' AND $primary = $ido");
		}
	}

	if (!$titre) return false; // inexistant ou non public

	if ($idf>0) {
		$titre_m = sql_fetsel('titre', 'spip_forum', "id_forum = $idf");
		if (!$titre_m) return false; // URL fabriquee
		$titre = $titre_m;
	}

	if ($GLOBALS['meta']["mots_cles_forums"] != "oui")
		$table = '';

	$titre = supprimer_numero($titre['titre']);

	// Sur quelle adresse va-t-on "boucler" pour la previsualisation ?
	// si vide : self()
	$script = '';

	return
		array($titre, $table, $type, $objet, $primary, $script,
		$ido, $idf, $am, $ag, $af, $url);
}

?>
