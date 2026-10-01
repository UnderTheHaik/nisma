$ErrorActionPreference='Stop'
$projectRoot=Split-Path $PSScriptRoot -Parent
$runtime=Join-Path $projectRoot 'work/nisma-runtime'
$php=Join-Path $runtime 'php/php.exe'
$wp=Join-Path $runtime 'wordpress/wordpress'
if (!(Test-Path -LiteralPath $php) -or !(Test-Path -LiteralPath "$wp/wp-config.php")) { throw 'Run nisma-store/setup.ps1 first.' }
Copy-Item -Path "$PSScriptRoot/theme/*" -Destination "$wp/wp-content/themes/nisma" -Recurse -Force
Copy-Item -LiteralPath "$PSScriptRoot/plugins/nisma-demo.php" -Destination "$wp/wp-content/plugins/nisma-demo/nisma-demo.php" -Force
Write-Host 'Nisma: http://127.0.0.1:8090 — Ctrl+C to stop'
& $php -S 127.0.0.1:8090 -t $wp "$PSScriptRoot/router.php"

