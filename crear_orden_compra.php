<?php
// FLUJO 7: ENCARGADO CREA ORDEN DE COMPRA
require_once 'verifica_login.php';
require_once 'conexion.php';

verificar_rol(['ENCARGADO_COMPRAS']);

echo "=== FLUJO 7: CREAR ORDEN DE COMPRA ===\n";
echo "Encargado: " . $_SESSION['nombre'] . "\n\n";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $proveedor = $_POST['proveedor'];
    $productos = $_POST['productos'] ?? [];
    $cantidades = $_POST['cantidades'] ?? [];
    
    try {
        // Crear orden de compra
        $stmt = $pdo->prepare("INSERT INTO ordenes_compra (encargado_id, proveedor, estado) VALUES (?, ?, 'PENDIENTE') RETURNING id");
        $stmt->execute([$_SESSION['usuario_id'], $proveedor]);
        $orden_id = $stmt->fetch()['id'];
        
        echo "✓ ORDEN DE COMPRA CREADA\n";
        echo "Número de Orden: $orden_id\n";
        echo "Proveedor: $proveedor\n";
        echo "La orden pasará al FLUJO 8 cuando se reciban los productos\n";
        
    } catch(PDOException $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    
} else {
    // Mostrar productos con stock bajo
    $stmt = $pdo->query("SELECT id, nombre, stock, stock_minimo FROM productos WHERE stock <= stock_minimo");
    $productos_bajo_stock = $stmt->fetchAll();
    
    if (!empty($productos_bajo_stock)) {
        echo "<h3>Productos con Stock Bajo:</h3>\n";
        foreach($productos_bajo_stock as $prod) {
            echo "- {$prod['nombre']}: Stock actual {$prod['stock']}, Mínimo {$prod['stock_minimo']}<br>\n";
        }
        echo "<br>\n";
    }
    
    echo "<form method='POST'>\n";
    echo "Proveedor: <input type='text' name='proveedor' required><br><br>\n";
    echo "<input type='submit' value='Crear Orden de Compra'>\n";
    echo "</form>\n";
}

echo "<br><a href='index.php'>Volver al menú</a>\n";
?>