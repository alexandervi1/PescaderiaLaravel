Api contracts para WinScriptPlus (Opción A1) – diseño detallado

Resumen
- Este documento especifica el contrato de la API local (ASP.NET Core Minimal API) que orquesta la interfaz entre la UI WinUI 3 + WebView2 y la lógica de PowerShell (WinScriptPlus).
- Incluye endpoints, métodos, estructuras de request/response, códigos de estado y ejemplos de payloads.

1) Endpoints principales
- GET /health
  - Descripción: estado básico de la API y del entorno local.
  - Respuesta exitosa:
    { "status": "ok" }
  - Respuesta de fallo:
    { "status": "error", "details": "mensaje" }

- GET /status
  - Descripción: estado actual del equipo y el entorno (detección, energía, sistema operativo, uso de recursos).
  - Respuesta exitosa (JSON):
    {
      "computerType": "Desktop" | "Laptop",
      "energySource": "AC" | "Battery",
      "telemetry": "minimized" | "enabled",
      "os": "Windows 11",
      "cpuUsage": 12.3,
      "ramUsage": 45.6
    }

- GET /profiles
  - Descripción: obtener la lista de perfiles disponibles (incluye los metadatos de cada perfil).
  - Respuesta exitosa:
    { "profiles": [ {"name":"GamerAggressive","type":"GamerAggressive","settings":{...},"batteryPolicy":{...},"applyOrder":["preflight","apply"] ,"createdAt":"...","updatedAt":"..."}, ... ] }

- POST /profiles
  - Descripción: crear un nuevo perfil. Cuerpo JSON debe cumplir con Profile schema (ver schemas/). 
  - Cuerpo (ejemplo):
    {
      "name": "GamerAggressive",
      "type": "GamerAggressive",
      "settings": { ... },
      "batteryPolicy": { ... },
      "applyOrder": ["preflightChecks", "setPowerPlan"]
    }
  - Respuesta exitosa:
    { "success": true, "profileName": "GamerAggressive", "createdAt": "...", "message": "Profile created" }

- GET /profiles/{name}
  - Descripción: obtener detalle de un perfil específico.
  - Respuesta exitosa: perfil completo en JSON (con todos los campos).

- POST /profiles/{name}/apply?whatIf=true
  - Descripción: aplicar un perfil. Si whatIf=true (valor por defecto), simula los cambios y devuelve progreso; si whatIf=false aplica cambios reales tras confirmación en UI.
  - Parámetros: whatIf (bool, opcional, predeterminado true)
  - Respuesta inicial:
    { "started": true, "profileName": "GamerAggressive", "whatIf": true, "progressId": "abc123", "estimatedSeconds": 12, "status": "in_progress" }

- POST /restore-points
  - Descripción: crear un punto de restauración antes de aplicar cambios.
  - Cuerpo opcional: { "description": "Pre-change backup" }
  - Respuesta exitosa:
    { "success": true, "restorePointId": "RP-20260319-1234", "timestamp": "2026-03-19T12:00:00Z", "status": "created" }

- GET /reports/latest
  - Descripción: obtener el informe más reciente generado tras una ejecución.
  - Acepta: application/json, text/html
  - Respuesta: JSON o HTML que describe el resumen de resultados y un enlace a los logs.

2) Estructuras de error
- Formato común de error (json):
  { "success": false, "error": { "code": "InvalidInput", "message": "Descripción breve del error", "details": "Detalles técnicos opcionales" } }

3) Versionado y seguridad
- Este contrato utiliza -WhatIf por defecto para operaciones que cambian la configuración.
- La UI debe requerir confirmación para aplicar cambios reales.
- Safe Mode disponible para revertir cambios sin riesgo.

4) Consideraciones de seguridad y autenticación
- Este servicio corre localmente y no expone endpoints fuera del host. No se implementa autenticación remota; se confía en el entorno del usuario y en la seguridad de la máquina.
- Se deben registrar todas las operaciones para auditoría.

5) Tópicos de implementación
- Validación de payloads contra el JSON Schema correspondiente (ver schemas/).
- Manejo de errores centralizado con códigos legibles y trazabilidad.
- Pruebas unitarias e integradas para endpoints clave.
