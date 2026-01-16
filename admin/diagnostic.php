<?php
/**
 * Diagnostic Page
 * SUNDARI TOP STAR S.R.L.
 * Checks server configuration and common issues
 */

// Set error reporting for diagnostics
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering to prevent any whitespace
ob_start();

// Helper function to output test results
function testResult($name, $status, $message = '', $details = '') {
    $icon = $status === 'pass' ? '✓' : ($status === 'fail' ? '✗' : '⚠');
    $color = $status === 'pass' ? '#27ae60' : ($status === 'fail' ? '#e74c3c' : '#f39c12');
    $bgColor = $status === 'pass' ? '#d4edda' : ($status === 'fail' ? '#f8d7da' : '#fff3cd');

    echo '<tr style="background: ' . $bgColor . ';">';
    echo '<td style="padding: 12px; border: 1px solid #dee2e6;"><strong>' . htmlspecialchars($name) . '</strong></td>';
    echo '<td style="padding: 12px; border: 1px solid #dee2e6; color: ' . $color . '; font-weight: bold; font-size: 1.2em; text-align: center;">' . $icon . '</td>';
    echo '<td style="padding: 12px; border: 1px solid #dee2e6;">' . htmlspecialchars($message) . '</td>';
    echo '<td style="padding: 12px; border: 1px solid #dee2e6;"><code style="background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-size: 0.9em;">' . htmlspecialchars($details) . '</code></td>';
    echo '</tr>';
}

// Start collecting test results
$tests = [];

// Test 1: PHP Version
$phpVersion = PHP_VERSION;
$phpVersionStatus = version_compare($phpVersion, '7.4', '>=') ? 'pass' : 'fail';
$tests[] = [
    'name' => 'PHP Version',
    'status' => $phpVersionStatus,
    'message' => $phpVersionStatus === 'pass' ? 'PHP version is adequate' : 'PHP version too old (need 7.4+)',
    'details' => $phpVersion
];

// Test 2: PDO Extension
$pdoLoaded = extension_loaded('pdo');
$pdoMysqlLoaded = extension_loaded('pdo_mysql');
$pdoStatus = ($pdoLoaded && $pdoMysqlLoaded) ? 'pass' : 'fail';
$tests[] = [
    'name' => 'PDO Extension',
    'status' => $pdoStatus,
    'message' => $pdoStatus === 'pass' ? 'PDO is available' : 'PDO or PDO_MySQL not loaded',
    'details' => 'PDO: ' . ($pdoLoaded ? 'Yes' : 'No') . ', PDO_MySQL: ' . ($pdoMysqlLoaded ? 'Yes' : 'No')
];

// Test 3: MySQLi Extension
$mysqliLoaded = extension_loaded('mysqli');
$mysqliStatus = $mysqliLoaded ? 'pass' : 'warn';
$tests[] = [
    'name' => 'MySQLi Extension',
    'status' => $mysqliStatus,
    'message' => $mysqliLoaded ? 'MySQLi is available' : 'MySQLi not loaded (alternative to PDO)',
    'details' => $mysqliLoaded ? 'Yes' : 'No'
];

// Test 4: Database Connection
$dbConnectionStatus = 'fail';
$dbConnectionMessage = 'Cannot test (PDO not loaded)';
$dbConnectionDetails = 'N/A';

if ($pdoLoaded && $pdoMysqlLoaded) {
    try {
        // Try to load config
        if (file_exists(__DIR__ . '/../config/config.php')) {
            require_once __DIR__ . '/../config/config.php';

            // Test PDO connection
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            $dbConnectionStatus = 'pass';
            $dbConnectionMessage = 'Database connection successful';
            $dbConnectionDetails = DB_NAME . '@' . DB_HOST;

            // Test a simple query
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM products");
            $result = $stmt->fetch();
            $dbConnectionDetails .= ' | Products: ' . $result['count'];

        } else {
            $dbConnectionMessage = 'Config file not found';
            $dbConnectionDetails = 'config/config.php missing';
        }
    } catch (Exception $e) {
        $dbConnectionMessage = 'Connection failed';
        $dbConnectionDetails = substr($e->getMessage(), 0, 100);
    }
}

$tests[] = [
    'name' => 'Database Connection',
    'status' => $dbConnectionStatus,
    'message' => $dbConnectionMessage,
    'details' => $dbConnectionDetails
];

// Test 5: Required Functions
$requiredFunctions = ['mysqli_connect', 'json_encode', 'hash', 'openssl_encrypt'];
$missingFunctions = [];
foreach ($requiredFunctions as $func) {
    if (!function_exists($func)) {
        $missingFunctions[] = $func;
    }
}
$functionsStatus = empty($missingFunctions) ? 'pass' : 'fail';
$tests[] = [
    'name' => 'Required PHP Functions',
    'status' => $functionsStatus,
    'message' => empty($missingFunctions) ? 'All required functions available' : 'Missing functions: ' . implode(', ', $missingFunctions),
    'details' => count($requiredFunctions) . ' functions checked'
];

