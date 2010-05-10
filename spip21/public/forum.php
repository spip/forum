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

// ajouter certaines fonctions non modifiees de spip 2.1
include_spip('public/quete');
// quete_accepter_forum()
// lang_parametres_forum()

//
// <BOUCLE(FORUMS)>
//
// http://doc.spip.org/@boucle_FORUMS_dist
function boucle_FORUMS($id_boucle, &$boucles) {
	$boucle = &$boucles[$id_boucle];
	$id_table = $boucle->id_table;
	$mstatut = $id_table .'.statut';

	// Par defaut, selectionner uniquement les forums sans mere
	// Les criteres {tout} et {plat} inversent ce choix
	// de meme qu'un critere sur {id_forum} ou {id_parent}
	if (!isset($boucle->modificateur['tout'])
	  AND !isset($boucle->modificateur['plat'])
	  AND !isset($boucle->modificateur['criteres']['id_forum'])
	  AND !isset($boucle->modificateur['criteres']['id_parent'])
	  ) {
		array_unshift($boucle->where,array("'='", "'$id_table." ."id_parent'", 0));
	}
	// Restreindre aux elements publies
	if (!isset($boucle->modificateur['criteres']['statut'])) {
		if ($GLOBALS['var_preview'])
			array_unshift($boucle->where,array("'IN'", "'$mstatut'", "'(\\'publie\\',\\'prop\\')'"));
		else
			array_unshift($boucle->where,array("'='", "'$mstatut'", "'\\'publie\\''"));
	}

	return calculer_boucle($id_boucle, $boucles);
}

// {meme_parent}
// http://www.spip.net/@meme_parent
// http://doc.spip.org/@critere_meme_parent_dist
function critere_FORUMS_meme_parent($idb, &$boucles, $crit) {
	global $exceptions_des_tables;
	$boucle = &$boucles[$idb];
	$arg = kwote(calculer_argument_precedent($idb, 'id_parent', $boucles));
	$id_parent = isset($exceptions_des_tables[$boucle->id_table]['id_parent']) ?
		$exceptions_des_tables[$boucle->id_table]['id_parent'] :
		'id_parent';
	$mparent = $boucle->id_table . '.' . $id_parent;

	$boucle->where[]= array("'='", "'$mparent'", $arg);
	$boucle->where[]= array("'>'", "'$mparent'", 0);
	$boucle->modificateur['plat'] = true;
}

// Faute de copie du champ id_secteur dans la table des forums,
// faut le retrouver par jointure
// Pour chaque Row il faudrait tester si le forum est
// d'article, de breve, de rubrique, ou de syndication.
// Pour le moment on ne traite que les articles,
// les 3 autres cas ne marcheront donc pas: ca ferait 4 jointures
// qu'il faut traiter optimalement ou alors pas du tout.
function public_critere_secteur_forums($idb, &$boucles, $val, $crit)
{
	static $trouver_table;
	if (!$trouver_table)
		$trouver_table = charger_fonction('trouver_table', 'base');

	$desc = $trouver_table('articles', $boucles[$idb]->sql_serveur);
	return calculer_critere_externe_init($boucles[$idb], array($desc['table']), 'id_secteur', $desc, $crit->cond, true);
}


