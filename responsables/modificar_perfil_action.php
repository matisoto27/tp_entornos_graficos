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
} else {
    $codigo = $_SESSION['codigo'];
}

// Validar si se reciben datos por POST.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validar que los campos obligatorios no esten vacios.
    if (empty($_POST['nombre']) || empty($_POST['apellido']) || empty($_POST['email'])) {
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

    // Validar formato del correo electrónico.
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $_SESSION['mensaje_error'] = "El correo electrónico no tiene un formato válido.";
        header("Location: modificar_perfil.php");
        exit();
    }

    // Obtener datos del formulario.
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
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
        $stmt = $mysqli->prepare("UPDATE responsables SET nombre = ?, apellido = ?, email = ?, contrasena = ? WHERE codigo = ?");
        $stmt->bind_param("sssss", $nombre, $apellido, $email, $contrasena, $codigo);
        $stmt->execute();

        // Establecer mensaje de éxito.
        $_SESSION['mensaje_exito'] = "Se ha modificado el perfil con éxito.";
    } else {

        // Prepared statement.
        $stmt = $mysqli->prepare("UPDATE responsables SET nombre = ?, apellido = ?, email = ? WHERE codigo = ?");
        $stmt->bind_param("ssss", $nombre, $apellido, $email, $codigo);
        $stmt->execute();

        // Establecer mensaje de éxito.
        $_SESSION['mensaje_exito'] = "Se ha modificado el perfil con éxito.";
    }

    // Actualizar nombre y apellido de la sesión.
    unset($_SESSION['nombre']);
    unset($_SESSION['apellido']);
    $_SESSION['nombre'] = $nombre;
    $_SESSION['apellido'] = $apellido;

    // Cerrar la conexión a la base de datos, redireccionar y finalizar el script actual.
    $mysqli->close();
    header("Location: menu_principal.php");
    exit();
} else {

    // Establecer mensaje de error, redireccionar y finalizar el script actual.
    $_SESSION['mensaje_error'] = "Acceso no autorizado.";
    header("Location: menu_principal.php");
    exit();
}
