<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2011                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

// declarer la fonction du pipeline
function forum_autoriser(){}


function autoriser_foruminternesuivi_bouton_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	if(($GLOBALS['meta']['forum_prive'] == 'non') && ($GLOBALS['meta']['forum_prive_admin'] == 'non'))
		return false;
	return true;
}

function autoriser_forumreactions_bouton_dist($faire, $type='', $id=0, $qui = NULL, $opt = NULL){
	return autoriser('publierdans','rubrique',_request('id_rubrique'));
}


// Moderer le forum ?
// = modifier l'objet correspondant (si forum attache a un objet)
// = droits par defaut sinon (admin complet pour moderation complete)
// http://doc.spip.org/@autoriser_modererforum_dist
function autoriser_modererforum_dist($faire, $type, $id, $qui, $opt) {
	return
		$type?autoriser('modifier', $type, $id, $qui, $opt):autoriser('moderer', 'forum', 0, $qui, $opt);
}


// Modifier un forum ?
// = jamais !
// http://doc.spip.org/@autoriser_forum_modifier_dist
function autoriser_forum_modifier_dist($faire, $type, $id, $qui, $opt) {
	return
		false;
}


function autoriser_bouton_forum_dist($faire, $type, $id, $qui, $opt){
	return 	($GLOBALS['meta']['forum_prive'] != 'non' OR sql_countsel('spip_forum'));
}

function autoriser_bouton_forum_admin_dist($faire, $type, $id, $qui, $opt){
	return 	($GLOBALS['meta']['forum_prive_admin'] == 'oui');
}

function autoriser_bouton_controle_forum_dist($faire, $type, $id, $qui, $opt){
	return 	(sql_countsel('spip_forum'));
}

// Consulter le forum des admins ?
// admins y compris restreints
// http://doc.spip.org/@autoriser_forum_admin_dist
function autoriser_forum_admin_dist($faire, $type, $id, $qui, $opt) {
	return
		$qui['statut'] == '0minirezo'
		;
}

/**
 * Auto-association de documents sur des forum : niet
 */
function autoriser_forum_autoassocierdocument_dist($faire, $type, $id, $qui, $opts) {
	return false;
}

/**
 * Autoriser a participer au forum des admins
 *
 * @return bool
 */
function autoriser_forumadmin_participer_dist($faire, $type, $id, $qui, $opts) {
	return $qui['statut']=='0minirezo';
}

?>
