<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || $_SESSION['rol'] !== 'responsables') {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar si se reciben datos por POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['codigo']) || empty($_POST['contrasena'])) {
        $_SESSION['mensaje_error'] = "Todos los campos son obligatorios.";
        header("Location: login.php");
        exit();
    }

    // Recuperar datos del formulario.
    $codigo = $_POST['codigo'];
    $contrasena = $_POST['contrasena'];

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Prepared statement.
    $stmt = $mysqli->prepare("SELECT * FROM responsables WHERE codigo = ? AND contrasena = ?");
    $stmt->bind_param("ss", $codigo, $contrasena);
    $stmt->execute();
    $result = $stmt->get_result();

    // Validar si el responsable existe.
    if ($result->num_rows > 0) {

        // Establecer codigo, nombre y apellido de la sesión, y luego redireccionar.
        $responsable = $result->fetch_assoc();
        $_SESSION['codigo'] = $responsable['codigo'];
        $_SESSION['nombre'] = $responsable['nombre'];
        $_SESSION['apellido'] = $responsable['apellido'];
        header("Location: menu_principal.php");
    } else {

        // Establecer mensaje de error y redireccionar.
        $_SESSION['mensaje_error'] = "Las credenciales no coinciden.";
        header("Location: login.php");
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
