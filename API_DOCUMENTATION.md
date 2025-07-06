# API Documentation - User Management

## Autenticación

### POST /api/register
Registra un nuevo usuario.

**Parámetros:**
```json
{
    "name": "string (required, max:255)",
    "email": "string (required, email, unique)",
    "password": "string (required, min:8)",
    "password_confirmation": "string (required, must match password)"
}
```

**Respuesta exitosa (201):**
```json
{
    "message": "User registered successfully",
    "token": "1|token_string",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-07-06T12:00:00.000000Z",
        "updated_at": "2025-07-06T12:00:00.000000Z"
    }
}
```

### POST /api/login
Autentica un usuario existente.

**Parámetros:**
```json
{
    "email": "string (required)",
    "password": "string (required)"
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Login successful",
    "token": "1|token_string",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com"
    }
}
```

---

## CRUD de Usuarios (Requiere Autenticación)

**Header requerido:**
```
Authorization: Bearer {token}
```

### GET /api/users
Lista todos los usuarios.

**Respuesta exitosa (200):**
```json
{
    "message": "Users retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "created_at": "2025-07-06T12:00:00.000000Z",
            "updated_at": "2025-07-06T12:00:00.000000Z"
        }
    ]
}
```

### GET /api/users/{id}
Obtiene un usuario específico.

**Respuesta exitosa (200):**
```json
{
    "message": "User retrieved successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "created_at": "2025-07-06T12:00:00.000000Z",
        "updated_at": "2025-07-06T12:00:00.000000Z"
    }
}
```

**Error 404:**
```json
{
    "message": "User not found"
}
```

### POST /api/users
Crea un nuevo usuario.

**Parámetros:**
```json
{
    "name": "string (required, max:255)",
    "email": "string (required, email, unique)",
    "password": "string (required, min:8)"
}
```

**Respuesta exitosa (201):**
```json
{
    "message": "User created successfully",
    "data": {
        "id": 2,
        "name": "Jane Doe",
        "email": "jane@example.com",
        "created_at": "2025-07-06T12:00:00.000000Z",
        "updated_at": "2025-07-06T12:00:00.000000Z"
    }
}
```

### PUT /api/users/{id}
Actualiza un usuario existente (actualización completa o parcial).

**Parámetros (todos opcionales):**
```json
{
    "name": "string (optional, max:255)",
    "email": "string (optional, email, unique)",
    "password": "string (optional, min:8)"
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "User updated successfully",
    "data": {
        "id": 1,
        "name": "Updated Name",
        "email": "updated@example.com",
        "created_at": "2025-07-06T12:00:00.000000Z",
        "updated_at": "2025-07-06T12:00:00.000000Z"
    }
}
```

### DELETE /api/users/{id}
Elimina un usuario.

**Respuesta exitosa (200):**
```json
{
    "message": "User deleted successfully"
}
```

**Error 404:**
```json
{
    "message": "User not found"
}
```

---

## CRUD de Mascotas (Requiere Autenticación)

**Header requerido:**
```
Authorization: Bearer {token}
```

### GET /api/pets
Lista todas las mascotas.

**Respuesta exitosa (200):**
```json
{
    "message": "Pets retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Buddy",
            "photo": "pets/photo.jpg",
            "photo_url": "http://localhost:8000/storage/pets/photo.jpg",
            "status": "transit",
            "created_at": "2025-07-06T12:00:00.000000Z",
            "updated_at": "2025-07-06T12:00:00.000000Z"
        }
    ]
}
```

### GET /api/pets/{id}
Obtiene una mascota específica.

**Respuesta exitosa (200):**
```json
{
    "message": "Pet retrieved successfully",
    "data": {
        "id": 1,
        "name": "Buddy",
        "photo": "pets/photo.jpg",
        "photo_url": "http://localhost:8000/storage/pets/photo.jpg",
        "status": "transit",
        "created_at": "2025-07-06T12:00:00.000000Z",
        "updated_at": "2025-07-06T12:00:00.000000Z"
    }
}
```

### POST /api/pets
Crea una nueva mascota.

**Parámetros (multipart/form-data):**
```
name: string (required, max:255)
photo: file (optional, jpg/png, max:2MB)
status: enum (optional, values: transit|adopted|deceased, default: transit)
```

**Respuesta exitosa (201):**
```json
{
    "message": "Pet created successfully",
    "data": {
        "id": 1,
        "name": "Buddy",
        "photo": "pets/photo.jpg",
        "photo_url": "http://localhost:8000/storage/pets/photo.jpg",
        "status": "transit",
        "created_at": "2025-07-06T12:00:00.000000Z",
        "updated_at": "2025-07-06T12:00:00.000000Z"
    }
}
```

