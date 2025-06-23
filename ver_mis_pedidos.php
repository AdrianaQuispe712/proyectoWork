<?php
// VER PEDIDOS DEL CLIENTE
require_once 'verifica_login.php';
require_once 'conexion.php';

verificar_rol(['CLIENTE']);

echo "=== MIS PEDIDOS ===\n";
echo "Cliente: " . $_SESSION['nombre'] . "\n\n";

$stmt = $pdo->prepare("
    SELECT id, fecha, estado, total, observaciones
    FROM pedidos 
    WHERE cliente_id = ?
    ORDER BY fecha DESC
");
$stmt->execute([$_SESSION['usuario_id']]);
$pedidos = $stmt->fetchAll();

if (empty($pedidos)) {
    echo "No tienes pedidos registrados\n";
} else {
    foreach($pedidos as $pedido) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>\n";
        echo "<strong>Pedido #{$pedido['id']}</strong><br>\n";
        echo "Fecha: {$pedido['fecha']}<br>\n";
        echo "Estado: <strong>{$pedido['estado']}</strong><br>\n";
        echo "Total: \${$pedido['total']}<br>\n";
        
        if ($pedido['observaciones']) {
            echo "Observaciones: {$pedido['observaciones']}<br>\n";
        }
        
        // Mostrar progreso del workflow
        $stmt = $pdo->prepare("SELECT flujo, estado, fecha, observaciones FROM workflow_logs WHERE pedido_id = ? ORDER BY fecha");
        $stmt->execute([$pedido['id']]);
        $logs = $stmt->fetchAll();
        
        if (!empty($logs)) {
            echo "<strong>Historial del Pedido:</strong><br>\n";
            foreach($logs as $log) {
                echo "- Flujo {$log['flujo']}: {$log['estado']} ({$log['fecha']})<br>\n";
                if ($log['observaciones']) {
                    echo "  Notas: {$log['observaciones']}<br>\n";
                }
            }
        }
        
        echo "</div>\n";
    }
}

echo "<br><a href='index.php'>Volver al menú</a>\n";
?>