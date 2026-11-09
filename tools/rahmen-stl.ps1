<#
  Erzeugt die STL-Dateien für eine Rahmen-Bestellung aus dem Shop (Schattenfugenrahmen nach Maß).
  Die Maße stehen im Namen der Bestellposition, z. B.:
    "Schattenfugenrahmen nach Maß – Bild 650 × 450 × 20 mm, Fugenabstand 10 mm, STL-Dateien (Druckbett 256 mm)"

  Aufruf (PowerShell):
    .\rahmen-stl.ps1 -Laenge 650 -Breite 450 -Tiefe 20 -Fuge 10 -Druckbett 256 -Scad "C:\Pfad\Schattenfugenrahmen Generator.scad"

  Ergebnis: Ordner "Rahmen_650x450x20" neben diesem Skript mit je einer STL pro Druckplatte
  (Ecken, lange Segmente, kurze Segmente) und einer ZIP-Datei zum Verschicken.
#>
param(
    [Parameter(Mandatory)][double]$Laenge,
    [Parameter(Mandatory)][double]$Breite,
    [Parameter(Mandatory)][double]$Tiefe,
    [double]$Fuge = 10,
    [double]$Druckbett = 256,
    [string]$Scad = "C:\Users\PC\OneDrive\Dokumente\3D Prints\Openscad Tests\Schattenfugenrahmen Generator.scad",
    [string]$OpenScad = "C:\Program Files\OpenSCAD\openscad.com"
)

$ErrorActionPreference = 'Stop'
$inv = [System.Globalization.CultureInfo]::InvariantCulture
function N($v) { ([double]$v).ToString($inv) }

if (-not (Test-Path $Scad))     { throw "SCAD-Datei nicht gefunden: $Scad" }
if (-not (Test-Path $OpenScad)) { throw "OpenSCAD nicht gefunden: $OpenScad" }

$name = "Rahmen_{0}x{1}x{2}" -f (N $Laenge), (N $Breite), (N $Tiefe)
$out  = Join-Path $PSScriptRoot $name
New-Item -ItemType Directory -Force -Path $out | Out-Null

$platten = @(
    @{ nr = 1; datei = '1_Ecken.stl' },
    @{ nr = 2; datei = '2_Lange_Segmente.stl' },
    @{ nr = 3; datei = '3_Kurze_Segmente.stl' }
)

foreach ($p in $platten) {
    $ziel = Join-Path $out $p.datei
    Write-Host "Erzeuge $($p.datei) ..."
    & $OpenScad -o $ziel `
        -D "bild_laenge=$(N $Laenge)" -D "bild_breite=$(N $Breite)" -D "bild_hoehe=$(N $Tiefe)" `
        -D "SCHATTENFUGEN_ABSTAND=$(N $Fuge)" -D "groesse_druckbett=$(N $Druckbett)" `
        -D "druckplatte=$($p.nr)" $Scad 2>&1 | Where-Object { $_ -match 'ERROR|Teile gesamt|Segment' }
    if (-not (Test-Path $ziel)) { throw "STL wurde nicht erzeugt: $($p.datei)" }
}

$zip = "$out.zip"
if (Test-Path $zip) { Remove-Item $zip }
Compress-Archive -Path (Join-Path $out '*.stl') -DestinationPath $zip
Write-Host "Fertig: $zip"
