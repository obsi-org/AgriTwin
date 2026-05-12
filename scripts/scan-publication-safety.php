#!/usr/bin/env php
<?php
$root = dirname(__DIR__);
$tracked = [];
exec('git -C ' . escapeshellarg($root) . ' ls-files --cached --others --exclude-standard', $tracked, $code);
if ($code !== 0) {
    fwrite(STDERR, "Unable to list tracked files.\n");
    exit(2);
}

$skipExtensions = ['png', 'jpg', 'jpeg', 'gif', 'tif', 'tiff', 'glb', 'gltf', 'obj', 'blend', 'blend1', 'fbx', 'stl', 'bin', 'pdf'];
$findings = [];

foreach ($tracked as $file) {
    if (in_array($file, ['.gitignore', '.dockerignore', 'scripts/scan-publication-safety.php'], true)) {
        continue;
    }
    $path = $root . '/' . $file;
    if (!is_file($path)) {
        continue;
    }
    $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    if (in_array($extension, $skipExtensions, true)) {
        continue;
    }
    $content = file_get_contents($path);
    if ($content === false || str_contains($content, "\0")) {
        continue;
    }
    $lines = preg_split('/\R/', $content) ?: [];
    foreach ($lines as $index => $line) {
        $lineNo = $index + 1;
        $checks = [
            'private key material' => '/BEGIN (RSA |OPENSSH |EC |DSA )?PRIVATE KEY/',
            'certificate/private key file reference' => '/(?:^|[\/\'"])[^\\s\'"]+\.(pem|p12|pfx|key)\b/i',
            'jwt-like token' => '/eyJ[A-Za-z0-9_-]{10,}\.eyJ[A-Za-z0-9_-]{10,}\.[A-Za-z0-9_-]{20,}/',
            'hard-coded sensitive assignment' => '/(?:password|passwd|token|secret|api[_-]?key|client[_-]?secret)\s*[=:]\s*[\'"](?!$|replace-|change-me|mock-|demo-|synthetic-|local-|YOUR_|http:\/\/localhost)([^\'"]{12,})[\'"]/i',
            'raw/private pilot marker' => '/\b(raw_pilot|pilot_data|private parcel boundary|farm network secret|production apn)\b/i',
        ];
        foreach ($checks as $label => $pattern) {
            if (preg_match($pattern, $line)) {
                $findings[] = [$file, $lineNo, $label, trim($line)];
            }
        }
    }
}

if ($findings !== []) {
    echo "Publication safety scan found potential issues:\n";
    foreach ($findings as [$file, $lineNo, $label, $line]) {
        echo "- {$file}:{$lineNo} {$label}: " . substr($line, 0, 180) . "\n";
    }
    echo "\nThis grep-based scan is conservative. Review findings before publication.\n";
    exit(1);
}

echo "Publication safety scan passed for tracked text files.\n";
echo "Limitations: binary files, PDFs, images, and git history require separate manual review before public release.\n";
