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

## Códigos de Estado HTTP

- **200** - Operación exitosa
- **201** - Recurso creado exitosamente
- **401** - No autorizado (token inválido o faltante)
- **404** - Recurso no encontrado
- **422** - Error de validación
- **500** - Error interno del servidor

## Errores de Validación (422)

```json
{
    "message": "Validation failed",
    "errors": {
        "email": [
            "The email field is required."
        ],
        "password": [
            "The password must be at least 8 characters."
        ]
    }
}
```

## Ejemplos de Uso

### 1. Registrar y autenticar
```bash
# Registro
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
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
