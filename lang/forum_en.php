<?php
// This is a SPIP language file  --  Ceci est un fichier langue de SPIP
// extrait automatiquement de http://www.spip.net/trad-lang/
// ** ne pas modifier le fichier **

if (!defined('_ECRIRE_INC_VERSION')) return;

$GLOBALS[$GLOBALS['idx_lang']] = array(

	// A
	'aucun_message_forum' => 'Aucun message de forum', # NEW

	// B
	'bouton_radio_articles_futurs' => 'only to future articles (no change to the database).',
	'bouton_radio_articles_tous' => 'to all articles without exception.',
	'bouton_radio_articles_tous_sauf_forum_desactive' => 'to all articles, except those whose forum was disabled.',
	'bouton_radio_enregistrement_obligatoire' => 'Registration required (
  users must subscribe by providing their email address before
  being able to post contributions).',
	'bouton_radio_moderation_priori' => 'Pre-moderation (
 contributions will be shown only after validation by
 administrators).',
	'bouton_radio_modere_abonnement' => 'registration required',
	'bouton_radio_modere_posteriori' => 'post-moderation',
	'bouton_radio_modere_priori' => 'pre-moderation',
	'bouton_radio_publication_immediate' => 'Immediate publication of messages
 (contributions will be shown as soon as they are sent, administrators can
 then delete them if necessary).',

	// D
	'documents_interdits_forum' => 'Documents not allowed in this forum',

	// E
	'erreur_enregistrement_message' => 'Votre message n\'a pas pu être enregistré en raison d\'un problème technique', # NEW

	// F
	'form_pet_message_commentaire' => 'Any message or comments?',
	'forum' => 'Forum',
	'forum_acces_refuse' => 'You no longer have access to these forums.',
	'forum_attention_dix_caracteres' => '<b>Warning!</b> Your message must be at least ten characters long.',
	'forum_attention_trois_caracteres' => '<b>Warning!</b> Your title must be at least three characters long.',
	'forum_attention_trop_caracteres' => '<b>Warning !</b> Your message is too long (@compte@ characters) : a message cannot contain more than @max@ characters.',
	'forum_avez_selectionne' => 'You have selected:',
	'forum_cliquer_retour' => 'Click  <a href=\'@retour_forum@\'>here</a> to continue.',
	'forum_forum' => 'forum',
	'forum_info_modere' => 'Posts in this forum are moderated before publication: your contribution will only appear after being validated by a site administrator.',
	'forum_lien_hyper' => '<b>Hypertext link</b> (optional)',
	'forum_message_definitif' => 'Finished editing: submit message',
	'forum_message_trop_long' => 'Your message is too long. Maximum length is  20000 characters.',
	'forum_ne_repondez_pas' => 'Do not reply directly to this email - please use the online forum here:',
	'forum_page_url' => '(If your message refers to an article published on the web or to a page providing further information, please enter the title of the page and its URL below).',
	'forum_poste_par' => 'Message posted@parauteur@ following your article "@titre@".',
	'forum_qui_etes_vous' => '<b>Who are you?</b> (optional)',
	'forum_texte' => 'Text of your message:',
	'forum_titre' => 'Subject:',
	'forum_url' => 'URL:',
	'forum_valider' => 'Validate your choice',
	'forum_voir_avant' => 'Preview message before posting',
	'forum_votre_email' => 'Your email address:',
	'forum_votre_nom' => 'Your name (or alias):',
	'forum_vous_enregistrer' => 'You must be registered before participating in
  this forum. Please
  enter your personal identifier
