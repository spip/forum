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

if (!defined("_ECRIRE_INC_VERSION")) return;
include_spip('inc/forum');
include_spip('inc/filtres');
include_spip('inc/actions');

// Ce fichier est inclus par dist/formulaires/forum.php

// http://code.spip.net/@controler_forum_abo
function controler_forum_abo($retour)
{
	global $visiteur_session;
	if ($visiteur_session) {
		$statut = $visiteur_session['statut'];
		if (!$statut OR $statut == '5poubelle') {
			ask_php_auth(_T('forum_acces_refuse'),
				     _T('forum_cliquer_retour',
						array('retour_forum' => $retour)));
		}
	} else {
		ask_php_auth(_T('forum_non_inscrit'),
			     _T('forum_cliquer_retour',
					array('retour_forum' => $retour)));
		}
}

/**
 * Retourne pour un couple objet/id_objet donne
 * de quelle maniere les forums sont acceptes dessus
 * non: pas de forum
 * pos: a posteriori, acceptes et eventuellements moderes ensuite
 * pri: a priori, doivent etre valides par un admin
 * abo: les personnes doivent au prealable etre identifiees
 *
 * @param string $objet objet a tester
 * @param int $id_objet identifiant de l'objet
 * @param string $res chaine de 3 caractere parmi 'non','pos','pri','abo'
 */
// http://code.spip.net/@controler_forum
function controler_forum($objet, $id_objet) {
	// Valeur par defaut
	$accepter_forum = $GLOBALS['meta']["forums_publics"];
	
	// il y a un cas particulier pour l'acceptation de forum d'article...
	if ($f = charger_fonction($objet . '_accepter_forums_publics', 'inc', true)){
		$accepter_forum = $f($id_objet);
	}

	return substr($accepter_forum, 0, 3);
}

// http://code.spip.net/@mots_du_forum
function mots_du_forum($ajouter_mot, $id_message)
{
	$t = array('id_forum' => $id_message);
	foreach ($ajouter_mot as $id_mot)
		if ($t['id_mot'] = intval($id_mot))
			sql_insertq('spip_mots_forum', $t);
}


// http://code.spip.net/@reduce_strlen
function reduce_strlen($n, $c)
{
  return $n - (is_string($c) ? strlen($c) : 0);
}


// http://code.spip.net/@tracer_erreur_forum
function tracer_erreur_forum($type='') {
	spip_log("erreur forum ($type): ".print_r($_POST, true));

	define('_TRACER_ERREUR_FORUM', false);
	if (_TRACER_ERREUR_FORUM) {
		$envoyer_mail = charger_fonction('envoyer_mail','inc');
		$envoyer_mail($GLOBALS['meta']['email_webmaster'], "erreur forum ($type)",
			"erreur sur le forum ($type) :\n\n".
			'$_POST = '.print_r($_POST, true)."\n\n".
			'$_SERVER = '.print_r($_SERVER, true));
	}
}

// Un parametre permet de forcer le statut (exemple: plugin antispam)
// http://code.spip.net/@inc_forum_insert_dist
function inc_forum_insert_dist($force_statut = NULL) {
	# en reponse a
	$id_forum = intval(_request('id_forum'))>0?intval(_request('id_forum')):0;

	# objet
	$id_objet = intval(_request('id_objet'));
	$objet = _request('objet');

	# temporaire vieux code
	if (!$id_objet OR !$objet) {
		foreach (array('article', 'breve', 'rubrique', 'syndic', 'message','ticket')
		as $ob) {
			if ($id = intval(_request('id_'.$ob))) {
				$objet = $ob;
				$id_objet = $id;
			}
		}
	}

	spip_log("insertion de forum sur $objet $id_objet (+$id_forum)", 'forum');

	$reqret = rawurldecode(_request('retour_forum'));
	$retour = ($reqret !== '!')
		? $reqret
		: forum_insert_nopost($id_forum, $objet, $id_objet);

	$c = array('statut'=>'off');
	$c['objet'] = $objet;
	$c['id_objet'] = $id_objet;

	foreach (array(
		'titre', 'texte', 'nom_site', 'url_site'
	) as $champ)
		$c[$champ] = _request($champ);

	$c['auteur'] = sinon($GLOBALS['visiteur_session']['nom'],
		$GLOBALS['visiteur_session']['session_nom']);
	$c['email_auteur'] = sinon($GLOBALS['visiteur_session']['email'],
		$GLOBALS['visiteur_session']['session_email']);

	$c = pipeline('pre_edition',array(
		'args'=>array(
				'table' => 'spip_forum',
				'id_objet' => $id_forum,
				'action'=>'instituer'
		),
		'data'=>forum_insert_statut($c, $retour, $force_statut)
	));

	$id_reponse = forum_insert_base($c, $id_forum, $objet, $id_objet, $c['statut'], $retour);

	if (!$id_reponse) {
		spip_log("Echec insertion $retour", 'forum');
		return array($retour,0); // echec
	}
	else
		spip_log("forum insere' $id_reponse", 'forum');

	// En cas de retour sur (par exemple) {#SELF}, on ajoute quand
	// meme #forum12 a la fin de l'url, sauf si un #ancre est explicite
	if ($reqret !== '!')
		return array(
			strpos($retour, '#') ? $retour : $retour.'#forum'.$id_reponse,
			$id_reponse
		);

	// le retour par defaut envoie sur le thread, ce qui permet
	// de traiter elegamment le cas des forums moderes a priori.
	// Cela assure aussi qu'on retrouve son message dans le thread
	// dans le cas des forums moderes a posteriori, ce qui n'est
	// pas plus mal.
	if (function_exists('generer_url_forum')) {
		$url = generer_url_forum($id_reponse);
	} else {
		$thread = sql_fetsel('id_thread', 'spip_forum', 'id_forum='.$id_reponse);
spip_log('id_thread='.$thread['id_thread'], 'forum');
		$url = generer_url_entite($thread['id_thread'], 'forum');
	}

	return array($url, $id_reponse);
}

