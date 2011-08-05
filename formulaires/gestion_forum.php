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

function formulaires_gestion_forum_charger_dist($id_forum='', $id_rubrique='', $id_article='', $id_breve='', $id_syndic='', $id_message='', $id_auteur='', $auteur='', $email_auteur='', $ip='',$objet='',$id_objet='') {
	
	$valeurs = array(
		'editable'=>true
		);
	
	$valeurs['id_forums'] = array();
	$valeurs['pagination'] = _request('pagination');
	$valeurs['select_type'] = _request('select_type');
	$valeurs['select_statut'] = _request('select_statut');
	
	$valeurs['id_forum'] = _request('id_forum',$id_forum);
	$valeurs['id_rubrique'] = _request('id_rubrique',$id_rubrique);
	$valeurs['id_article'] = _request('id_article',$id_article);
	$valeurs['id_breve'] = _request('id_breve',$id_breve);
	$valeurs['id_syndic'] = _request('id_syndic',$id_syndic);
	$valeurs['id_message'] = _request('id_message',$id_message);
	$valeurs['id_auteur'] = _request('id_auteur',$id_auteur);
	$valeurs['objet'] = _request('objet',$objet);
	$valeurs['id_objet'] = _request('id_objet',$id_objet);
	$valeurs['auteur'] = _request('auteur',$auteur);
	$valeurs['email_auteur'] = _request('email_auteur',$email_auteur);
	$valeurs['ip'] = _request('ip',$ip);
	$valeurs['debut_forum'] = _request('debut_forum',$debut_forum);
	$valeurs['recherche'] = _request('recherche');
	
	return $valeurs;
}

function formulaires_gestion_forum_verifier_dist($id_forum='', $id_rubrique='', $id_article='', $id_breve='', $id_syndic='', $id_message='', $id_auteur='', $auteur='', $email_auteur='', $ip='',$objet='',$id_objet='') {

	$erreurs = array();
	
	return $erreurs;
}


function formulaires_gestion_forum_traiter_dist($id_forum='', $id_rubrique='', $id_article='', $id_breve='', $id_syndic='', $id_message='', $id_auteur='', $auteur='', $email_auteur='', $ip='',$objet='',$id_objet='') {

	$retour = array();
	
	if(_request('valider') OR _request('bruler') OR _request('supprimer'))
		$retour['message_ok'] = _T('forum:message_rien_a_faire');
	
	if (!$forum_ids = _request('forum_ids'))
		$forum_ids = array();
	
	$select_type = _request('select_type');
	$select_statut = _request('select_statut');
	$pagination = _request('pagination');
	$pagination_ancien = _request('pagination_ancien');

	set_request('select_type',$select_type);
	set_request('voir_staut',$select_statut);
	
	if ($pagination != $pagination_ancien)
		set_request('debut_forum','');
	
	if (_request('valider') && (count($forum_ids) > 0)){
		$statut = 'publie';
		$retour['message_ok'] = singulier_ou_pluriel(count($forum_ids), 'forum:message_publie', 'forum:messages_publies');
	}
	
	if (_request('bruler') && (count($forum_ids) > 0)){
		$statut = 'spam';
		$retour['message_ok'] = singulier_ou_pluriel(count($forum_ids), 'forum:message_marque_comme_spam', 'forum:messages_marques_comme_spam');
	}
	
	if(_request('supprimer') && (count($forum_ids) > 0)){
		$statut = 'off';
		$retour['message_ok'] = singulier_ou_pluriel(count($forum_ids), 'forum:message_supprime', 'forum:messages_supprimes');
	}
	
	include_spip('action/instituer_forum');
	foreach ($forum_ids as $id) {
		$row = sql_fetsel("*", "spip_forum", "id_forum=$id");
		if($statut == "publie" and $row['statut'] == "privoff")
			$statut = "prive";
		if($statut == "off" and $row['statut'] == "prive")
			$statut = "privoff";	
		if($statut == "off" and $row['statut'] == "privrac")
			$statut = "privoff";		
		instituer_un_forum($statut,$row);
	}
	
	return $retour;
}

?>
