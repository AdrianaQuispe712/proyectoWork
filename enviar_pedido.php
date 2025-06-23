<?php
// FLUJO 5: ALMACENERO ENVÍA PEDIDO
require_once 'verifica_login.php';
require_once 'conexion.php';

verificar_rol(['ALMACENERO']);

echo "=== FLUJO 5: ENVÍO DE PEDIDOS ===\n";
echo "Almacenero: " . $_SESSION['nombre'] . "\n\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = $_POST['pedido_id'];
    
    try {
        // Actualizar stock de productos
        $stmt = $pdo->prepare("
            SELECT dp.producto_id, dp.cantidad 
            FROM detalle_pedidos dp 
            WHERE dp.pedido_id = ?
        ");
        $stmt->execute([$pedido_id]);
        $detalles = $stmt->fetchAll();
        
        foreach($detalles as $detalle) {
            $stmt = $pdo->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$detalle['cantidad'], $detalle['producto_id']]);
        }
        
        // Actualizar estado del pedido
        $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'ENVIADO' WHERE id = ?");
        $stmt->execute([$pedido_id]);
        
        registrar_workflow($pedido_id, 5, 'PEDIDO_ENVIADO', 'Pedido despachado - Stock actualizado');
        
        echo "✓ PEDIDO ENVIADO\n";
        echo "El pedido $pedido_id está en camino al cliente (FLUJO 6)\n";
        echo "Stock actualizado correctamente\n";
        
    } catch(PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

// Mostrar pedidos aprobados para envío
$stmt = $pdo->query("
    SELECT p.id, p.fecha, u.nombre as cliente, p.total
    FROM pedidos p 
    JOIN usuarios u ON p.cliente_id = u.id 
    WHERE p.estado = 'APROBADO' 
    ORDER BY p.fecha
");
$pedidos = $stmt->fetchAll();

if (empty($pedidos)) {
    echo "No hay pedidos aprobados para envío\n";
} else {
    echo "<h3>Pedidos Listos para Envío:</h3>\n";
    
    foreach($pedidos as $pedido) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>\n";
        echo "<strong>Pedido #{$pedido['id']}</strong><br>\n";
        echo "Cliente: {$pedido['cliente']}<br>\n";
        echo "Fecha: {$pedido['fecha']}<br>\n";
        echo "Total: \${$pedido['total']}<br>\n";
        
        echo "<form method='POST' style='margin-top: 10px;'>\n";
        echo "<input type='hidden' name='pedido_id' value='{$pedido['id']}'>\n";
        echo "<input type='submit' value='Enviar Pedido' style='background: blue; color: white;'>\n";
        echo "</form>\n";
        echo "</div>\n";
    }
}

echo "<br><a href='index.php'>Volver al menú</a>\n";
?>