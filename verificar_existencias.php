<?php
// FLUJO 3: ALMACENERO VERIFICA EXISTENCIAS
require_once 'verifica_login.php';
require_once 'conexion.php';

verificar_rol(['ALMACENERO']);

echo "=== FLUJO 3: VERIFICAR EXISTENCIAS ===\n";
echo "Almacenero: " . $_SESSION['nombre'] . "\n\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedido_id = $_POST['pedido_id'];
    $accion = $_POST['accion'];
    
    try {
        if ($accion === 'aprobar') {
            $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'EXISTENCIAS_OK' WHERE id = ?");
            $stmt->execute([$pedido_id]);
            
            registrar_workflow($pedido_id, 3, 'EXISTENCIAS_VERIFICADAS', 'Stock suficiente confirmado');
            
            echo "✓ EXISTENCIAS CONFIRMADAS\n";
            echo "El pedido $pedido_id pasará al FLUJO 4 (Aprobación Gerencial)\n";
            
        } else {
            $stmt = $pdo->prepare("UPDATE pedidos SET estado = 'SIN_STOCK' WHERE id = ?");
            $stmt->execute([$pedido_id]);
            
            registrar_workflow($pedido_id, 3, 'SIN_STOCK', 'Stock insuficiente - Requiere orden de compra');
            
            echo "⚠ SIN STOCK SUFICIENTE\n";
            echo "El pedido $pedido_id requerirá FLUJO 7 (Orden de Compra)\n";
        }
        
    } catch(PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
}

// Mostrar pedidos pendientes de verificación
$stmt = $pdo->query("
    SELECT p.id, p.fecha, u.nombre as cliente, p.total, p.estado
    FROM pedidos p 
    JOIN usuarios u ON p.cliente_id = u.id 
    WHERE p.estado IN ('PENDIENTE') 
    ORDER BY p.fecha
");
$pedidos = $stmt->fetchAll();

if (empty($pedidos)) {
    echo "No hay pedidos pendientes de verificación\n";
} else {
    echo "<h3>Pedidos Pendientes de Verificación:</h3>\n";
    
    foreach($pedidos as $pedido) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px;'>\n";
        echo "<strong>Pedido #{$pedido['id']}</strong><br>\n";
        echo "Cliente: {$pedido['cliente']}<br>\n";
        echo "Fecha: {$pedido['fecha']}<br>\n";
        echo "Total: \${$pedido['total']}<br>\n";
        
        // Mostrar detalle del pedido
        $stmt = $pdo->prepare("
            SELECT pr.nombre, dp.cantidad, dp.precio, pr.stock
            FROM detalle_pedidos dp
            JOIN productos pr ON dp.producto_id = pr.id
            WHERE dp.pedido_id = ?
        ");
        $stmt->execute([$pedido['id']]);
        $detalles = $stmt->fetchAll();
        
        echo "<strong>Productos:</strong><br>\n";
        $stock_ok = true;
        
        foreach($detalles as $detalle) {
            $disponible = $detalle['stock'] >= $detalle['cantidad'] ? "✓" : "✗";
            if ($detalle['stock'] < $detalle['cantidad']) $stock_ok = false;
            
            echo "- {$detalle['nombre']}: {$detalle['cantidad']} unid. (Stock: {$detalle['stock']}) $disponible<br>\n";
        }
        
        echo "<form method='POST' style='margin-top: 10px;'>\n";
        echo "<input type='hidden' name='pedido_id' value='{$pedido['id']}'>\n";
        
        if ($stock_ok) {
            echo "<input type='submit' name='accion' value='aprobar' style='background: green; color: white;'>\n";
        } else {
            echo "<input type='submit' name='accion' value='rechazar' style='background: red; color: white;'>\n";
            echo "<small>Stock insuficiente</small>\n";
        }
        
        echo "</form>\n";
        echo "</div>\n";
    }
}

echo "<br><a href='index.php'>Volver al menú</a>\n";
?>