<?php
// FLUJO 1: AUTENTICACIÓN DE USUARIOS
session_start();
require_once 'conexion.php';

echo "=== SISTEMA DE WORKFLOW - TIENDA ===\n";
echo "=== FLUJO 1: AUTENTICACIÓN ===\n\n";

if ($_SESSION['usuario_id'] ?? false) {
    echo "Ya tienes una sesión activa. Redirigiendo al menú...\n";
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($usuario) || empty($password)) {
        echo "ERROR: Usuario y contraseña son obligatorios\n";
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, usuario, password, rol, nombre, activo FROM usuarios WHERE usuario = ? AND activo = true");
        $stmt->execute([$usuario]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario'] = $user['usuario'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['nombre'] = $user['nombre'];
            
            echo "✓ Login exitoso\n";
            echo "Bienvenido: {$user['nombre']}\n";
            echo "Rol: {$user['rol']}\n";
            
            header("Location: index.php");
            exit;
        } else {
            echo "ERROR: Credenciales inválidas\n";
        }
    } catch(PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}
?>

<!DOCTYPE html>
<html>
<head><title>Login - Workflow Tienda</title></head>
<body>
<h2>INICIAR SESIÓN</h2>
<form method="POST">
    Usuario: <input type="text" name="usuario" required><br><br>
    Contraseña: <input type="password" name="password" required><br><br>
    <input type="submit" value="Ingresar">
</form>

<h3>USUARIOS DISPONIBLES:</h3>
<ul>
    <li><strong>cliente1</strong> / 123456 (CLIENTE)</li>
    <li><strong>cliente2</strong> / 123456 (CLIENTE)</li>
    <li><strong>almacenero</strong> / 123456 (ALMACENERO)</li>
    <li><strong>gerente</strong> / 123456 (GERENTE)</li>
    <li><strong>compras</strong> / 123456 (ENCARGADO_COMPRAS)</li>
</ul>
</body>
</html>