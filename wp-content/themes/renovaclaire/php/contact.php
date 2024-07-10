<?php
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/contact', array(
        'methods' => 'POST',
        'callback' => function ($request) {
            header("Access-Control-Allow-Origin: " . get_home_url());
            $data = json_decode(file_get_contents('php://input'), true);
            $nom = trim($data['prenom'] . ' ' . $data['nom']);
            $mail = [];
            if ($data['objet'] == 'devis') {
                $sujet = 'Demande de devis';
            } else if ($data['objet'] == 'etude') {
                $sujet = 'Demande d\'étude personnalisée';
            } else {
                $sujet = 'Question';
            }
            $message = '<br><b>Nom</b><br>';
            $message .= $nom;
            $message .= '<br><b>Code postal du projet</b><br>';
            $message .= $data['code_postal'];
            $message .= '<br><b>Email</b><br>';
            $message .= $data['email'];
            $message .= '<br><b>Téléphone</b><br>';
            $message .= $data['telephone'];
            $message .= '<br><br>';
            $message .= '<div style="border:1px solid black;padding:1rem;margin:1rem">' . nl2br($data['message']) . '</div>';
            $mail['to'] = get_field('contact', 'option')['destinataire'];
            // $mail['to'] = 'gilles@trend7.fr';
            $mail['subject'] = $sujet . ' venant de ' . $nom;
            $mail['replyTo'] = $data['email'];
            $mail['replyToName'] = $nom;
            $mail['fromName'] = $nom;
            $mail['message'] = $mail['subject'] . $message;

            $headers = [];
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $nom . ' <' . $mail['replyTo'] . '>',
                'Reply-To: ' . $mail['replyToName'] . ' <' . $mail['replyTo'] . '>',
            );

            $ret = wp_mail($mail['to'], $mail['subject'], $mail['message'], $headers);
            if ($ret) {
                return new WP_REST_Response(true, 200);
            } else {
                return new WP_REST_Response(false, 500);
            }
        }
    ));
});
