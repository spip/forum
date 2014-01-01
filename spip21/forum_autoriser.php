<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2014                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

// declarer la fonction du pipeline
function forum_autoriser(){}

if(!function_exists('autoriser_forum_interne_suivi_bouton_dist')){
	function autoriser_forum_interne_suivi_bouton_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
		return true;
	}
}

if(!function_exists('autoriser_forum_reactions_bouton_dist')){
	function autoriser_forum_reactions_bouton_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
		return autoriser('publierdans','rubrique',_request('id_rubrique'));
	}
}

if(!function_exists('autoriser_bouton_forum_dist')){
	function autoriser_bouton_forum_dist($faire, $type, $id, $qui, $opt){
		return 	($GLOBALS['meta']['forum_prive'] != 'non' OR sql_countsel('spip_forum'));
	}
}

if(!function_exists('autoriser_bouton_forum_admin_dist')){
	function autoriser_bouton_forum_admin_dist($faire, $type, $id, $qui, $opt){
		return 	($GLOBALS['meta']['forum_prive_admin'] == 'oui');
	}
}

if(!function_exists('autoriser_bouton_controle_forum_dist')){
	function autoriser_bouton_controle_forum_dist($faire, $type, $id, $qui, $opt){
		return 	(sql_countsel('spip_forum'));
	}
}

// Consulter le forum des admins ?
// admins y compris restreints
// http://doc.spip.org/@autoriser_forum_admin_dist
if(!function_exists('autoriser_forum_admin_dist')){
function autoriser_forum_admin_dist($faire, $type, $id, $qui, $opt) {
	return
		$qui['statut'] == '0minirezo'
		;
}
}

?>
