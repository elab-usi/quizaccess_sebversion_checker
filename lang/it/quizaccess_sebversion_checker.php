<?php
/**
 * Settings
 */
$string['pluginname'] = 'Controllo Versione SEB';
$string['enable_windows'] = 'Attiva controllo Windows';
$string['enable_windows_desc'] = 'Se abilitato, il plugin verificherà la versione di SEB sui sistemi Windows.';
$string['allowed_seb_win'] = 'Versioni SEB Windows ammesse';
$string['allowed_seb_win_desc'] = 'Inserisci i pattern ammessi (uno per riga). Usa * come wildcard.';
$string['enable_mac'] = 'Attiva controllo MacOS';
$string['enable_mac_desc'] = 'Se abilitato, il plugin verificherà la versione di SEB sui sistemi MacOS.';
$string['allowed_seb_mac'] = 'Versioni SEB MacOS ammesse';
$string['allowed_seb_mac_desc'] = 'Inserisci i pattern ammessi (uno per riga). Usa * come wildcard.';
$string['enable_email_alerts'] = 'Attiva notifiche email';
$string['enable_email_alerts_desc'] = 'Invia una segnalazione via email quando viene rilevata una versione SEB non autorizzata o se il check non è ancora stato effettuato.';
$string['enable_prevent_access'] = 'Autorizza Prevent Access';
$string['enable_prevent_access_desc'] = 'Se attivato, blocca l\'accesso al quiz se una versione non dovesse essere nella lista abilitata.';
$string['min_date_check'] = 'Data di partenza sessione esami';
$string['min_date_check_desc'] = 'Esegui il controllo solo a partire da questa data (Formato: AAAA-MM-GG). Se l\'utente ha fatto il controllo prima di quella data, reinizia a mandare la notifica tramite email';
$string['max_date_check'] = 'Fine esami';
$string['max_date_check_desc'] = 'Esegui il controllo solo fino a questa data (Format: YYYY-MM-DD).';

/**
 * Popup strings
 */
$string['checking_seb_version'] = 'Verifica versione SEB in corso...';
$string['not_yet_inseb'] = 'Safe Exam Browser non ancora avviato.';
$string['start_seb'] = 'Clicca su Avvia Safe Exam Browser.';
$string['installed_version'] = 'Versione installata:';
$string['prevent_access_error'] = 'Non sei autorizzato a cominciare questo quiz. Installa l\'ultima versione di Safe Exam Browser o contatta il supporto tecnico.';
$string['correct_version'] = 'Hai la versione corretta.';
$string['wrong_version'] = 'NON hai la versione corretta. Installa l\'ultima versione di Safe Exam Browser o contatta il supporto tecnico.';
$string['seb_detected'] = 'Safe Exam Browser rilevato';

/**
 * Report
 */
$string['report_settings_title'] = 'SEB Version Checker Report';
$string['userid'] = 'ID Utente';
$string['version'] = 'Versione SEB';
$string['has_session'] = 'In Sessione Esame';
$string['timemodified'] = 'Ultimo Aggiornamento';
$string['never'] = 'Mai';
$string['filter_noaccess'] = 'Mai effettuato l\'accesso';
$string['filter_insession'] = 'Solo in sessione d\'esame';
$string['results'] = 'Risultati: ';