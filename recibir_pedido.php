<?php
// FLUJO 6: CLIENTE RECIBE PEDIDO
require_once 'verifica_login.php';
require_once 'conexion.php';

verificar_rol(['CLIENTE']);

echo "=== FLUJO 6: RECEPCIÓN DE PEDIDO ===\n";
echo "Cliente: " . $_SESSION['nombre'] . "\n\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = $_POST['pedido_id'];
    $calificacion = $_POST['calificacion'] ?? '';
    
    try {
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'COMPLETADO', observaciones = ? WHERE id = ? AND cliente_id = ?");
        $stmt->execute([$calificacion, $pedido_id, $_SESSION['usuario_id']]);
        
        registrar_workflow($pedido_id, 6, 'PEDIDO_COMPLETADO', 'Cliente confirmó recepción: ' . $calificacion);
        
        echo "✓ PEDIDO RECIBIDO Y COMPLETADO\n";
        echo "Gracias por tu compra. El workflow ha finalizado exitosamente.\n";
        
    } catch(PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

// Mostrar pedidos enviados al cliente
$stmt = $pdo->prepare("
    SELECT id, fecha, total, estado
    FROM pedidos 
    WHERE cliente_id = ? AND estado = 'ENVIADO'
    ORDER BY fecha DESC
");
$stmt->execute([$_SESSION['usuario_id']]);
$pedidos = $stmt->fetchAll();

if (empty($pedidos)) {
    echo "No tienes pedidos en tránsito\n";
} else {
    echo "<h3>Pedidos Enviados (Confirma Recepción):</h3>\n";
    
    foreach($pedidos as $pedido) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>\n";
        echo "<strong>Pedido #{$pedido['id']}</strong><br>\n";
        echo "Fecha: {$pedido['fecha']}<br>\n";
        echo "Total: \${$pedido['total']}<br>\n";
        
        echo "<form method='POST' style='margin-top: 10px;'>\n";
        echo "<input type='hidden' name='pedido_id' value='{$pedido['id']}'>\n";
        echo "Califica tu experiencia: <textarea name='calificacion' rows='2' cols='40' placeholder='Comentarios opcionales'></textarea><br>\n";
        echo "<input type='submit' value='Confirmar Recepción' style='background: green; color: white;'>\n";
        echo "</form>\n";
        echo "</div>\n";
    }
}

echo "<br><a href='index.php'>Volver al menú</a>\n";
?>