### PUT /api/pets/{id}
Actualiza una mascota existente.

**Parámetros (todos opcionales, multipart/form-data):**
```
name: string (optional, max:255)
photo: file (optional, jpg/png, max:2MB)
status: enum (optional, values: transit|adopted|deceased)
```

**Respuesta exitosa (200):**
```json
{
    "message": "Pet updated successfully",
    "data": {
        "id": 1,
        "name": "Updated Buddy",
        "photo": "pets/new-photo.jpg",
        "photo_url": "http://localhost:8000/storage/pets/new-photo.jpg",
        "status": "adopted",
        "created_at": "2025-07-06T12:00:00.000000Z",
        "updated_at": "2025-07-06T12:00:00.000000Z"
    }
}
```

### DELETE /api/pets/{id}
Elimina una mascota (y su foto asociada).

**Respuesta exitosa (200):**
```json
{
    "message": "Pet deleted successfully"
}
```

---

## CRUD de Solicitudes de Adopción (Requiere Autenticación)

**Header requerido:**
```
Authorization: Bearer {token}
```

### GET /api/adoption-requests
Lista todas las solicitudes de adopción.

**Respuesta exitosa (200):**
```json
{
    "message": "Adoption requests retrieved successfully",
    "data": [
        {
            "id": 1,
            "address": "123 Main St",
            "phone": "555-1234",
            "application": "I love animals and have experience caring for pets.",
            "status": "pending",
            "pet": {
                "id": 1,
                "name": "Buddy",
                "status": "transit",
                "photo_url": "http://localhost:8000/storage/pets/photo.jpg"
            },
            "user": {
                "id": 1,
                "name": "John Doe",
                "email": "john@example.com"
            },
            "created_at": "2025-07-06T12:00:00.000000Z",
            "updated_at": "2025-07-06T12:00:00.000000Z"
        }
    ]
}
```

### GET /api/adoption-requests/{id}
Obtiene una solicitud de adopción específica.

**Respuesta exitosa (200):**
```json
{
    "message": "Adoption request retrieved successfully",
    "data": {
        "id": 1,
        "address": "123 Main St",
        "phone": "555-1234",
        "application": "I love animals and have experience caring for pets.",
        "status": "pending",
        "pet": {
            "id": 1,
            "name": "Buddy",
            "status": "transit",
            "photo_url": "http://localhost:8000/storage/pets/photo.jpg"
        },
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "created_at": "2025-07-06T12:00:00.000000Z",
        "updated_at": "2025-07-06T12:00:00.000000Z"
    }
}
```

### POST /api/adoption-requests
Crea una nueva solicitud de adopción.

**Parámetros:**
```json
{
    "pet_id": "integer (required, must exist in pets table)",
    "user_id": "integer (required, must exist in users table)",
    "address": "string (required, max:255)",
    "phone": "string (required, max:20)",
    "application": "string (required, text description)",
    "status": "enum (optional, values: pending|approved|rejected, default: pending)"
}
```

**Respuesta exitosa (201):**
```json
{
    "message": "Adoption request created successfully",
    "data": {
        "id": 1,
        "address": "123 Main St",
        "phone": "555-1234",
        "application": "I love animals and have experience caring for pets.",
        "status": "pending",
        "pet": {
            "id": 1,
            "name": "Buddy",
            "status": "transit"
        },
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "created_at": "2025-07-06T12:00:00.000000Z",
        "updated_at": "2025-07-06T12:00:00.000000Z"
    }
}
```

**Error 409 (Conflict):**
```json
{
    "message": "User already has a pending adoption request for this pet"
}
```

### PUT /api/adoption-requests/{id}
Actualiza una solicitud de adopción existente.

**Parámetros (todos opcionales):**
```json
{
    "pet_id": "integer (optional, must exist in pets table)",
    "user_id": "integer (optional, must exist in users table)",
    "address": "string (optional, max:255)",
    "phone": "string (optional, max:20)",
    "application": "string (optional, text description)",
    "status": "enum (optional, values: pending|approved|rejected)"
}
```

**Respuesta exitosa (200):**
```json
{
    "message": "Adoption request updated successfully",
    "data": {
        "id": 1,
        "address": "Updated Address",
        "phone": "555-9999",
        "application": "Updated application text",
        "status": "approved",
        "pet": {
            "id": 1,
            "name": "Buddy",
            "status": "transit"
        },
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com"
        },
        "created_at": "2025-07-06T12:00:00.000000Z",
        "updated_at": "2025-07-06T12:00:00.000000Z"
    }
}
```

### DELETE /api/adoption-requests/{id}
Elimina una solicitud de adopción.

**Respuesta exitosa (200):**
```json
{
    "message": "Adoption request deleted successfully"
}
```

