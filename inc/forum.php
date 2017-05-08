<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2016                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/actions');

// recuperer le critere SQL qui selectionne nos forums
// https://code.spip.net/@critere_statut_controle_forum
function critere_statut_controle_forum($type, $id_rubrique=0, $recherche='') {

	if (is_array($id_rubrique))   $id_rubrique = join(',',$id_rubrique);
	if (!$id_rubrique) {
		$from = 'spip_forum AS F';
		$where = "";
		$and = "";
	} else {
		if (strpos($id_rubrique,','))
		  $eq = " IN ($id_rubrique)";
		else $eq = "=$id_rubrique";
	      
		$from = 'spip_forum AS F, spip_articles AS A';
		$where = "A.id_secteur$eq AND F.objet='article' AND F.id_objet=A.id_article";
		$and = ' AND ';
	}
   
	switch ($type) {
	case 'public':
		$and .= "F.statut IN ('publie', 'off', 'prop', 'spam') AND F.texte!=''";
		break;
	case 'prop':
		$and .= "F.statut='prop'";
		break;
	case 'spam':
		$and .= "F.statut='spam'";
		break;
	case 'interne':
		$and .= "F.statut IN ('prive', 'privrac', 'privoff', 'privadm') AND F.texte!=''";
		break;
	case 'vide':
		$and .= "F.statut IN ('publie', 'off', 'prive', 'privrac', 'privoff', 'privadm') AND F.texte=''";
		break;
	default:
		$where = '0=1';
		$and ='';
		break;
	}

	if ($recherche) {
		# recherche par IP
		if (preg_match(',^\d+\.\d+\.(\*|\d+\.(\*|\d+))$,', $recherche)) {
			$and .= " AND ip LIKE ".sql_quote(str_replace('*', '%', $recherche));
		} else {
			include_spip('inc/rechercher');
			if ($a = recherche_en_base($recherche, 'forum'))
				$and .= " AND ".sql_in('id_forum',
					array_keys(array_pop($a)));
			else
				$and .= " AND 0=1";
		}
	}

	return array($from, "$where$and");
}

// Index d'invalidation des forums
// obsolete, remplace par l'appel systematique a 2 invalideurs :
// - forum/id_forum
// - objet/id_objet
// https://code.spip.net/@calcul_index_forum
function calcul_index_forum($objet,$id_objet) {
	return substr($objet,0,1).$id_objet;
}

//
// Recalculer tous les threads
//
// https://code.spip.net/@calculer_threads
function calculer_threads() {
	// fixer les id_thread des debuts de discussion
	sql_update('spip_forum', array('id_thread'=>'id_forum'), "id_parent=0");
	// reparer les messages qui n'ont pas l'id_secteur de leur parent
	do {
		$discussion = "0";
		$precedent = 0;
		$r = sql_select("fille.id_forum AS id,	maman.id_thread AS thread", 'spip_forum AS fille, spip_forum AS maman', "fille.id_parent = maman.id_forum AND fille.id_thread <> maman.id_thread",'', "thread");
		while ($row = sql_fetch($r)) {
			if ($row['thread'] == $precedent)
				$discussion .= "," . $row['id'];
			else {
				if ($precedent)
					sql_updateq("spip_forum", array("id_thread" => $precedent), "id_forum IN ($discussion)");
				$precedent = $row['thread'];
				$discussion = $row['id'];
			}
		}
		sql_updateq("spip_forum", array("id_thread" => $precedent), "id_forum IN ($discussion)");
	} while ($discussion != "0");
}

// Calculs des URLs des forums (pour l'espace public)
// https://code.spip.net/@racine_forum
function racine_forum($id_forum){
	if (!$id_forum = intval($id_forum)) return false;

	$row = sql_fetsel("id_parent, objet, id_objet, id_thread", "spip_forum", "id_forum=".$id_forum);

	if (!$row) return false;

	if ($row['id_parent']
	AND $row['id_thread'] != $id_forum) // eviter boucle infinie
		return racine_forum($row['id_thread']);

	return array($row['objet'], $row['id_objet'], $id_forum);
} 


// https://code.spip.net/@parent_forum
function parent_forum($id_forum) {
	if (!$id_forum = intval($id_forum)) return;
	$row = sql_fetsel("id_parent, objet, id_objet", "spip_forum", "id_forum=".$id_forum);
	if(!$row) return array();
	if ($row['id_parent'])
		return array('forum', $row['id_parent']);
	else
		return array($row['objet'], $row['id_objet']);
} 


/**
 * Pour compatibilite : remplacer les appels par son contenu
 *
 * @param unknown_type $id_forum
 * @param unknown_type $args
 * @param unknown_type $ancre
 * @return unknown
 */
function generer_url_forum_dist($id_forum, $args='', $ancre='') {
	$generer_url_forum = charger_fonction('generer_url_forum','urls');
	return $generer_url_forum($id_forum, $args, $ancre);
}


// https://code.spip.net/@generer_url_forum_parent
function generer_url_forum_parent($id_forum) {
	if ($id_forum = intval($id_forum)) {
		list($type, $id) = parent_forum($id_forum);
		if ($type)
			return generer_url_entite($id, $type);
	}
	return '';
} 


// Quand on edite un forum, on tient a conserver l'original
// sous forme d'un forum en reponse, de statut 'original'
// https://code.spip.net/@conserver_original
function conserver_original($id_forum) {
	$s = sql_fetsel("id_forum", "spip_forum", "id_parent=".sql_quote($id_forum)." AND statut='original'");

	if ($s)	return ''; // pas d'erreur

	// recopier le forum
	$t = sql_fetsel("*", "spip_forum", "id_forum=".sql_quote($id_forum));

	if ($t) {
		unset($t['id_forum']);
		$id_copie = sql_insertq('spip_forum', $t);
		if ($id_copie) {
			sql_updateq('spip_forum', array('id_parent'=> $id_forum, 'statut'=>'original'), "id_forum=$id_copie");
			return ''; // pas d'erreur
		}
	}

	return '&erreur';
}

// appelle conserver_original(), puis modifie le contenu via l'API inc/modifier
// https://code.spip.net/@enregistre_et_modifie_forum
function enregistre_et_modifie_forum($id_forum, $c=false) {
	if ($err = conserver_original($id_forum)) {
		spip_log("erreur de sauvegarde de l'original, $err");
		return;
	}

	include_spip('action/editer_forum');
	return revision_forum($id_forum, $c);
}


?>