// Test 6: File Permissions
$writableDirs = [];
$unwritableDirs = [];
$dirsToCheck = [
    __DIR__ . '/../logs',
    __DIR__ . '/../assets/images/products',
    __DIR__ . '/../config'
];

foreach ($dirsToCheck as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            $writableDirs[] = basename($dir);
        } else {
            $unwritableDirs[] = basename($dir);
        }
    }
}

$permissionsStatus = empty($unwritableDirs) ? 'pass' : 'warn';
$tests[] = [
    'name' => 'File Permissions',
    'status' => $permissionsStatus,
    'message' => empty($unwritableDirs) ? 'All critical directories are writable' : 'Some directories not writable: ' . implode(', ', $unwritableDirs),
    'details' => 'Writable: ' . (empty($writableDirs) ? 'None' : implode(', ', $writableDirs))
];

// Test 7: Memory Limit
$memoryLimit = ini_get('memory_limit');
$memoryBytes = return_bytes($memoryLimit);
$memoryStatus = $memoryBytes >= 64 * 1024 * 1024 ? 'pass' : 'warn';
$tests[] = [
    'name' => 'PHP Memory Limit',
    'status' => $memoryStatus,
    'message' => $memoryStatus === 'pass' ? 'Memory limit is adequate' : 'Memory limit might be too low',
    'details' => $memoryLimit
];

// Test 8: Upload Max Filesize
$uploadMax = ini_get('upload_max_filesize');
$postMax = ini_get('post_max_size');
$uploadStatus = return_bytes($uploadMax) >= 2 * 1024 * 1024 ? 'pass' : 'warn';
$tests[] = [
    'name' => 'File Upload Limits',
    'status' => $uploadStatus,
    'message' => $uploadStatus === 'pass' ? 'Upload limits are adequate' : 'Upload limits might be too low',
    'details' => 'Upload: ' . $uploadMax . ', Post: ' . $postMax
];

// Test 9: Error Logging
$errorLog = ini_get('error_log');
$logErrors = ini_get('log_errors');
$loggingStatus = ($logErrors && !empty($errorLog)) ? 'pass' : 'warn';
$tests[] = [
    'name' => 'Error Logging',
    'status' => $loggingStatus,
    'message' => $logErrors ? 'Error logging is enabled' : 'Error logging is disabled',
    'details' => $errorLog ?: 'Not configured'
];

// Test 10: Session Configuration
$sessionStatus = 'pass';
$sessionMessage = 'Session configuration OK';
$sessionDetails = session_name() . ' | ' . ini_get('session.cookie_secure') ? 'HTTPS only' : 'HTTP/HTTPS';

if (!session_save_path() || !is_writable(session_save_path())) {
    $sessionStatus = 'fail';
    $sessionMessage = 'Session save path not writable';
}

$tests[] = [
    'name' => 'Session Configuration',
    'status' => $sessionStatus,
    'message' => $sessionMessage,
    'details' => $sessionDetails
];

// Helper function
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}

// Calculate overall status
$passCount = 0;
$failCount = 0;
$warnCount = 0;

foreach ($tests as $test) {
    if ($test['status'] === 'pass') $passCount++;
    elseif ($test['status'] === 'fail') $failCount++;
    else $warnCount++;
}

