<?php
/*
Plugin Name: Turnstile sur la demande de réinitialisation de mot de passe
Description: Protège wp-login.php?action=lostpassword par Cloudflare Turnstile, afin d'empêcher les bots de déclencher des envois d'e-mails de réinitialisation.
Version: 1.0
*/

/*
 * Nécessite deux constantes dans wp-config.php, à côté des constantes CLOUDFLARE_* :
 *   define('TURNSTILE_SITE_KEY',   '0x4AAA...');   // clé publique (visible dans le HTML)
 *   define('TURNSTILE_SECRET_KEY', '0x4AAA...');   // clé privée (ne jamais exposer)
 *
 * Tant que ces constantes ne sont pas définies, le fichier est inerte : aucun
 * risque de bloquer la réinitialisation si les clés ne sont pas encore posées.
 */

/**
 * Le garde-fou n'est actif que pour un visiteur non connecté.
 * Un admin connecté qui utilise « Envoyer un lien de réinitialisation » depuis
 * wp-admin/users.php passe aussi par retrieve_password() sans jeton Turnstile :
 * on ne doit surtout pas le bloquer.
 */
function turnstile_lp_actif()
{
    if (!defined('TURNSTILE_SITE_KEY') || !defined('TURNSTILE_SECRET_KEY')) return false;
    if (!TURNSTILE_SITE_KEY || !TURNSTILE_SECRET_KEY) return false;
    if (defined('WP_CLI') && WP_CLI) return false;
    if (is_user_logged_in()) return false;

    return true;
}

/**
 * Affiche le widget dans le formulaire « Mot de passe oublié ».
 */
add_action('lostpassword_form', function () {
    if (!turnstile_lp_actif()) return;
?>
    <div class="cf-turnstile" data-sitekey="<?php echo esc_attr(TURNSTILE_SITE_KEY); ?>" data-language="fr" style="margin:0 0 16px"></div>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php
});

/**
 * Élargit le formulaire de login, sinon le widget (300px) déborde de la boîte.
 */
add_action('login_head', function () {
    if (!turnstile_lp_actif()) return;
    if (empty($_REQUEST['action']) || !in_array($_REQUEST['action'], ['lostpassword', 'retrievepassword'], true)) return;

    echo '<style>#login{width:340px}.cf-turnstile{display:flex;justify-content:center}</style>';
});

/**
 * Vérifie le jeton côté serveur avant l'envoi de l'e-mail.
 * retrieve_password() s'arrête dès que $errors contient une erreur.
 */
add_action('lostpassword_post', function ($errors) {
    if (!turnstile_lp_actif()) return;

    $jeton = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';

    if ($jeton === '') {
        $errors->add('turnstile_manquant', __('<strong>Erreur</strong> : merci de valider le contrôle anti-robot avant d\'envoyer le formulaire.'));
        return;
    }

    $corps = ['secret' => TURNSTILE_SECRET_KEY, 'response' => $jeton];

    // Derrière le proxy Cloudflare, REMOTE_ADDR est une IP Cloudflare : n'envoyer
    // l'IP du visiteur que si l'en-tête CF-Connecting-IP est bien présent.
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = filter_var(wp_unslash($_SERVER['HTTP_CF_CONNECTING_IP']), FILTER_VALIDATE_IP);
        if ($ip) $corps['remoteip'] = $ip;
    }

    $reponse = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'timeout' => 10,
        'body'    => $corps,
    ]);

    if (is_wp_error($reponse)) {
        $errors->add('turnstile_injoignable', __('<strong>Erreur</strong> : le contrôle anti-robot est momentanément indisponible. Merci de réessayer dans quelques instants.'));
        return;
    }

    $resultat = json_decode(wp_remote_retrieve_body($reponse), true);

    if (empty($resultat['success'])) {
        $errors->add('turnstile_invalide', __('<strong>Erreur</strong> : le contrôle anti-robot a échoué. Merci de recharger la page et de réessayer.'));
    }
});
