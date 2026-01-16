<?php
/**
 * Fix index.php - Add missing PHP closing tag
 */
$file = '/media/iwan/New Volume1/Iulian/GeminiCLI/Siteuri pentru portofoliu/PieseMasiniCusut/index.php';

// Read current file
$content = file_get_contents($file);

// Remove the duplicate <?php tags at the end
$content = str_replace("<?php\n<?php\n", "<?php\n", $content);

// Ensure file ends with ?>
if (!preg_match('/\?>\s*$/', $content)) {
    $content = preg_replace('/<\/script>\s*$/', "</script>\n\n?>", $content);
}

// Write back
file_put_contents($file, $content);

echo "✓ Fix applied\n";
echo "Last 5 lines:\n";
echo exec("tail -5 " . escapeshellarg($file));
