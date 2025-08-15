<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafe_app";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => "Connection failed: " . $e->getMessage()]);
    exit;
}

// Get date filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Initialize response
$response = [
    'success' => true,
    'orders_by_date' => [],
    'highest_sale_product' => null,
    'category_analysis' => [],
    'average_order_value' => 0,
    'debug' => []
];

// Query 1: Orders by Date
try {
    $query = "SELECT DATE(date) as order_date, COUNT(*) as order_count 
              FROM orders";
    $params = [];
    if ($start_date && $end_date) {
        $query .= " WHERE date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }
    $query .= " GROUP BY DATE(date) ORDER BY order_date";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $response['orders_by_date'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['debug']['orders_by_date_query'] = $query;
} catch(PDOException $e) {
    $response['success'] = false;
    $response['message'] = "Error fetching orders by date: " . $e->getMessage();
    echo json_encode($response);
    exit;
}

// Query 2: Highest Selling Product
try {
    $query = "SELECT i.item_name, SUM(i.quantity) as total_quantity 
              FROM orders o 
              CROSS JOIN JSON_TABLE(o.items, '$[*]' COLUMNS (
                  item_name VARCHAR(255) PATH '$.name',
                  quantity INT PATH '$.quantity'
              )) i";
    $params = [];
    if ($start_date && $end_date) {
        $query .= " WHERE o.date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }
    $query .= " GROUP BY i.item_name ORDER BY total_quantity DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['highest_sale_product'] = $result ? $result : null;
    $response['debug']['highest_sale_product_query'] = $query;
} catch(PDOException $e) {
    $response['success'] = false;
    $response['message'] = "Error fetching highest selling product: " . $e->getMessage();
    echo json_encode($response);
    exit;
}

// Query 3: Food Category Analysis
try {
    $query = "
        SELECT 
            COALESCE(m.category, 'Unknown') as category, 
            i.item_name, 
            SUM(i.quantity) as item_count, 
            SUM(i.quantity * COALESCE(m.price, 0)) as total_revenue 
        FROM orders o 
        CROSS JOIN JSON_TABLE(o.items, '$[*]' COLUMNS (
            item_name VARCHAR(255) PATH '$.name',
            quantity INT PATH '$.quantity'
        )) i
        LEFT JOIN menu_items m ON LOWER(i.item_name) = LOWER(m.item_name)";
    $params = [];
    if ($start_date && $end_date) {
        $query .= " WHERE o.date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }
    $query .= " GROUP BY i.item_name, m.category ORDER BY category, i.item_name";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $response['category_analysis'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $response['debug']['category_analysis_query'] = $query;
    // Debug: List unmatched items
    $query_unmatched = "
        SELECT DISTINCT i.item_name 
        FROM orders o 
        CROSS JOIN JSON_TABLE(o.items, '$[*]' COLUMNS (
            item_name VARCHAR(255) PATH '$.name'
        )) i
        LEFT JOIN menu_items m ON LOWER(i.item_name) = LOWER(m.item_name)
        WHERE m.item_name IS NULL";
    $stmt_unmatched = $conn->prepare($query_unmatched);
    $stmt_unmatched->execute();
    $response['debug']['unmatched_items'] = $stmt_unmatched->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $response['success'] = false;
    $response['message'] = "Error fetching category analysis: " . $e->getMessage();
    echo json_encode($response);
    exit;
}

// Query 4: Average Order Value
try {
    $query = "SELECT AVG(total) as average_order_value 
              FROM orders";
    $params = [];
    if ($start_date && $end_date) {
        $query .= " WHERE date BETWEEN :start_date AND :end_date";
        $params[':start_date'] = $start_date;
        $params[':end_date'] = $end_date;
    }
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response['average_order_value'] = $result['average_order_value'] ? floatval($result['average_order_value']) : 0;
    $response['debug']['average_order_value_query'] = $query;
} catch(PDOException $e) {
    $response['success'] = false;
    $response['message'] = "Error fetching average order value: " . $e->getMessage();
    echo json_encode($response);
    exit;
}

// Output response
echo json_encode($response);

// Close connection
$conn = null;
?>