$overallStatus = $failCount > 0 ? 'fail' : ($warnCount > 0 ? 'warn' : 'pass');

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostic Server - Piese Mașini de Cusut</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            margin-top: 15px;
            background: white;
            color: #333;
        }

        .status-badge.pass { background: #27ae60; color: white; }
        .status-badge.fail { background: #e74c3c; color: white; }
        .status-badge.warn { background: #f39c12; color: white; }

        .card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .card h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }

        td {
            padding: 12px;
            border: 1px solid #dee2e6;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #667eea;
        }

        .info-item strong {
            display: block;
            color: #667eea;
            margin-bottom: 5px;
        }

        .extensions-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
        }

        .extension-tag {
            background: #e8f4f8;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.9em;
            color: #2980b9;
        }

        .extension-tag.missing {
            background: #f8d7da;
            color: #c82333;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #27ae60;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #e74c3c;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #f39c12;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            color: #666;
        }

        @media (max-width: 768px) {
            table {
                font-size: 0.9em;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 Diagnostic Server</h1>
            <p>Piese Mașini de Cusut - Verificare Sistem</p>
            <span class="status-badge <?php echo $overallStatus; ?>">
                <?php
                if ($overallStatus === 'pass') echo '✓ Sistem OK';
                elseif ($overallStatus === 'fail') echo '✗ Probleme Critice';
                else echo '⚠ Avertismente';
                ?>
            </span>
        </div>

        <?php if ($failCount > 0): ?>
        <div class="alert alert-danger">
            <strong>Atenție!</strong> Au fost găsite <?php echo $failCount; ?> probleme critice care trebuie rezolvate urgent.
        </div>
        <?php endif; ?>

        <?php if ($warnCount > 0): ?>
        <div class="alert alert-warning">
            <strong>Avertisment!</strong> Au fost găsite <?php echo $warnCount; ?> avertismente care ar trebui verificate.
        </div>
        <?php endif; ?>

        <?php if ($failCount === 0 && $warnCount === 0): ?>
        <div class="alert alert-success">
            <strong>Excelent!</strong> Toate verificările au trecut cu succes.
        </div>
        <?php endif; ?>

        <div class="card">
            <h2>📊 Rezultate Teste</h2>
            <table>
                <thead>
                    <tr>
                        <th>Test</th>
                        <th>Status</th>
                        <th>Mesaj</th>
                        <th>Detalii</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests as $test): ?>
                        <?php testResult($test['name'], $test['status'], $test['message'], $test['details']); ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>💻 Informații Server</h2>
            <div class="info-grid">
                <div class="info-item">
                    <strong>Versiune PHP</strong>
                    <?php echo PHP_VERSION; ?>
                </div>
                <div class="info-item">
                    <strong>Server Software</strong>
                    <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
                </div>
                <div class="info-item">
                    <strong>Sistem de Operare</strong>
                    <?php echo PHP_OS; ?>
                </div>
                <div class="info-item">
                    <strong>Limită Memorie</strong>
                    <?php echo ini_get('memory_limit'); ?>
                </div>
                <div class="info-item">
                    <strong>Max Upload</strong>
                    <?php echo ini_get('upload_max_filesize'); ?>
                </div>
                <div class="info-item">
                    <strong>Max Post</strong>
                    <?php echo ini_get('post_max_size'); ?>
                </div>
            </div>
        </div>

        <div class="card">
            <h2>🔧 Extensii PHP Încărcate</h2>
            <div class="extensions-list">
                <?php
                $extensions = get_loaded_extensions();
                sort($extensions);
                foreach ($extensions as $ext):
                    $isImportant = in_array($ext, ['pdo', 'pdo_mysql', 'mysqli', 'json', 'session', 'mbstring', 'openssl']);
                ?>
                <span class="extension-tag <?php echo $isImportant ? '' : ''; ?>">
                    <?php echo htmlspecialchars($ext); ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <h2>⚙️ Configurare PHP</h2>
            <table>
                <thead>
                    <tr>
                        <th>Directivă</th>
                        <th>Valoare Locală</th>
                        <th>Valoare Globală</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $iniSettings = [
                        'display_errors',
                        'log_errors',
                        'error_log',
                        'max_execution_time',
                        'memory_limit',
                        'upload_max_filesize',
                        'post_max_size',
                        'session.save_path',
                        'session.cookie_secure',
                        'session.cookie_httponly'
                    ];

                    foreach ($iniSettings as $setting):
                        $local = ini_get($setting);
                        $global = ini_get($setting);
                    ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($setting); ?></code></td>
                        <td><?php echo htmlspecialchars($local ?: '(not set)'); ?></td>
                        <td><?php echo htmlspecialchars($global ?: '(not set)'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>📁 Permisiuni Fișiere</h2>
            <table>
                <thead>
                    <tr>
                        <th>Director/Fișier</th>
                        <th>Există</th>
                        <th>Writable</th>
                        <th>Permisiuni</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $paths = [
                        '/../config/config.php',
                        '/../config/database.php',
                        '/../logs',
                        '/../assets/images/products',
                        '/../includes'
                    ];

                    foreach ($paths as $path):
                        $fullPath = __DIR__ . $path;
                        $exists = file_exists($fullPath);
                        $writable = $exists && is_writable($fullPath);
                        $perms = $exists ? substr(sprintf('%o', fileperms($fullPath)), -4) : 'N/A';
                    ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($path); ?></code></td>
                        <td><?php echo $exists ? '✓' : '✗'; ?></td>
                        <td><?php echo $writable ? '✓' : '✗'; ?></td>
                        <td><?php echo $perms; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="card">
            <h2>🔗 Rezumat</h2>
            <div class="info-grid">
                <div class="info-item" style="border-left-color: #27ae60;">
                    <strong>Teste Trecute</strong>
                    <?php echo $passCount; ?> din <?php echo count($tests); ?>
                </div>
                <div class="info-item" style="border-left-color: #e74c3c;">
                    <strong>Erori Critice</strong>
                    <?php echo $failCount; ?>
                </div>
                <div class="info-item" style="border-left-color: #f39c12;">
                    <strong>Avertismente</strong>
                    <?php echo $warnCount; ?>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>Generat la <?php echo date('Y-m-d H:i:s'); ?> | PHP <?php echo PHP_VERSION; ?></p>
        </div>
    </div>
</body>
</html>
