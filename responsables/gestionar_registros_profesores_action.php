<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'responsables') {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar información de sesión.
if (empty($_SESSION['codigo']) || empty($_SESSION['nombre']) || empty($_SESSION['apellido'])) {
    $_SESSION['mensaje_error'] = "La sesión ha caducado.";
    header("Location: login.php");
    exit();
}

// Validar si se reciben datos por POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['dni']) || empty($_POST['accion'])) {
        $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
        header("Location: menu_principal.php");
        exit();
    }

    // Recuperar datos del formulario.
    $dni = $_POST['dni'];
    $accion = $_POST['accion'];

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Validar si la acción es aprobar o rechazar.
    if ($accion === 'rechazar') {

        // Prepared statement.
        $stmt = $mysqli->prepare("DELETE FROM usuarios WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
    } else {

        // Recuperar datos del profesor.
        $stmt = $mysqli->prepare("SELECT nombre, apellido, email FROM usuarios WHERE dni = ?");
        $stmt->bind_param("s", $dni);
        $stmt->execute();
        $result = $stmt->get_result();
        $profesor = $result->fetch_assoc();
        $nombre = $profesor['nombre'];
        $apellido = $profesor['apellido'];
        $email = $profesor['email'];

        // Generar contraseña.
        function generarContrasena()
        {
            $caracteres = 'abcdefghijklmnopqrstuvwxyz0123456789';
            $contrasena = '';
            for ($i = 0; $i < 8; $i++) {
                $index = rand(0, strlen($caracteres) - 1);
                $contrasena .= $caracteres[$index];
            }
            return $contrasena;
        }
        $contrasena = generarContrasena();

        // Iniciar la transacción de la base de datos.
        $mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);

        // Prepared statement.
        $stmt = $mysqli->prepare("UPDATE usuarios SET contrasena = ? WHERE dni = ?");
        $stmt->bind_param("ss", $contrasena, $dni);
        $stmt->execute();

        // Prepared statement.
        $stmt = $mysqli->prepare("INSERT INTO profesores (dni, activo) VALUES (?, 1)");
        $stmt->bind_param("s", $dni);
        $stmt->execute();

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
            $mail->setFrom(SENDER_EMAIL, SENDER_NAME);
            $mail->addAddress($email, $nombre . ' ' . $apellido);

            // Establecer contenido del correo.
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = '¡Bienvenido Profesor!';
            $mail->Body = 'Hemos verificado tu información y te damos acceso a nuestro sistema. A continuación, encontrarás la contraseña que utilizarás para iniciar sesión:<br><br>Contraseña: <b>' . $contrasena . '</b><br><br>Recuerda cambiar tu contraseña al ingresar por primera vez para garantizar la seguridad de tu cuenta.<br><br>Si tienes alguna pregunta o necesitas asistencia, no dudes en ponerte en contacto con el equipo administrativo.';
            $mail->AltBody = 'Hemos verificado tu información y te damos acceso a nuestro sistema. A continuación, encontrarás la contraseña que utilizarás para iniciar sesión:\n\nContraseña: ' . $contrasena . '\n\nRecuerda cambiar tu contraseña al ingresar por primera vez para garantizar la seguridad de tu cuenta.\n\nSi tienes alguna pregunta o necesitas asistencia, no dudes en ponerte en contacto con el equipo administrativo.';

            // Enviar el correo.
            $mail->send();
        } catch (Exception $e) {

            // Revertir los cambios, establecer mensaje de error, redireccionar y finalizar el script actual.
            $mysqli->rollback();
            $_SESSION['mensaje_error'] = "El mensaje no pudo ser enviado. Error: " . $e->getMessage();
            header("Location: menu_principal.php");
            exit();
        }

        // Confirmar cambios.
        $mysqli->commit();
    }

    // Cerrar la conexión a la base de datos, redireccionar y finalizar el script actual.
    $mysqli->close();
    header("Location: gestionar_registros_profesores.php");
    exit();
} else {

    // Establecer mensaje de error, redireccionar y finalizar el script actual.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: menu_principal.php");
    exit();
}
