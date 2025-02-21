<?php

// Inicializar sesión.
session_start();

// Validar sesión y rol.
if (empty($_SESSION['rol']) || !in_array($_SESSION['rol'], ['alumnos', 'profesores'])) {
    $_SESSION['mensaje_error'] = "Rol inválido.";
    header("Location: http://entornosgraficospps.infinityfreeapp.com/");
    exit();
}

// Validar si se reciben datos por POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['dni']) || empty($_POST['contrasena'])) {
        $_SESSION['mensaje_error'] = "Todos los campos son obligatorios.";
        header("Location: login.php");
        exit();
    }

    // Obtener datos del formulario.
    $dni = $_POST['dni'];
    $contrasena = $_POST['contrasena'];

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Obtener tabla dependiendo del rol de la sesión.
    $tabla = $_SESSION['rol'] === 'alumnos' ? 'alumnos' : 'profesores';

    // Prepared statement.
    $stmt = $mysqli->prepare('SELECT * FROM usuarios INNER JOIN ' . $tabla . ' ON usuarios.dni = ' . $tabla . '.dni WHERE usuarios.dni = ? AND contrasena = ?');
    $stmt->bind_param("ss", $dni, $contrasena);
    $stmt->execute();
    $result = $stmt->get_result();

    // Validar si el usuario existe.
    if ($result->num_rows > 0) {

        // Establecer DNI de la sesión y redireccionar.
        $usuario = $result->fetch_assoc();
        $_SESSION['dni'] = $usuario['dni'];
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
    header("Location: login.php");
}

// Finalizar el script actual.
exit();
