<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$inventoryFile = 'inventory.json';

function getInventory() {
    global $inventoryFile;
    if (file_exists($inventoryFile)) {
        return json_decode(file_get_contents($inventoryFile), true);
    }
    return [];
}

function saveInventory($data) {
    global $inventoryFile;
    file_put_contents($inventoryFile, json_encode($data, JSON_PRETTY_PRINT));
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['draw'])) {
            // DataTables server-side processing
            $draw = intval($_GET['draw']);
            $start = intval($_GET['start']);
            $length = intval($_GET['length']);
            $search = $_GET['search']['value'] ?? '';
            $orderColumn = intval($_GET['order'][0]['column']);
            $orderDir = $_GET['order'][0]['dir'];

            $inventory = getInventory();
            $columns = ['artikelnummer', 'produktbezeichnung', 'ean', 'menge', 'preis'];

            // Filter by search
            if (!empty($search)) {
                $inventory = array_filter($inventory, function($item) use ($search, $columns) {
                    foreach ($columns as $col) {
                        if (stripos($item[$col] ?? '', $search) !== false) {
                            return true;
                        }
                    }
                    return false;
                });
            }

            // Sort
            if (isset($columns[$orderColumn])) {
                $sortKey = $columns[$orderColumn];
                usort($inventory, function($a, $b) use ($sortKey, $orderDir) {
                    $valA = $a[$sortKey] ?? '';
                    $valB = $b[$sortKey] ?? '';
                    if ($orderDir === 'asc') {
                        return strcmp($valA, $valB);
                    } else {
                        return strcmp($valB, $valA);
                    }
                });
            }

            // Paginate
            $totalRecords = count($inventory);
            $inventory = array_slice($inventory, $start, $length);

            echo json_encode([
                'draw' => $draw,
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => array_values($inventory)
            ]);
        } else {
            echo json_encode(getInventory());
        }
        break;
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['action']) && $input['action'] === 'reset') {
            saveInventory([]);
            echo json_encode(['status' => 'reset']);
            break;
        }
        $inventory = getInventory();
        if (is_array($input) && isset($input[0])) {
            // Batch import
            foreach ($input as $item) {
                $inventory[] = $item;
            }
        } else {
            // Single item
            $inventory[] = $input;
        }
        saveInventory($inventory);
        echo json_encode(['status' => 'success']);
        break;
    case 'PUT':
        $input = json_decode(file_get_contents('php://input'), true);
        $inventory = getInventory();
        foreach ($inventory as &$item) {
            if ($item['artikelnummer'] === $input['artikelnummer']) {
                $oldAmount = $item['menge'];
                if (isset($input['add']) && $input['add']) {
                    $item['menge'] += $input['menge'];
                } else {
                    $item['menge'] = $input['menge'];
                }
                $newAmount = $item['menge'];
                $change = $newAmount - $oldAmount;
                $type = isset($input['add']) ? 'add' : 'overwrite';
                $item['history'][] = [
                    'timestamp' => date('c'),
                    'change' => $change,
                    'type' => $type,
                    'newAmount' => $newAmount
                ];
                break;
            }
        }
        saveInventory($inventory);
        echo json_encode(['status' => 'success']);
        break;
    case 'DELETE':
        $input = json_decode(file_get_contents('php://input'), true);
        $inventory = getInventory();
        $inventory = array_filter($inventory, function($item) use ($input) {
            return $item['artikelnummer'] !== $input['artikelnummer'];
        });
        saveInventory($inventory);
        echo json_encode(['status' => 'deleted']);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}
?>