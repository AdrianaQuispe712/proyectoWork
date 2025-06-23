<?php
// FLUJO 4: GERENTE APRUEBA/RECHAZA PEDIDOS
require_once 'verifica_login.php';
require_once 'conexion.php';

verificar_rol(['GERENTE']);

echo "=== FLUJO 4: APROBACIÓN GERENCIAL ===\n";
echo "Gerente: " . $_SESSION['nombre'] . "\n\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = $_POST['pedido_id'];
    $decision = $_POST['decision'];
    $observaciones = $_POST['observaciones'] ?? '';
    
    try {
        if ($decision === 'aprobar') {
            $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'APROBADO', observaciones = ? WHERE id = ?");
            $stmt->execute([$observaciones, $pedido_id]);
            
            registrar_workflow($pedido_id, 4, 'PEDIDO_APROBADO', 'Aprobado por gerente: ' . $observaciones);
            
            echo "✓ PEDIDO APROBADO\n";
            echo "El pedido $pedido_id pasará al FLUJO 5 (Envío de Pedido)\n";
            
        } else {
            $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'RECHAZADO', observaciones = ? WHERE id = ?");
            $stmt->execute([$observaciones, $pedido_id]);
            
            registrar_workflow($pedido_id, 4, 'PEDIDO_RECHAZADO', 'Rechazado por gerente: ' . $observaciones);
            
            echo "✗ PEDIDO RECHAZADO\n";
            echo "Motivo: $observaciones\n";
        }
        
    } catch(PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

// Mostrar pedidos para aprobación
$stmt = $pdo->query("
    SELECT p.id, p.fecha, u.nombre as cliente, p.total, p.estado
    FROM pedidos p 
    JOIN usuarios u ON p.cliente_id = u.id 
    WHERE p.estado IN ('EXISTENCIAS_OK') 
    ORDER BY p.fecha
");
$pedidos = $stmt->fetchAll();

if (empty($pedidos)) {
    echo "No hay pedidos pendientes de aprobación\n";
} else {
    echo "<h3>Pedidos Pendientes de Aprobación:</h3>\n";
    
    foreach($pedidos as $pedido) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>\n";
        echo "<strong>Pedido #{$pedido['id']}</strong><br>\n";
        echo "Cliente: {$pedido['cliente']}<br>\n";
        echo "Fecha: {$pedido['fecha']}<br>\n";
        echo "Total: \${$pedido['total']}<br>\n";
        
        echo "<form method='POST' style='margin-top: 10px;'>\n";
        echo "<input type='hidden' name='pedido_id' value='{$pedido['id']}'>\n";
        echo "Observaciones: <textarea name='observaciones' rows='2' cols='50'></textarea><br>\n";
        echo "<input type='submit' name='decision' value='aprobar' style='background: green; color: white;'>\n";
        echo "<input type='submit' name='decision' value='rechazar' style='background: red; color: white;'>\n";
        echo "</form>\n";
        echo "</div>\n";
    }
}

echo "<br><a href='index.php'>Volver al menú</a>\n";
?>