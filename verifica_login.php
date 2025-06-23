<?php
// VERIFICACIÓN DE SESIÓN ACTIVA
session_start();

if (!isset($_SESSION['usuario_id'])) {
    echo "ERROR: Debes iniciar sesión primero\n";
    echo "<a href='login.php'>Ir al Login</a>\n";
    exit;
}

// Función para verificar rol
function verificar_rol($roles_permitidos) {
    if (!in_array($_SESSION['rol'], $roles_permitidos)) {
        echo "ERROR: No tienes permisos para acceder a esta sección\n";
        echo "Tu rol: " . $_SESSION['rol'] . "\n";
        echo "Roles permitidos: " . implode(', ', $roles_permitidos) . "\n";
        echo "<a href='index.php'>Volver al menú</a>\n";
        exit;
    }
}

// Función para registrar en workflow_logs
function registrar_workflow($pedido_id, $flujo, $estado, $observaciones = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("INSERT INTO workflow_logs (pedido_id, flujo, estado, usuario_id, observaciones) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$pedido_id, $flujo, $estado, $_SESSION['usuario_id'], $observaciones]);
    } catch(PDOException $e) {
        echo "Error al registrar workflow: " . $e->getMessage() . "\n";
    }
}
?>