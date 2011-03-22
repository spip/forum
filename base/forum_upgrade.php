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

/**
 * Installation/maj des tables forum
 *
 * @param string $nom_meta_base_version
 * @param string $version_cible
 */
function forum_upgrade($nom_meta_base_version,$version_cible){
	$current_version = '0.0';

	if ( (!isset($GLOBALS['meta'][$nom_meta_base_version]) )
			|| (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){

		// installation des tables
		if (version_compare($current_version, '0.0','<=')){
			include_spip('base/forum');
			include_spip('base/create');
			// creer les tables
			creer_base();
			// mettre les metas par defaut
			$config = charger_fonction('config','inc');
			$config();
			ecrire_meta($nom_meta_base_version,$current_version = '1.0');
		}

		$maj = array();
		$maj['1.1.0'] = array(
			array('sql_alter',"TABLE spip_forum ADD id_objet bigint(21) DEFAULT 0 NOT NULL AFTER id_forum"),
			array('sql_alter',"TABLE spip_forum ADD objet VARCHAR (25) DEFAULT '' NOT NULL AFTER id_objet"),
			array('sql_alter',"TABLE spip_forum DROP key optimal"),
			array('sql_alter',"TABLE spip_forum ADD key optimal (statut,id_parent,id_objet,objet,date_heure)"),
		);
		$maj['1.1.1'] = array(
			array('sql_update',"spip_forum", array('objet' => 'breve','id_objet' => 'id_breve'), 'id_breve> 0'),
			array('sql_alter',"TABLE spip_forum DROP id_breve"),
			array('sql_update',"spip_forum", array('objet' => 'article','id_objet' => 'id_article'), 'id_article>0'),
			array('sql_alter',"TABLE spip_forum DROP id_article"),
			array('sql_update',"spip_forum", array('objet' => 'site','id_objet' => 'id_syndic'), 'id_syndic>0'),
			array('sql_alter',"TABLE spip_forum DROP id_syndic"),
			array('sql_update',"spip_forum", array('objet' => 'message','id_objet' => 'id_message'), 'id_message>0'),
			array('sql_alter',"TABLE spip_forum DROP id_message"),
			array('sql_update',"spip_forum", array('objet' => 'rubrique','id_objet' => 'id_rubrique'), 'id_rubrique>0'),
			array('sql_alter',"TABLE spip_forum DROP id_rubrique"),
		);

		# champ ip sur 40 car (compat IPv6)
		$maj['1.2.0'] = array(
			array('sql_alter',"TABLE spip_forum CHANGE ip ip VARCHAR(40) DEFAULT '' NOT NULL"),
		);

		include_spip('base/upgrade');
		maj_plugin($nom_meta_base_version, $version_cible, $maj);

	}
}

/**
 * Desinstallation/suppression des tables forum
 *
 * @param string $nom_meta_base_version
 */
function forum_vider_tables($nom_meta_base_version) {
	sql_drop_table("spip_forum");

	effacer_meta("mots_cles_forums");
	effacer_meta("forums_titre");
	effacer_meta("forums_texte");
	effacer_meta("forums_urlref");
	effacer_meta("forums_afficher_barre");
	effacer_meta("formats_documents_forum");
	effacer_meta("forums_publics");
	effacer_meta("forum_prive");
	effacer_meta("forum_prive_objets");
	effacer_meta("forum_prive_admin");

	effacer_meta($nom_meta_base_version);
}

?>
