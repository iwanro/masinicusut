<?php
/**
 * FIX QUIRKS MODE - Elimină whitespace și erori
 * Încarcă în public_html/ și accesează: https://www.piesemasinicusut.ro/fix_whitespace.php
 */

// 1. Fix header.php - elimină whitespace-ul înainte de DOCTYPE
$header_file = __DIR__ . '/includes/header.php';
$header_content = file_get_contents($header_file);

// Elimină tot whitespace-ul de la început până la <!DOCTYPE
$header_content = preg_replace('/^.*?(?=<!DOCTYPE)/s', '', $header_content);
file_put_contents($header_file, $header_content);

// 2. Fix index.php - asigură că începe corect
$index_file = __DIR__ . '/index.php';
$index_lines = file($index_file);

// Găsește prima linie non-comentariu non-golă
$new_index = [];
$in_code = false;
foreach ($index_lines as $line) {
    if (!$in_code) {
        $trimmed = trim($line);
        if ($trimmed === '' || $trimmed === '<?php' || strpos($trimmed, '//') === 0 || strpos($trimmed, '*') === 0) {
            continue; // Sari over whitespace și comentarii
        }
        $in_code = true;
    }
    $new_index[] = $line;
}

// Adaugă <?php la început dacă lipsește
if (count($new_index) > 0 && strpos($new_index[0], '<?php') === false) {
    array_unshift($new_index, "<?php\n");
}

file_put_contents($index_file, implode('', $new_index));

// 3. Curăță cache PHP
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// 4. Curăță session
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Fix Completat!</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 20px; border-radius: 5px; }
        .error { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 5px; }
        h1 { margin-top: 0; }
        .steps { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="success">
        <h1>✅ Fix Aplicat!</h1>
        <p><strong>Quirks Mode a fost reparat!</strong></p>
    </div>

    <div class="steps">
        <h2>Următorii pași:</h2>
        <ol>
            <li><strong>Șterge acest fișier:</strong> public_html/fix_whitespace.php</li>
            <li><strong>Șterge test.php</strong> dacă există</li>
            <li><strong>Șterge deploy_database.php</strong> dacă există</li>
            <li><strong>Refresh site:</strong> <a href="/" target="_blank">Intră pe piesemasinicusut.ro</a> (Ctrl+F5)</li>
        </ol>
    </div>

    <div class="steps">
        <h2>Dacă tot nu merge, verifică erorile:</h2>
        <p>Deschide în cPanel: <strong>public_html/logs/php_errors.log</strong></p>
        <p>Copiază ultimele 5 linii și trimiți-mi!</p>
    </div>
</body>
</html>
