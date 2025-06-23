<?php
// VER FLUJOS DE TRABAJO COMPLETOS
require_once 'verifica_login.php';
require_once 'conexion.php';

verificar_rol(['GERENTE']);

echo "=== FLUJOS DE TRABAJO DEL SISTEMA ===\n\n";

echo "<h2>DESCRIPCIÓN DE FLUJOS:</h2>\n";
echo "<strong>FLUJO 1:</strong> Autenticación de Usuarios<br>\n";
echo "<strong>FLUJO 2:</strong> Cliente Realiza Pedido<br>\n";
echo "<strong>FLUJO 3:</strong> Almacenero Verifica Existencias<br>\n";
echo "<strong>FLUJO 4:</strong> Gerente Aprueba/Rechaza Pedido<br>\n";
echo "<strong>FLUJO 5:</strong> Almacenero Envía Pedido<br>\n";
echo "<strong>FLUJO 6:</strong> Cliente Recibe Pedido<br>\n";
echo "<strong>FLUJO 7:</strong> Encargado Crea Orden de Compra<br>\n";
echo "<strong>FLUJO 8:</strong> Recepción de Compra<br>\n";

echo "<br><h2>ACTIVIDAD RECIENTE:</h2>\n";

$stmt = $pdo->query("
    SELECT wl.*, u.nombre as usuario_nombre, p.id as pedido_num
    FROM workflow_logs wl
    JOIN usuarios u ON wl.usuario_id = u.id
    JOIN pedidos p ON wl.pedido_id = p.id
    ORDER BY wl.fecha DESC
    LIMIT 20
");
$logs = $stmt->fetchAll();

foreach($logs as $log) {
    echo "<div style='border-left: 3px solid #007cba; padding: 5px; margin: 5px;'>\n";
    echo "<strong>Flujo {$log['flujo']}</strong> - {$log['estado']}<br>\n";
    echo "Pedido #{$log['pedido_num']} | Usuario: {$log['usuario_nombre']}<br>\n";
    echo "Fecha: {$log['fecha']}<br>\n";
    if ($log['observaciones']) {
        echo "Notas: {$log['observaciones']}<br>\n";
    }
    echo "</div>\n";
}

echo "<br><a href='index.php'>Volver al menú</a>\n";
?>