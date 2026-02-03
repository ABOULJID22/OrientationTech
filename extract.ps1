$keys = @()
Get-ChildItem -Path . -Recurse -Filter *.php | ForEach-Object {
  $content = Get-Content $_.FullName -Raw
  [regex]::Matches($content, "__\(\s*['\"](filament\.[^'\"]+)['\"]\s*\)") | ForEach-Object { $keys += $_.Groups[1].Value }
}
$keys = $keys | Sort-Object -Unique
$keys | Out-File filament_keys.txt -Encoding utf8
$en = Get-Content resources\lang\en.json -Raw
$missingEn = $keys | Where-Object { $en -notmatch ('"'+[regex]::Escape($_)+'"') }
$missingEn | Out-File missing_en.txt -Encoding utf8
$fr = Get-Content resources\lang\fr.json -Raw
$missingFr = $keys | Where-Object { $fr -notmatch ('"'+[regex]::Escape($_)+'"') }
$missingFr | Out-File missing_fr.txt -Encoding utf8
Write-Output "Done: $($keys.Count) keys; missing_en: $((Get-Content missing_en.txt| Measure-Object -Line).Lines); missing_fr: $((Get-Content missing_fr.txt | Measure-Object -Line).Lines)"