. If you have not yet registered, you must',
	'forum_vous_inscrire' => 'register.',

	// I
	'icone_bruler_message' => 'Signaler comme Spam', # NEW
	'icone_bruler_messages' => 'Signaler comme Spam', # NEW
	'icone_legitimer_message' => 'Signaler comme licite', # NEW
	'icone_poster_message' => 'Post a message',
	'icone_suivi_forum' => 'Follow public forum: @nb_forums@ contribution(s)',
	'icone_suivi_forums' => 'Manage forums',
	'icone_supprimer_message' => 'Delete this message',
	'icone_supprimer_messages' => 'Supprimer ces messages', # NEW
	'icone_valider_message' => 'Validate message',
	'icone_valider_messages' => 'Valider ces messages', # NEW
	'icone_valider_repondre_message' => 'Valider &amp; Répondre à ce message', # NEW
	'info_1_message_forum' => '1 message de forum', # NEW
	'info_activer_forum_public' => '<i>To enable public forums, please choose the default type of moderation:</i>',
	'info_appliquer_choix_moderation' => 'Apply this choice for moderation:',
	'info_config_forums_prive' => 'You can activate various types of forum in the private area:',
	'info_config_forums_prive_admin' => 'A forum reserved for site administrators:',
	'info_config_forums_prive_global' => 'A global forum, open to all the authors:',
	'info_config_forums_prive_objets' => 'A forum attached to each article, news item, referenced site, etc.:',
	'info_desactiver_forum_public' => 'Disable the use of public
 forums. Public forums can be enabled on a case by case
 basis for articles; they will be permanently disabled for sections, news, etc.',
	'info_envoi_forum' => 'Send article forums to the respective authors',
	'info_fonctionnement_forum' => 'Forum operation:',
	'info_forums_liees_mot' => 'Les messages de forum liés à ce mot', # NEW
	'info_gauche_suivi_forum_2' => 'The <i>forums management</i> page is a site management tool, not a discussion or editing area. It displays all contributions to the forums (both on the public site and in the private area) and allows you to manage these contributions.',
	'info_liens_syndiques_3' => 'forums',
	'info_liens_syndiques_4' => 'are',
	'info_liens_syndiques_5' => 'forum',
	'info_liens_syndiques_6' => 'is',
	'info_liens_syndiques_7' => 'awaiting validation.',
	'info_liens_texte' => 'Lien(s) contenu(s) dans le texte du message', # NEW
	'info_liens_titre' => 'Lien(s) contenu(s) dans le titre du message', # NEW
	'info_mode_fonctionnement_defaut_forum_public' => 'Default mode for public forums',
	'info_nb_messages_forum' => '@nb@ messages de forum', # NEW
	'info_option_email' => 'When a visitor posts a message to the forum of an article, the article\'s author(s) can be informed of this by email. Indicate for each type of forum if this option should be activated.',
	'info_pas_de_forum' => 'no forum',
	'info_question_visiteur_ajout_document_forum' => 'If you want to allow visitors to attach documents (images, sound files, etc.) to their forum messages, indicate which file extensions are accepted (e.g. gif, jpg, png, mp3).',
	'info_question_visiteur_ajout_document_forum_format' => 'If you want to authorise all document types considered safe by SPIP, use an asterisk. Leave empty to block everything.',
	'interface_formulaire' => 'Interface formulaire', # NEW
	'interface_onglets' => 'Interface avec onglets', # NEW
	'item_activer_forum_administrateur' => 'Enable administrators\' forum',
	'item_config_forums_prive_global' => 'Activate the authors\' forum',
	'item_config_forums_prive_objets' => 'Activate these forums',
	'item_desactiver_forum_administrateur' => 'Disable administrators\' forum',
	'item_non_config_forums_prive_global' => 'Disable authors\' forum',
	'item_non_config_forums_prive_objets' => 'Disable these forums',

	// L
	'lien_reponse_article' => 'Comment on this article',
	'lien_reponse_breve_2' => 'Comment on this news item',
	'lien_reponse_rubrique' => 'Comment on this section',
	'lien_reponse_site_reference' => 'Comment on this website:',

	// M
	'messages_aucun' => 'Aucun', # NEW
	'messages_meme_auteur' => 'Tous les messages de cet auteur', # NEW
	'messages_meme_email' => 'Tous les messages de cet email', # NEW
	'messages_meme_ip' => 'Tous les messages de cette IP', # NEW
	'messages_off' => 'Supprimés', # NEW
	'messages_perso' => 'Personnels', # NEW
	'messages_privadm' => 'Administrateurs', # NEW
	'messages_prive' => 'Privés', # NEW
	'messages_privoff' => 'Supprimés', # NEW
	'messages_privrac' => 'Généraux', # NEW
	'messages_prop' => 'Proposés', # NEW
	'messages_publie' => 'Publiés', # NEW
	'messages_spam' => 'Spam', # NEW
	'messages_tous' => 'Tous', # NEW

	// O
	'onglet_messages_internes' => 'Private messages',
	'onglet_messages_publics' => 'Public messages',
	'onglet_messages_vide' => 'Messages without text',

	// R
	'repondre_message' => 'Reply to this message',

	// S
	'statut_off' => 'Supprimé', # NEW
	'statut_original' => 'original',
	'statut_prop' => 'Proposé', # NEW
	'statut_publie' => 'Publié', # NEW
	'statut_spam' => 'Spam', # NEW

	// T
	'text_article_propose_publication_forum' => 'You can comment on this article via the forum below.',
	'texte_en_cours_validation' => 'Les articles, brèves, forums ci dessous sont proposés à la publication.', # NEW
	'texte_en_cours_validation_forum' => 'You can comment freely in the forums attached.',
	'texte_messages_publics' => 'Messages publics sur&nbsp;:', # NEW
	'titre_cadre_forum_administrateur' => 'Administrators\' private forum',
	'titre_cadre_forum_interne' => 'Internal forum',
	'titre_config_forums_prive' => 'Forums in the private area',
	'titre_forum' => 'Forum',
	'titre_forum_suivi' => 'Forums follow-up',
	'titre_page_forum_suivi' => 'Forums follow-up',
	'titre_selection_action' => 'Sélection', # NEW
	'tout_voir' => 'Voir tous les messages', # NEW

	// V
	'voir_messages_objet' => 'voir les messages' # NEW
);

?>
