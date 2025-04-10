<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || !in_array($_SESSION['rol'], ['alumnos', 'profesores'])) {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar información de sesión.
if (empty($_SESSION['dni'])) {
    $_SESSION['mensaje_error'] = "La sesión ha caducado.";
    header("Location: login.php");
    exit();
} else {
    $dni = $_SESSION['dni'];
}

// Validar si se reciben datos por POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['nombre']) || empty($_POST['email']) || empty($_POST['mensaje'])) {
        $_SESSION['mensaje_error'] = "Todos los campos son obligatorios.";
        header("Location: contacto.php");
        exit();
    }

    // Recuperar datos del formulario.
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $mensaje = $_POST['mensaje'];

    // Incluir el autoload de PHPMailer.
    include $_SERVER['DOCUMENT_ROOT'] . '/librerias/vendor/autoload.php';

    // Incluir archivo de configuración.
    include $_SERVER['DOCUMENT_ROOT'] . '/config.php';

    $mail = new PHPMailer(true);

    try {

        // Configurar el servidor SMTP de Gmail.
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = SENDER_EMAIL;
        $mail->Password = GMAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Remitente y destinatario.
        $mail->setFrom(SENDER_EMAIL, 'UTNFRROPPS-Contact-Us');
        $mail->addAddress('maatt.facultad@gmail.com', 'Responsable PPS');

        // Establecer contenido del correo.
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Nuevo mensaje de contacto';
        $mail->Body = "<p><strong>Nombre:</strong> $nombre</p>
                       <p><strong>Email:</strong> $email</p>
                       <p><strong>Mensaje:</strong><br>$mensaje</p>";
        $mail->AltBody = "Nombre: $nombre\nEmail: $email\nMensaje: $mensaje";

        // Enviar el correo.
        $mail->send();

        // Establecer mensaje de éxito.
        $_SESSION['mensaje_exito'] = "¡Gracias por tu mensaje! Nos pondremos en contacto contigo pronto.";
    } catch (Exception $e) {

        // Establecer mensaje de error.
        $_SESSION['mensaje_error'] = "El mensaje no pudo ser enviado. Error: " . $e->getMessage();
    }
} else {

    // Establecer mensaje de error.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
}

// Redireccionar y finalizar el script actual.
header("Location: contacto.php");
exit();
