$replacements = @{
    'â€¢' = '&bull;'
    'â€”' = '&mdash;'
    'â†’' = '&rarr;'
    'âœ“' = '&check;'
    'â• ' = '='
    'Ã—' = '&times;'
}

Get-ChildItem -Path resources/views -Recurse -Filter *.blade.php | ForEach-Object {
    $content = Get-Content $_.FullName -Raw -Encoding UTF8
    $changed = $false
    foreach ($key in $replacements.Keys) {
        if ($content -match $key) {
            $content = $content.Replace($key, $replacements[$key])
            $changed = $true
        }
    }
    if ($changed) {
        Set-Content -Path $_.FullName -Value $content -Encoding UTF8
        Write-Host "Fixed: $($_.FullName)"
    }
}
