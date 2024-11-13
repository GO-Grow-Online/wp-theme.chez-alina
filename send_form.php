<?php

require_once 'vendor/phpmailer/phpmailer/src/Exception.php';
require_once 'vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once 'vendor/phpmailer/phpmailer/src/SMTP.php';

// Vérification du formulaire soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $data = array();
    $response = array();
    $response['error'] = "";

    $form_valid = true;
    $form_complete = true;

    // Get every fields values
    foreach ($_POST as $name => $value) {
        $data[$name] = $value;
    }

    // Honeypot verification
    $honeypot = $data["send"];
    
    if (empty($honeypot)) {

        // Check required field
        // WARNING : edit html classes if chaging required fields
        $required_fields = [
            'name',
            'subject',
            'email',
        ];

        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                $form_complete = false;
            }
        }

        $form_valid = $form_complete;
        $response['error'] .= '<p>Veuillez compléter les champs indiqués.</p>';
        
        // Check if mail is valid
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $response['error'] .= '<p>Veuillez entrer une adresse e-mail valide.</p>';
            $form_valid = false;
        }

        // Check if phone is valid
        if (!empty($data['phone']) && !preg_match("/^\+?(\d{1,4}[-.\s]?){1,14}(\(\d+\))?[-.\s]?\d+$/", $data['phone'])) {
            $response['error'] .= '<p>Veuillez entrer un numéro de téléphone valide.</p>';
            $form_valid = false;
        }

        // If form is allright, sand
        if ($form_valid) {

            // Add custom HTML template for mail and replace "{ field key }" with the form values
            $body = '<!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Formulaire de contact</title>
                <style>
                    .email-body {
                        font-family: Arial, sans-serif;

                        background: #fff;

                        margin: 0;
                        padding: 40px;
                    }
                    .email-container {
                        max-width: 600px;
                        margin: 0 auto;
                        background-color: #ffffff;
                    }
                    ul {
                        padding: 0;
                        list-style: none;
                    }
                    .content-card {
                        padding: 20px;
                        border-top: 5px dotted #313131;
                    }
            
                    .content-card p:first-child {
                        margin-top: -15px;
                        margin-left: -5px;
                        padding: 0 5px;
                        width: fit-content;
                        background-color: white;
                    }
            
                    .header {
                        text-align: center;
                        background-color: #313131;
                        padding: 20px;
                        border-radius: 10px;
                    }
                    .header h1 {
                        margin: 20px 0;
                        color: #ffffff;
                    }
                    .content {
                        padding: 20px 0;
                    }
                    .content h2 {
                        color: #333333;
                        margin-bottom: 30px;
                    }
                    .content p {
                        color: #666666;
                        line-height: 1.7;
                    }
                    .footer {
                        text-align: center;
                        padding: 20px;
                        background-color: #f4f4f4;
                        font-size: 12px;
                        color: #8b8b8b;
                    }
                </style>
            </head>
            <body>
            <div class="email-body">
                <div class="email-container">
                    <div class="header">
                        <h1>Formulaire de contact</h1>
                    </div>
                    <div class="content">
                        <h2>[name] veut vous contacter !</h2>
                        <div class="content-card">
                            <p class="content-card-title"><small>Coordonées :</small></p>
                            <p><strong>Nom :</strong> [name]</p>
                            <p><strong>Email :</strong> [email]</p>
                            <p><strong>Téléphone :</strong> [phone]</p>
                        </div>
            
                        <div class="content-card">
                            <p class="content-card-title"><small>Message :</small></p>
            
                            <p><strong>[subject]</strong></p>
                            <p>[message]</p>
                        </div>
                    </div>
                    <div class="footer">
                        <p>&copy; 2024 GO : Grow Online. Tous droits réservés.</p>
                        <p><a href="https://www.grow-online.be" target="_blank">www.grow-online.be</a></p>
                        <p><a href="tel:+32489180888">+32 489 180 888</a></p>
                    </div>
                </div>
                </div>
            </body>
            </html>
            ';
        
            if(isset($data)){
                foreach($data as $key => $value){
                    $value = !empty($value) ? $value : "<i>Aucun(e)</i>";
                    $body = str_replace('['.$key.']', $value, $body);
                }
            } 

            $mail = new PHPMailer\PHPMailer\PHPMailer();
        
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPSecure = "ssl"; 
            $mail->Port = 465;
            $mail->SMTPAuth = true;
            $mail->Username = "julien.growonline@gmail.com";
            $mail->Password = "dbiqnrvmmsqcrira";
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
        
            $mail->setFrom($data['email'], $data['name']);
            $mail->addAddress("julien.growonline@gmail.com", "GO : Grow Online");
        
            // Mail content
            $mail->Subject = "Formulaire de : " . $data['name'];
            $mail->Body = $body;
        
            if ($mail->send()) {
                $response['success'] = true;
            } else {
                $response['error'] =  "<p>Le mail n'a pas pu être envoyé : " . $mail->ErrorInfo . "</p>";
            }
        }

    // If honeypot is completed :
    }else{
        $response['error'] = "<p>Le mail n'a pas été envoyé. Spam-bot détecté.</p>";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}

?>