### PATCH /api/adoption-requests/{id}/approve
Aprueba una solicitud de adopción.

**Respuesta exitosa (200):**
```json
{
    "message": "Adoption request approved successfully",
    "data": {
        "id": 1,
        "status": "approved"
    }
}
```

### PATCH /api/adoption-requests/{id}/reject
Rechaza una solicitud de adopción.

**Respuesta exitosa (200):**
```json
{
    "message": "Adoption request rejected successfully",
    "data": {
        "id": 1,
        "status": "rejected"
    }
}
```

---

## Estados de Solicitudes de Adopción (AdoptionRequestStatus)

### Valores permitidos:
- **`pending`** - Solicitud pendiente (valor por defecto)
- **`approved`** - Solicitud aprobada
- **`rejected`** - Solicitud rechazada

---

## Reglas de Negocio para Solicitudes de Adopción

### Prevención de Duplicados:
- Un usuario no puede tener múltiples solicitudes **pendientes** para la misma mascota
- Si una solicitud fue aprobada o rechazada, el usuario puede crear una nueva solicitud

### Validaciones:
- **pet_id** y **user_id** deben existir en sus respectivas tablas
- **address** y **phone** son campos requeridos
- **application** debe contener una descripción del solicitante

---

## Ejemplos de Uso con cURL

### 1. Autenticación
```bash
# Registro de usuario
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login de usuario
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### 2. Operaciones CRUD con token
```bash
# Listar usuarios
curl -X GET http://localhost:8000/api/users \
  -H "Authorization: Bearer {token}"

# Crear usuario
curl -X POST http://localhost:8000/api/users \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Jane Doe",
    "email": "jane@example.com",
    "password": "password123"
  }'

# Actualizar usuario
curl -X PUT http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Name"
  }'

# Eliminar usuario
curl -X DELETE http://localhost:8000/api/users/1 \
  -H "Authorization: Bearer {token}"
```

### 3. Operaciones CRUD de Mascotas
```bash
# Listar mascotas
curl -X GET http://localhost:8000/api/pets \
  -H "Authorization: Bearer {token}"

# Crear mascota sin foto
curl -X POST http://localhost:8000/api/pets \
  -H "Authorization: Bearer {token}" \
  -F "name=Buddy" \
  -F "status=transit"

# Crear mascota con foto
curl -X POST http://localhost:8000/api/pets \
  -H "Authorization: Bearer {token}" \
  -F "name=Luna" \
  -F "status=adopted" \
  -F "photo=@/path/to/pet-photo.jpg"

# Obtener mascota específica
curl -X GET http://localhost:8000/api/pets/1 \
  -H "Authorization: Bearer {token}"

# Actualizar mascota (solo nombre)
curl -X PUT http://localhost:8000/api/pets/1 \
  -H "Authorization: Bearer {token}" \
  -F "name=Updated Buddy"

# Actualizar mascota con nueva foto
curl -X PUT http://localhost:8000/api/pets/1 \
  -H "Authorization: Bearer {token}" \
  -F "name=Rex" \
  -F "status=adopted" \
  -F "photo=@/path/to/new-photo.png"

# Eliminar mascota
curl -X DELETE http://localhost:8000/api/pets/1 \
  -H "Authorization: Bearer {token}"
```

### 4. Operaciones CRUD de Solicitudes de Adopción
```bash
# Listar solicitudes de adopción
curl -X GET http://localhost:8000/api/adoption-requests \
  -H "Authorization: Bearer {token}"

# Crear solicitud de adopción
curl -X POST http://localhost:8000/api/adoption-requests \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "pet_id": 1,
    "user_id": 2,
    "address": "123 Main Street, City, State 12345",
    "phone": "555-1234",
    "application": "I have experience with dogs and would love to provide a loving home for this pet. I have a large yard and work from home."
  }'

# Obtener solicitud específica
curl -X GET http://localhost:8000/api/adoption-requests/1 \
  -H "Authorization: Bearer {token}"

# Actualizar solicitud de adopción
curl -X PUT http://localhost:8000/api/adoption-requests/1 \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "address": "456 Oak Avenue, New City, State 54321",
    "phone": "555-9999",
    "application": "Updated: I have moved to a new house with an even bigger yard!"
  }'

# Aprobar solicitud de adopción
curl -X PATCH http://localhost:8000/api/adoption-requests/1/approve \
  -H "Authorization: Bearer {token}"

# Rechazar solicitud de adopción
curl -X PATCH http://localhost:8000/api/adoption-requests/1/reject \
  -H "Authorization: Bearer {token}"

# Eliminar solicitud de adopción
curl -X DELETE http://localhost:8000/api/adoption-requests/1 \
  -H "Authorization: Bearer {token}"
```
