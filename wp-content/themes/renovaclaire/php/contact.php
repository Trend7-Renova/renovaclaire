<?php
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/contact', array(
        'methods' => 'POST',
        'callback' => function ($request) {
            header("Access-Control-Allow-Origin: " . get_home_url());
            $data = json_decode(file_get_contents('php://input'), true);
            $mail = [];
            if ($data['sujet'] == 'devis') {
                $sujet = 'Demande de devis';
            } else {
                $sujet = 'Question';
            }
            $message = '<br><b>Nom</b><br>';
            $message .= $data['nom'];
            $message = '<br><b>Email</b><br>';
            $message .= $data['email'];
            $message = '<br><b>Téléphone</b><br>';
            $message .= $data['telephone'];
            $message .= '<hr>';
            $message .= nl2br($data['message']);
            $mail['to'] = get_field('contact', 'option')['destinataire'];
            $mail['subject'] = $sujet . ' venant de ' . $data['nom'];
            $mail['replyTo'] = $data['email'];
            $mail['replyToName'] = $data['nom'];
            $mail['fromName'] = $data['nom'];
            $mail['message'] = $mail['subject'] . $message;

            $headers = [];
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . $data['nom'] . ' <' . $mail['replyTo'] . '>',
                'Reply-To: ' . $mail['replyToName'] . ' <' . $mail['replyTo'] . '>',
            );

            $ret = wp_mail($mail['to'], $mail['subject'], $mail['message'], $headers);
            $ret = true;
            if ($ret) {
                return new WP_REST_Response(true, 200);
            } else {
                return new WP_REST_Response(false, 500);
            }

        }
    ));
});
