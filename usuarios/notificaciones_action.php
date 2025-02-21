<?php

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
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Obtener id_notificacion del formulario.
    $id_notificacion = $_POST['id_notificacion'];

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Prepared statement.
    $stmt = $mysqli->prepare("SELECT fecha_recibida FROM notificaciones WHERE dni = ? AND id_notificacion = ?");
    $stmt->bind_param("si", $dni, $id_notificacion);
    $stmt->execute();

    // Validar si la fecha_recibida de la notificacion está vacia.
    $result = $stmt->get_result();
    $notificacion = $result->fetch_assoc();
    if (empty($notificacion['fecha_recibida'])) {

        // Si es así, establecer la fecha actual.
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fecha_recibida = date("Y-m-d H:i:s");

        // Prepared statement.
        $stmt = $mysqli->prepare("UPDATE notificaciones SET fecha_recibida = ? WHERE dni = ? AND id_notificacion = ?");
        $stmt->bind_param("ssi", $fecha_recibida, $dni, $id_notificacion);
        $stmt->execute();
    }

    // Cerrar la conexión a la base de datos.
    $mysqli->close();
} else {

    // Establecer mensaje de error y redireccionar.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: menu_principal.php");
}

// Finalizar el script actual.
exit();