//
// Parametres de reponse a un forum
//
// Cette balise peut etre executee en dehors de toute boucle,
// par exemple en tete de inc-forums.html ; impossible donc de
// savoir a quel objet elle va s'appliquer, ca dependra du contexte
//
// http://doc.spip.org/@balise_PARAMETRES_FORUM_dist
function balise_PARAMETRES_FORUM($p) {
spip_log('parametres_forum','forums');
	// s'il y a un id_article dans le contexte, regarder le statut
	// accepter_forum de cet article
	$_id_article = champ_sql('id_article', $p);
	$p->code = '
		// refus des forums ?
		(quete_accepter_forum('.$_id_article.')=="non" OR
		($GLOBALS["meta"]["forums_publics"] == "non"
		AND quete_accepter_forum('.$_id_article.') == ""))
		? "" : // sinon:
		';

	// pas de calculs superflus si le site est monolingue
	$lang = strpos($GLOBALS['meta']['langues_utilisees'], ',');

	// si on est dans une boucle de forums, c'est une reponse
	if ($p->type_requete == 'forums')
		$_id_reponse = champ_sql('id_forum', $p);
	else
		$_id_reponse = "null";

	// objet et id_objet principaux sont a determiner
	// dans le contexte ; on demande en tout etat de cause
	// a la boucle mere de reserver son id_primary
	if ($p->id_boucle
	AND isset($p->boucles[$p->id_boucle])
	AND $primary = $p->boucles[$p->id_boucle]->primary
	) {
		$_type = $p->boucles[$p->id_boucle]->type_requete;
		$_primary = champ_sql($primary, $p);
	} else {
		$_type = "null";
		$_primary = "null";
	}

	// le code de base des parametres
	$c = 'calcul_parametres_forum($Pile[0],'
		.$_id_reponse.','.$_type.','.$_primary.')';

	// ajouter la lang, eventuellement donnee par le contexte
	if ($lang) {
		$_lang = champ_sql('lang', $p);
		$c = "lang_parametres_forum($c,$_lang)";
	}

	// Syntaxe [(#PARAMETRES_FORUM{#SELF})] pour fixer le retour du forum
	# note : ce bloc qui sert a recuperer des arguments calcules pourrait
	# porter un nom et faire partie de l'API.
	$retour = interprete_argument_balise(1,$p);
	if ($retour===NULL)
		$retour = "''";

	// Attention un eventuel &retour=xxx dans l'URL est prioritaire
	$c .= '.
	(($lien = (_request("retour") ? _request("retour") : str_replace("&amp;", "&", '.$retour.'))) ? "&retour=".rawurlencode($lien) : "")';

	$c = '('.$c.')';
	// Ajouter le code d'invalideur specifique a cette balise
	include_spip('inc/invalideur');
	if ($i = charger_fonction('code_invalideur_forums','',true))
		$p->code .= $i($p, $c);
	else
		$p->code .= $c;

	$p->interdire_scripts = false;
	return $p;
}

// Cette fonction est appellee avec le contexte + trois parametres optionnels :
// 1. $reponse = l'id_forum auquel on repond
// 2. $type = le type de boucle dans lequel on se trouve, le cas echeant
// 3. $primary = l'id_objet de la boucle dans laquelle on se trouve
// elle doit renvoyer '', 'id_article=5' ou 'id_article=5&id_forum=12'
// selon les cas
function calcul_parametres_forum(&$env, $reponse, $type, $primary) {
	spip_log('calcul_parametres_forum','forums');
	// si c'est une reponse, on peut esperer que (objet,id_objet) sont dans
	// la boucle mere, mais il est possible que non (forums imbriques etc)
	// dans ce cas on va chercher dans la base.
	if ($id_parent = intval($reponse)) {
		if ($type
		AND $type!='forums'
		AND $primary)
			$forum = array('objet' => $type, 'id_objet' => $primary);
		else
			$forum = sql_fetsel('objet, id_objet', 'spip_forum', 'id_forum='.$id_parent);

		if ($forum)
			return id_table_objet($forum['objet']).'='.$forum['id_objet']
			.'&id_forum='.$id_parent;
		else
			return '';
	}

	// Ce n'est pas une reponse, on prend la boucle mere
	if ($type AND $primary)
		return id_table_objet($type).'='.intval($primary);

	// dernier recours, on regarde pour chacun des objets forumables
	// ce que nous propose le contexte #ENV
	foreach ($env as $k => $v) {
		if (preg_match(',^id_([a_z_]+)$,S', $k)
		AND $id = intval($v)) {
			return id_table_objet($k).'='.$v;
		}
	}

	return '';
}



// Pour que le compilo ajoute un invalideur a la balise #PARAMETRES_FORUM
// Noter l'invalideur de la page contenant ces parametres,
// en cas de premier post sur le forum
// http://doc.spip.org/@code_invalideur_forums
function code_invalideur_forums_dist($p, $code) {
	return $code;
}
?>