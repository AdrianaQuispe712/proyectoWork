<?php
// SCRIPT PARA CREAR BASE DE DATOS Y TABLAS
require_once 'conexion.php';

echo "=== CREANDO ESTRUCTURA DE BASE DE DATOS ===\n\n";

// Tabla usuarios
$sql_usuarios = "
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol VARCHAR(20) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    activo BOOLEAN DEFAULT true,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// Tabla productos
$sql_productos = "
CREATE TABLE IF NOT EXISTS productos (
    id SERIAL PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INTEGER DEFAULT 0,
    stock_minimo INTEGER DEFAULT 5,
    activo BOOLEAN DEFAULT true
)";

// Tabla pedidos
$sql_pedidos = "
CREATE TABLE IF NOT EXISTS pedidos (
    id SERIAL PRIMARY KEY,
    cliente_id INTEGER REFERENCES usuarios(id),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(30) DEFAULT 'PENDIENTE',
    total DECIMAL(10,2) DEFAULT 0,
    observaciones TEXT
)";

// Tabla detalle_pedidos
$sql_detalle = "
CREATE TABLE IF NOT EXISTS detalle_pedidos (
    id SERIAL PRIMARY KEY,
    pedido_id INTEGER REFERENCES pedidos(id),
    producto_id INTEGER REFERENCES productos(id),
    cantidad INTEGER NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL
)";

// Tabla ordenes_compra
$sql_ordenes = "
CREATE TABLE IF NOT EXISTS ordenes_compra (
    id SERIAL PRIMARY KEY,
    encargado_id INTEGER REFERENCES usuarios(id),
    proveedor VARCHAR(100) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(30) DEFAULT 'PENDIENTE',
    total DECIMAL(10,2) DEFAULT 0
)";

// Tabla workflow_logs
$sql_workflow = "
CREATE TABLE IF NOT EXISTS workflow_logs (
    id SERIAL PRIMARY KEY,
    pedido_id INTEGER REFERENCES pedidos(id),
    flujo INTEGER NOT NULL,
    estado VARCHAR(50) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INTEGER REFERENCES usuarios(id),
    observaciones TEXT
)";

try {
    $pdo->exec($sql_usuarios);
    echo "✓ Tabla usuarios creada\n";
    
    $pdo->exec($sql_productos);
    echo "✓ Tabla productos creada\n";
    
    $pdo->exec($sql_pedidos);
    echo "✓ Tabla pedidos creada\n";
    
    $pdo->exec($sql_detalle);
    echo "✓ Tabla detalle_pedidos creada\n";
    
    $pdo->exec($sql_ordenes);
    echo "✓ Tabla ordenes_compra creada\n";
    
    $pdo->exec($sql_workflow);
    echo "✓ Tabla workflow_logs creada\n";
    
    // Insertar datos iniciales
    echo "\n=== INSERTANDO DATOS INICIALES ===\n";
    
    // Usuarios con roles
    $usuarios = [
        ['cliente1', '123456', 'CLIENTE', 'Juan Pérez'],
        ['cliente2', '123456', 'CLIENTE', 'María García'],
        ['almacenero', '123456', 'ALMACENERO', 'Pedro López'],
        ['gerente', '123456', 'GERENTE', 'Ana Rodríguez'],
        ['compras', '123456', 'ENCARGADO_COMPRAS', 'Carlos Martín']
    ];
    
    foreach($usuarios as $user) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (usuario, password, rol, nombre) VALUES (?, ?, ?, ?) ON CONFLICT (usuario) DO NOTHING");
        $stmt->execute([$user[0], password_hash($user[1], PASSWORD_DEFAULT), $user[2], $user[3]]);
        echo "✓ Usuario: {$user[0]} - Rol: {$user[2]} - Password: {$user[1]}\n";
    }
    
    // Productos
    $productos = [
        ['Laptop Dell', 1500.00, 10],
        ['Mouse Logitech', 25.50, 50],
        ['Teclado Mecánico', 85.00, 20],
        ['Monitor 24"', 300.00, 8],
        ['Auriculares', 45.00, 30]
    ];
    
    foreach($productos as $prod) {
        $stmt = $pdo->prepare("INSERT INTO productos (nombre, precio, stock) VALUES (?, ?, ?) ON CONFLICT DO NOTHING");
        $stmt->execute($prod);
        echo "✓ Producto: {$prod[0]} - Stock: {$prod[2]}\n";
    }
    
    echo "\n=== BASE DE DATOS CONFIGURADA CORRECTAMENTE ===\n";
    echo "USUARIOS Y CONTRASEÑAS:\n";
    echo "- cliente1 / 123456 (CLIENTE)\n";
    echo "- cliente2 / 123456 (CLIENTE)\n";
    echo "- almacenero / 123456 (ALMACENERO)\n";
    echo "- gerente / 123456 (GERENTE)\n";
    echo "- compras / 123456 (ENCARGADO_COMPRAS)\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>