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

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['nombre']) || empty($_POST['apellido']) || empty($_POST['fecha-nacimiento']) || empty($_POST['email'])) {
        $_SESSION['mensaje_error'] = "Todos los campos son obligatorios.";
        header("Location: modificar_perfil.php");
        exit();
    }

    // Validar que el nombre solo contenga letras.
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $_POST['nombre'])) {
        $_SESSION['mensaje_error'] = "El nombre solo puede contener letras.";
        header("Location: modificar_perfil.php");
        exit();
    }

    // Validar que el apellido solo contenga letras.
    if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $_POST['apellido'])) {
        $_SESSION['mensaje_error'] = "El apellido solo puede contener letras.";
        header("Location: modificar_perfil.php");
        exit();
    }

    // Validar que sea mayor de 18 años.
    $fecha_actual = new DateTime();
    $edad = $fecha_actual->diff(new DateTime($_POST['fecha-nacimiento']))->y;
    if ($edad < 18) {
        $_SESSION['mensaje_error'] = "Debes ser mayor de 18 años para registrarte.";
        header("Location: modificar_perfil.php");
        exit();
    }

    // Validar formato del correo electrónico.
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['mensaje_error'] = "El correo electrónico no tiene un formato válido.";
        header("Location: modificar_perfil.php");
        exit();
    }

    // Obtener datos del formulario.
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $fecha_nacimiento = $_POST['fecha-nacimiento'];
    $email = $_POST['email'];

    // Abrir la conexión a la base de datos.
    include $_SERVER['DOCUMENT_ROOT'] . '/connection.php';

    // Validar si la contraseña no está vacía.
    if (!empty($_POST['contrasena']) && !empty($_POST['repetir-contrasena'])) {

        // Recuperar contraseña del formulario.
        $contrasena = $_POST['contrasena'];
        $repetir_contrasena = $_POST['repetir-contrasena'];

        // Validar que las contraseñas coincidan.
        if ($contrasena !== $repetir_contrasena) {
            $_SESSION['mensaje_error'] = "Las contraseñas no coinciden.";
            header("Location: modificar_perfil.php");
            exit();
        }

        // Validar la longitud de la contraseña.
        if (strlen($contrasena) < 8) {
            $_SESSION['mensaje_error'] = "La contraseña debe tener al menos 8 caracteres.";
            header("Location: modificar_perfil.php");
            exit();
        }

        // Prepared statement.
        $stmt = $mysqli->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, fecha_nacimiento = ?, email = ?, contrasena = ? WHERE dni = ?");
        $stmt->bind_param("ssssss", $nombre, $apellido, $fecha_nacimiento, $email, $contrasena, $dni);
        $stmt->execute();
        if ($mysqli->affected_rows > 0) $_SESSION['mensaje_exito'] = "Se ha modificado el perfil con éxito.";
        else $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
    } else {

        // Prepared statement.
        $stmt = $mysqli->prepare("UPDATE usuarios SET nombre = ?, apellido = ?, fecha_nacimiento = ?, email = ? WHERE dni = ?");
        $stmt->bind_param("sssss", $nombre, $apellido, $fecha_nacimiento, $email, $dni);
        $stmt->execute();
        if ($mysqli->affected_rows > 0) $_SESSION['mensaje_exito'] = "Se ha modificado el perfil con éxito.";
        else $_SESSION['mensaje_error'] = "Se ha producido un error inesperado.";
    }

    // Cerrar la conexión a la base de datos.
    $mysqli->close();
} else {

    // Establecer mensaje de error.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
}

// Redireccionar.
header("Location: modificar_perfil.php");
exit();
