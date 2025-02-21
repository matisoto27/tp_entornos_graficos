<?php

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
    if (!isset($_POST['activo']) || empty($_POST['dni'])) {
        $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
        header("Location: menu_principal.php");
        exit();
    }

    // Recuperar datos del formulario.
    $activo = ($_POST['activo'] == 1) ? 0 : 1;
    $dni = $_POST['dni'];

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Prepared statement.
    $stmt = $mysqli->prepare("UPDATE profesores SET activo = ? WHERE dni = ?");
    $stmt->bind_param("is", $activo, $dni);
    $stmt->execute();

    // Cerrar la conexión a la base de datos, redireccionar y finalizar el script actual.
    $mysqli->close();
    header("Location: alta_baja_profesores.php");
    exit();
} else {

    // Establecer mensaje de error, redireccionar y finalizar el script actual.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: menu_principal.php");
    exit();
}
