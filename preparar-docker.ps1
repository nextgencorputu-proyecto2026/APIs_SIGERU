
$ErrorActionPreference = "Stop"

Set-Location $PSScriptRoot

if (-not (Test-Path ".env")) {
    Copy-Item ".env.example" ".env"
    Write-Host "Archivo .env creado."
}

$contenido = Get-Content ".env" -Raw

function New-SigeruKey {
    $bytes = [byte[]]::new(32)
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()

    try {
        $rng.GetBytes($bytes)
    }
    finally {
        $rng.Dispose()
    }

    return "base64:" + [Convert]::ToBase64String($bytes)
}

function New-SigeruJwtSecret {
    $bytes = [byte[]]::new(48)
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()

    try {
        $rng.GetBytes($bytes)
    }
    finally {
        $rng.Dispose()
    }

    return [Convert]::ToBase64String($bytes)
}

function Set-SigeruVariable {
    param(
        [string]$Nombre,
        [string]$Valor
    )

    $lineas = $script:contenido -split '\r?\n'
    $encontrada = $false

    $lineas = foreach ($linea in $lineas) {
        if ($linea -match "^$([regex]::Escape($Nombre))=(.*)$") {
            $encontrada = $true

            if ([string]::IsNullOrWhiteSpace($Matches[1])) {
                "$Nombre=$Valor"
            }
            else {
                $linea
            }
        }
        else {
            $linea
        }
    }

    if (-not $encontrada) {
        throw "Falta la variable $Nombre en .env"
    }

    $script:contenido = $lineas -join "`r`n"
}

Set-SigeruVariable "USUARIOS_APP_KEY" (New-SigeruKey)
Set-SigeruVariable "GESTION_APP_KEY" (New-SigeruKey)
Set-SigeruVariable "RECOLECCION_APP_KEY" (New-SigeruKey)
Set-SigeruVariable "USUARIOS_JWT_SECRET" (New-SigeruJwtSecret)

[System.IO.File]::WriteAllText(
    (Join-Path $PSScriptRoot ".env"),
    $contenido
)

Write-Host ""
Write-Host "Configuracion de SiGeRU preparada correctamente."
Write-Host "Las claves existentes se conservaron."
Write-Host ""
Write-Host "Ahora podes ejecutar:"
Write-Host "docker compose up -d --build"