// http://code.spip.net/@forum_insert_base
function forum_insert_base($c, $id_forum, $objet, $id_objet, $statut, $retour)
{
	$afficher_texte = (_request('afficher_texte') <> 'non');
	$ajouter_mot = _request('ajouter_mot');

	// si le statut est vide, c'est qu'on ne veut pas de ce presume spam !
	if (!$statut)
		return false;

	//  Si forum avec previsu sans bon hash de securite, echec silencieux
	if (!test_espace_prive() AND $afficher_texte AND forum_insert_noprevisu()) {
		return false;
	}

	if (array_reduce($_POST, 'reduce_strlen', (20 * 1024)) < 0) {
		ask_php_auth(_T('forum_message_trop_long'),
			_T('forum_cliquer_retour',
				array('retour_forum' => $retour)));
	}

	// Entrer le message dans la base
	$id_reponse = sql_insertq('spip_forum', array(
		'date_heure'=> date('Y-m-d H:i:s'),
		'ip' => $GLOBALS['ip'],
		'id_auteur' => $GLOBALS['visiteur_session']['id_auteur']
	));

	if ($id_forum>0) {
		$id_thread = sql_getfetsel("id_thread", "spip_forum", "id_forum = $id_forum");
	}
	else
		$id_thread = $id_reponse; # id_thread oblige INSERT puis UPDATE.

	// Entrer les cles
	sql_updateq('spip_forum', array('id_parent' => $id_forum, 'objet' => $objet, 'id_objet' => $id_objet, 'id_thread' => $id_thread, 'statut' => $statut), "id_forum = $id_reponse");

	// Entrer les mots-cles associes
	if ($ajouter_mot) mots_du_forum($ajouter_mot, $id_reponse);

	//
	// Entree du contenu et invalidation des caches
	//
	include_spip('action/editer_forum');
	revision_forum($id_reponse, $c);

	// Ajouter un document
	if (isset($_FILES['ajouter_document'])
	AND $_FILES['ajouter_document']['tmp_name']) {
		$ajouter_documents = charger_fonction('ajouter_documents', 'inc');
		$ajouter_documents(
			$_FILES['ajouter_document']['tmp_name'],
			$_FILES['ajouter_document']['name'], 'forum', $id_reponse,
			'document', 0, $documents_actifs);
		// supprimer le temporaire et ses meta donnees
		spip_unlink($_FILES['ajouter_document']['tmp_name']);
		spip_unlink(preg_replace(',\.bin$,',
			'.txt', $_FILES['ajouter_document']['tmp_name']));
	}

	// Notification
	if ($notifications = charger_fonction('notifications', 'inc'))
		$notifications('forumposte', $id_reponse);

	return $id_reponse;
}

// calcul de l'adresse de retour en cas d'echec du POST
// mais la veritable adresse de retour sera calculee apres insertion

// http://code.spip.net/@forum_insert_nopost
function forum_insert_nopost($id_forum, $objet, $id_objet)
{
	if ($id_forum>0)
		$r = generer_url_entite($id_forum, 'forum');
	else
		$r = generer_url_entite($id_objet, $objet);

	return str_replace('&amp;','&',$r);
}

// http://code.spip.net/@forum_insert_noprevisu
function forum_insert_noprevisu()
{
	// simuler une action venant de l'espace public
	// pour se conformer au cas general.
	set_request('action', 'ajout_forum');
	// Creer une session s'il n'y en a pas (cas du postage sans cookie)
	$securiser_action = charger_fonction('securiser_action', 'inc');
	$arg = $securiser_action();

	$file = _DIR_TMP ."forum_" . preg_replace('/[^0-9]/', '', $arg) .".lck";
	if (!file_exists($file)) {
		# ne pas tracer cette erreur, peut etre due a un double POST
		# tracer_erreur_forum('session absente');
		return true;
	}
	unlink($file);

	// antispam : si le champ au nom aleatoire verif_$hash n'est pas 'ok'
	// on meurt
	if (_request('verif_'._request('hash')) != 'ok') {
			tracer_erreur_forum('champ verif manquant');
			return true;
	}
	return false;
}

// http://code.spip.net/@forum_insert_statut
function forum_insert_statut($champs, $retour, $forcer_statut=NULL)
{
	$statut = controler_forum($champs['objet'], $champs['id_objet']);

	// Ne pas autoriser d'envoi hacke si forum sur abonnement
	if ($statut == 'abo' AND !test_espace_prive()) {
		controler_forum_abo($retour); // demandera une auth http
	}

	if ($forcer_statut !== NULL)
		$champs['statut'] = $forcer_statut;
	else
		$champs['statut'] = ($statut == 'non') ? 'off' : (($statut == 'pri') ? 'prop' :	'publie');

	// Antispam basique :
	// si l'input invisible a ete renseigne, ca ne peut etre qu'un bot
	if (strlen(_request(_request('cle_ajouter_document')))) {
		tracer_erreur_forum('champ interdit (nobot) rempli');
		$champs['statut']=false;
	}

	return $champs;
}

?>
