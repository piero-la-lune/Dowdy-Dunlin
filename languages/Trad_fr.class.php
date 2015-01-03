<?php

class Trad {

		# Mots

	const W_SECONDE = 'seconde';
	const W_MINUTE = 'minute';
	const W_HOUR = 'heure';
	const W_DAY = 'jour';
	const W_WEEK = 'semaine';
	const W_MONTH = 'mois';
	const W_YEAR = 'année';
	const W_DECADE = 'décennie';
	const W_SECONDE_P = 'secondes';
	const W_MINUTE_P = 'minutes';
	const W_HOUR_P = 'heures';
	const W_DAY_P = 'jours';
	const W_WEEK_P = 'semaines';
	const W_MONTH_P = 'mois';
	const W_YEAR_P = 'années';
	const W_DECADE_P = 'décennies';

	const W_JANUARY = 'Janvier';
	const W_FEBRUARY = 'Février';
	const W_MARCH = 'Mars';
	const W_APRIL = 'Avril';
	const W_MAY = 'Mai';
	const W_JUNE = 'Juin';
	const W_JULY = 'Juillet';
	const W_AUGUST = 'Août';
	const W_SEPTEMBER = 'Septembre';
	const W_OCTOBER = 'Octobre';
	const W_NOVEMBER = 'Novembre';
	const W_DECEMBER = 'Décembre';

	const W_PREVIOUS = 'Précédent';
	const W_NEXT = 'Suivant';
	const W_CALENDAR = 'Calendrier';

		# Phrases

	const S_AGO = 'il y a %duration% %pediod%';
	const S_NOTFOUND = 'La page que vous recherchez n\'existe pas…';
	const S_EMPTY_TAGS = 'Aucun tag…';
	const S_EMPTY_COMMENT = 'Aucun commentaire…';
	const S_FROMTO_DAY = 'du %from%<br />au %to%';
	const S_FROMTO_HOUR = 'de %from% à %to%';
	const S_FROMTO_DAY_HOUR = 'du %day_start% %time_start%<br />au %day_end% %time_end%';
	const S_ON = 'le %day%';
	const S_ALL_DAY = 'Journée';
	const S_PERIOD = '%start% - %end%';

		# Verbes

	const V_LOGIN = 'Se connecter';
	const V_CONTINUE = 'Continuer';
	const V_SAVE = 'Enregistrer';
	const V_ADD = 'Ajouter';
	const V_SHOW_DONE = 'Tout afficher';
	const V_EDIT = 'Modifier';
	const V_DELETE = 'Supprimer';
	const V_CANCEL = 'Annuler';

		# Forms

	const F_USERNAME = 'Nom d\'utilisateur :';
	const F_PASSWORD = 'Mot de passe :';
	const F_COOKIE = 'Type de connexion :';
	const F_COOKIE_FALSE = 'Ordinateur public';
	const F_COOKIE_TRUE = 'Ordinateur privé (rester connecté)';
	const F_URL = 'URL :';
	const F_URL_REWRITING = 'URL rewriting :';
	const F_LANGUAGE = 'Langue :';

	const F_TITLE = 'Titre :';
	const F_COMMENT = 'Commentaire :';
	const F_DAY = 'Jours :';
	const F_HOUR = 'Heures :';
	const F_TAGS = 'Tags :';
	const F_ADD = 'ajouter…';

	const F_TIP_PASSWORD = 'Laissez vide pour ne pas le changer.';
	const F_TIP_URL_REWRITING = 'Laissez vide pour désactiver l\'URL rewriting. Sinon, indiquez le chemin du dossier de Dowdy Dunlin (en commençant et terminant par un "/") par rapport au nom de domaine.';
	const F_TIP_CALDAV = 'Pour synchroniser Dowdy Dunlin avec un serveur CalDAV. Attention : le mot de passe sera enregistré en clair.';

		# Titres

	const T_404 = 'Erreur 404 – Page non trouvée';
	const T_LOGIN = 'Connexion';
	const T_LOGOUT = 'Déconnexion';
	const T_INSTALLATION = 'Installation';
	const T_SETTINGS = 'Préférences';
	const T_GLOBAL_SETTINGS = 'Réglages généraux';
	const T_USER_SETTINGS = 'Utilisateur';
	const T_CALDAV_SETTINGS = 'CalDAV';
	const T_HOME = 'Aujourd\'hui';
	const T_WEEK = 'Cette semaine';
	const T_MONTH = 'Ce mois';
	const T_ADD = 'Ajouter un événement';
	const T_NEW = 'Nouveau';

		# Alertes

	const A_ERROR_LOGIN = 'Mauvais nom d\'utilisateur ou mot de passe.';
	const A_ERROR_LOGIN_WAIT = 'Merci de patienter %duration% %period% avant de réessayer. Ceci est une protection contre les attaques malveillantes.';
	const A_ERROR_FORM = 'Merci de remplir tous les champs.';
	const A_ERROR_EMPTY_TITLE = 'Merci de donner un titre à l\'événement.';
	const A_ERROR_AJAX = 'Une erreur est survenue. Merci de réessayer.';
	const A_ERROR_AJAX_LOGIN = 'Vous êtes déconnecté. Raffraichissez la page, connectez-vous, puis vous pourrez réessayer.';
	const A_ERROR_DAY_START = 'Le jour de début est invalide.';
	const A_ERROR_DAY_END = 'Le jour de fin est invalide.';
	const A_ERROR_DAYS = 'La date de fin doit être après celle de début.';
	const A_ERROR_TIME_START = 'L\'heure de début est invalide.';
	const A_ERROR_TIME_END = 'L\'heure de fin est invalide.';
	const A_ERROR_TIMES = 'L\'heure de fin doit être après celle de début.';
	const A_ERROR_NO_EVENT = 'Aucun événement ne correspond.';

	const A_SUCCESS_INSTALL = 'Dowdy Dunlin est maintenant correctement installé. Connectez-vous pour commencer à l\'utiliser.';
	const A_SUCCESS_SETTINGS = 'Les préférences ont bien été enregistrées.';
	const A_SUCCESS_ADD = 'L\'événement a bien été enregistré.';
	const A_SUCCESS_EDIT = 'L\'événement a bien été modifié.';
	const A_SUCCESS_DELETE = 'L\'événement a bien été supprimé.';

	const A_CONFIRM_DELETE = 'Voulez-vous vraiment supprimer cet événement ?';

	public static $settings = array(
		'validate_url' => 'L\'url n\'est pas valide.',
		'validate_caldav' => 'Impossible de se connecter au serveur CalDAV.'
	);

}

?>