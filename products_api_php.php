<?php
header('Content-Type: application/json');
require_once '../../config/database.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetProducts();
            break;
        case 'POST':
            handleCreateProduct();
            break;
        default:
            http_response_code(405);
            echo json_encode(['message' => 'Metodo non consentito']);
            break;
    }
    
} catch (Exception $e) {
    error_log('Errore API prodotti: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['message' => 'Errore interno del server']);
}

function handleGetProducts() {
    global $pdo;
    
    $categoria = $_GET['categoria'] ?? '';
    $marca = $_GET['marca'] ?? '';
    $search = $_GET['search'] ?? '';
    $limit = (int)($_GET['limit'] ?? 20); // ERRORE 1: Mancata validazione di min/max
    $offset = (int)($_GET['offset'] ?? 0); // ERRORE 1: Mancata validazione di min/max
    
    $whereConditions = ['attivo = 1'];
    $params = [];
    
    if ($categoria) {
        $whereConditions[] = 'categoria = ?';
        $params[] = $categoria;
    }
    
    if ($marca) {
        $whereConditions[] = 'marca = ?';
        $params[] = $marca;
    }
    
    if ($search) {
        $whereConditions[] = '(nome LIKE ? OR descrizione LIKE ?)';
        $params[] = '%' . $search . '%';
        $params[] = '%' . $search . '%';
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Query per contare totali
    $countStmt = $pdo->prepare("SELECT COUNT(*) as total FROM products WHERE $whereClause");
    $countStmt->execute($params);
    $totalCount = $countStmt->fetch()['total'];
    
    // Query per prodotti
    $stmt = $pdo->prepare("
        SELECT id, nome, categoria, marca, descrizione, prezzo, taglie, colori, immagini, caratteristiche, dimensioni2D
        FROM products 
        WHERE $whereClause 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?
    ");
    
    $params[] = $limit;
    $params[] = $offset; // ERRORE 2: L'ordine dei parametri è sbagliato
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Decodifica campi JSON
    foreach ($products as &$product) {
        $product['taglie'] = json_decode($product['taglie'], true); // ERRORE 3: Gestione incompleta del JSON
        $product['colori'] = json_decode($product['colori'], true); // ERRORE 3: Gestione incompleta del JSON
        $product['immagini'] = json_decode($product['immagini'], true); // ERRORE 3: Gestione incompleta del JSON
        $product['caratteristiche'] = json_decode($product['caratteristiche'], true); // ERRORE 3: Gestione incompleta del JSON
        $product['dimensioni2D'] = json_decode($product['dimensioni2D'], true); // ERRORE 3: Gestione incompleta del JSON
    }
    
    echo json_encode([
        'products' => $products,
        'pagination' => [
            'total' => $totalCount,
            'limit' => $limit,
            'offset' => $offset,
            'hasMore' => ($offset + $limit) < $totalCount
        ]
    ]);
}

function handleCreateProduct() {
    global $pdo;
    require_once '../../config/auth.php';
    
    // Verifica autenticazione admin (implementa la tua logica)
    $user = requireAuth();
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    $requiredFields = ['nome', 'categoria', 'marca', 'descrizione', 'prezzo'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) { // ERRORE 4: Verifica con !isset anziché empty() per campi potenzialmente vuoti ma validi (es. stringa vuota)
            http_response_code(400);
            echo json_encode(['message' => "Campo '$field' richiesto"]);
            return;
        }
    }
    
    $validCategorie = ['magliette', 'camicie', 'pantaloni', 'gonne', 'giacche', 'vestiti', 'accessori'];
    if (!in_array($input['categoria'], $validCategorie)) {
        http_response_code(400);
        echo json_encode(['message' => 'Categoria non valida']);
        return;
    }
    
    if (!is_numeric($input['prezzo']) || $input['prezzo'] < 0) {
        http_response_code(400);
        echo json_encode(['message' => 'Prezzo non valido']);
        return;
    }
    
    $stmt = $pdo->prepare("
        INSERT INTO products (nome, categoria, marca, descrizione, prezzo, taglie, colori, immagini, caratteristiche, dimensioni2D) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $input['nome'],
        $input['categoria'],
        $input['marca'],
        $input['descrizione'],
        $input['prezzo'],
        json_encode($input['taglie'] ?? []),
        json_encode($input['colori'] ?? []),
        json_encode($input['immagini'] ?? []),
        json_encode($input['caratteristiche'] ?? []),
        json_encode($input['dimensioni2D'] ?? [])
    ]);
    
    $productId = $pdo->lastInsertId();
    
    http_response_code(201);
    echo json_encode([
        'message' => 'Prodotto creato con successo',
        'product_id' => $productId
    ]);
}
?>
