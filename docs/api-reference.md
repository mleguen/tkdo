# API Reference

This document provides a comprehensive reference for the Tkdo REST API, including all endpoints, request/response formats, and authentication requirements.

## Table of Contents

- [API Overview](#api-overview)
- [Authentication](#authentication)
- [Common Patterns](#common-patterns)
- [Error Handling](#error-handling)
- [API Endpoints](#api-endpoints)
  - [Authentication Endpoints](#authentication-endpoints)
  - [User Endpoints](#user-endpoints)
  - [Occasion Endpoints](#occasion-endpoints)
  - [Gift Idea Endpoints](#gift-idea-endpoints)
  - [Exclusion Endpoints](#exclusion-endpoints)
  - [Draw Endpoints](#draw-endpoints)
- [Using curl for Testing](#using-curl-for-testing)
- [Response Examples](#response-examples)

## API Overview

### Base URL

The API is accessed through the `/api` prefix:

**Production:**
```
https://your-tkdo-instance.com/api
```

**Development (Docker):**
```
http://localhost:8080/api
```

**Setting up for curl examples:**
```bash
# Production
export BASE_URL="https://your-tkdo-instance.com/api"

# Development
export BASE_URL="http://localhost:8080/api"
```

### Versioning

The API does not currently use version numbers in the URL. All endpoints are accessed directly under `/api/`.

### Content Type

- **Request**: `application/x-www-form-urlencoded` or `application/json`
- **Response**: `application/json`

All responses are formatted as pretty-printed JSON with a trailing newline.

### HTTP Methods

The API uses standard REST methods:

| Method   | Usage                                       |
|----------|---------------------------------------------|
| `GET`    | Retrieve resources                          |
| `POST`   | Create new resources                        |
| `PUT`    | Update existing resources                   |
| `DELETE` | Delete resources (soft delete for ideas)    |

### CORS Support

The API includes CORS support with a pre-flight OPTIONS handler for all routes.

## Authentication

### Overview

Tkdo uses **JWT (JSON Web Token)** based authentication. After logging in, clients receive a token that must be included in subsequent requests.

### Token Format

Tokens are signed JWTs containing:
- User ID (`idUtilisateur`)
- Admin status (`admin`)
- Expiration time

### Providing the Token

Tokens can be provided in two ways:

#### 1. Bearer Token (Recommended)

```
Authorization: Bearer YOUR_TOKEN_HERE
```

**curl example:**
```bash
curl -H "Authorization: Bearer $TOKEN" \
$BASE_URL/utilisateur
```

#### 2. Basic Auth with Token as Username

```
Authorization: Basic BASE64(TOKEN:)
```

**curl example:**
```bash
curl -u $TOKEN: \
$BASE_URL/utilisateur
```

Note the colon (`:`) after the token - this treats the token as a username with an empty password.

### Obtaining a Token

To obtain a token, use the `/api/connexion` endpoint:

```bash
curl -X POST \
-d identifiant=USERNAME \
-d mdp=PASSWORD \
$BASE_URL/connexion
```

**Response:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "utilisateur": {
    "id": 1,
    "nom": "Alice",
    "genre": "F",
    "admin": true
  }
}
```

### Token Expiration

Tokens are valid for a configurable duration (default: 30 days). After expiration, clients must re-authenticate.

### Authorization Levels

The API has two authorization levels:

| Level      | Description                                          |
|------------|------------------------------------------------------|
| **User**   | Can access their own data and participate in occasions |
| **Admin**  | Can manage all users, occasions, and perform draws    |

## Common Patterns

### Pagination

The API does not currently implement pagination. All list endpoints return complete result sets.

### Filtering

Some endpoints support query parameters for filtering:

```bash
# Get occasions for a specific participant
GET /api/occasion?idParticipant=USER_ID
```

### Soft Deletes

Gift ideas use soft deletes - they are marked as deleted but remain in the database:
- Active ideas: `dateSuppression` is `null`
- Deleted ideas: `dateSuppression` contains deletion timestamp

### Date Format

All dates are in ISO 8601 format with timezone:

```
2025-12-09T19:30:00+01:00
```

## Error Handling

### HTTP Status Codes

| Code | Meaning                     | Usage                                    |
|------|-----------------------------|------------------------------------------|
| 200  | OK                          | Successful request                       |
| 400  | Bad Request                 | Invalid parameters or missing required fields |
| 401  | Unauthorized                | Missing or invalid authentication token  |
| 403  | Forbidden                   | Insufficient permissions for the resource |
| 404  | Not Found                   | Resource does not exist                  |
| 500  | Internal Server Error       | Server-side error                        |

### Error Response Format

All errors return JSON with a `message` field:

```json
{
  "message": "identifiants invalides"
}
```

**Common error messages:**

- `"identifiants invalides"` - Invalid username or password
- `"Unauthorized"` - Missing or invalid token
- `"Forbidden"` - Insufficient permissions
- `"Not Found"` - Resource doesn't exist
- `"utilisateur inconnu"` - User not found
- `"occasion inconnue"` - Occasion not found

## API Endpoints

### Authentication Endpoints

#### POST /api/connexion

**Purpose:** Authenticate a user and receive an authentication token.

**Authentication:** None required

**Parameters:**

| Parameter     | Type   | Required | Description           |
|---------------|--------|----------|-----------------------|
| `identifiant` | string | Yes      | Username              |
| `mdp`         | string | Yes      | Password              |

**Example:**
```bash
curl -X POST \
-d identifiant=alice \
-d mdp=mdpalice \
$BASE_URL/connexion
```

**Success Response (200 OK):**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "utilisateur": {
    "id": 1,
    "nom": "Alice",
    "genre": "F",
    "admin": true
  }
}
```

**Error Response (400 Bad Request):**
```json
{
  "message": "identifiants invalides"
}
```

---

### User Endpoints

#### GET /api/utilisateur

**Purpose:** List all users (admin only) or redirect to own profile (regular users).

**Authentication:** Required

**Authorization:**
- **Regular users**: Returns their own profile
- **Admins**: Returns all users

**Example (Admin):**
```bash
curl -u TOKEN: \
$BASE_URL/utilisateur
```

**Success Response (200 OK):**
```json
[
  {
    "admin": true,
    "email": "alice@localhost",
    "genre": "F",
    "id": 1,
    "identifiant": "alice",
    "nom": "Alice",
    "prefNotifIdees": "N"
  },
  {
    "admin": false,
    "email": "bob@localhost",
    "genre": "M",
    "id": 2,
    "identifiant": "bob",
    "nom": "Bob",
    "prefNotifIdees": "I"
  }
]
```

#### POST /api/utilisateur

**Purpose:** Create a new user (admin only).

**Authentication:** Required (Admin)

**Authorization:** Admin only

**Parameters:**

| Parameter    | Type    | Required | Description                              |
|--------------|---------|----------|------------------------------------------|
| `identifiant`| string  | Yes      | Unique username for login                |
| `email`      | string  | Yes      | User's email address                     |
| `nom`        | string  | Yes      | Display name                             |
| `genre`      | string  | Yes      | Gender: `M` (Masculin) or `F` (Feminin)  |
| `admin`      | boolean | No       | Admin privileges (default: false)        |
| `prefNotifIdees` | string | No   | Notification preference: `N`, `I`, or `Q` (default: `N`) |

**Example:**
```bash
curl -u TOKEN: \
-d identifiant=newuser \
-d email=newuser@example.com \
-d nom="New User" \
-d genre=F \
-d admin=0 \
-d prefNotifIdees=N \
$BASE_URL/utilisateur
```

**Success Response (200 OK):**
```json
{
  "admin": false,
  "email": "newuser@example.com",
  "genre": "F",
  "id": 3,
  "identifiant": "newuser",
  "nom": "New User",
  "prefNotifIdees": "N"
}
```

**Notes:**
- Password is automatically generated and emailed to the user
- Username must be unique
- Email notification is sent with login credentials

#### GET /api/utilisateur/:idUtilisateur

**Purpose:** Get detailed information about a specific user.

**Authentication:** Required

**Authorization:**
- **Regular users**: Can only view their own profile
- **Admins**: Can view any user

**Parameters:**

| Parameter       | Type | Location | Description |
|-----------------|------|----------|-------------|
| `idUtilisateur` | int  | URL      | User ID     |

**Example:**
```bash
curl -u TOKEN: \
$BASE_URL/utilisateur/1
```

**Success Response (200 OK):**
```json
{
  "admin": true,
  "email": "alice@localhost",
  "genre": "F",
  "id": 1,
  "identifiant": "alice",
  "nom": "Alice",
  "prefNotifIdees": "N"
}
```

**Error Response (403 Forbidden):**
```json
{
  "message": "pas utilisateur ni admin"
}
```

#### PUT /api/utilisateur/:idUtilisateur

**Purpose:** Update user information.

**Authentication:** Required

**Authorization:**
- **Regular users**: Can only update their own profile (cannot change admin status)
- **Admins**: Can update any user

**Parameters:**

| Parameter       | Type    | Location | Required | Description                     |
|-----------------|---------|----------|----------|---------------------------------|
| `idUtilisateur` | int     | URL      | Yes      | User ID                         |
| `nom`           | string  | Body     | No       | Display name                    |
| `email`         | string  | Body     | No       | Email address                   |
| `genre`         | string  | Body     | No       | Gender: `M` or `F`              |
| `admin`         | boolean | Body     | No       | Admin status (admin only)       |
| `prefNotifIdees`| string  | Body     | No       | Notification preference: `N`, `I`, or `Q` |
| `mdp`           | string  | Body     | No       | New password (own profile only) |

**Example (Update email):**
```bash
curl -u TOKEN: \
-d email=newemail@example.com \
-X PUT \
$BASE_URL/utilisateur/2
```

**Example (Change password):**
```bash
curl -u TOKEN: \
-d mdp=newpassword123 \
-X PUT \
$BASE_URL/utilisateur/2
```

**Success Response (200 OK):**
```json
{
  "admin": false,
  "email": "newemail@example.com",
  "genre": "M",
  "id": 2,
  "identifiant": "bob",
  "nom": "Bob",
  "prefNotifIdees": "I"
}
```

**Notes:**
- All parameters are optional - only include fields to change
- Cannot change `identifiant` (username) after creation
- Regular users cannot modify `admin` field
- Users can only change their own password

#### POST /api/utilisateur/:idUtilisateur/reinitmdp

**Purpose:** Reset a user's password (admin only).

**Authentication:** Required (Admin)

**Authorization:** Admin only

**Parameters:**

| Parameter       | Type | Location | Description |
|-----------------|------|----------|-------------|
| `idUtilisateur` | int  | URL      | User ID     |

**Example:**
```bash
curl -u TOKEN: \
-X POST \
$BASE_URL/utilisateur/2/reinitmdp
```

**Success Response (200 OK):**
```json
{
  "message": "Mot de passe réinitialisé"
}
```

**Notes:**
- New password is automatically generated
- User receives email with new password
- User should change password after first login

---

### Occasion Endpoints

#### GET /api/occasion

**Purpose:** List occasions.

**Authentication:** Required

**Authorization:**
- **Regular users**: Returns only occasions they participate in
- **Admins**: Returns all occasions, or filtered by participant

**Query Parameters:**

| Parameter       | Type | Required | Description                              |
|-----------------|------|----------|------------------------------------------|
| `idParticipant` | int  | No       | Filter by participant (admin only)       |

**Example (Regular user - own occasions):**
```bash
curl -u TOKEN: \
$BASE_URL/occasion
```

**Example (Admin - filter by participant):**
```bash
curl -u TOKEN: \
$BASE_URL/occasion?idParticipant=2
```

**Success Response (200 OK):**
```json
[
  {
    "id": 1,
    "date": "2025-12-25T00:00:00+01:00",
    "titre": "Noël 2025"
  },
  {
    "id": 2,
    "date": "2026-01-15T00:00:00+01:00",
    "titre": "Anniversaire Alice"
  }
]
```

#### POST /api/occasion

**Purpose:** Create a new occasion (admin only).

**Authentication:** Required (Admin)

**Authorization:** Admin only

**Parameters:**

| Parameter | Type   | Required | Description                           |
|-----------|--------|----------|---------------------------------------|
| `titre`   | string | Yes      | Occasion name                         |
| `date`    | string | Yes      | Occasion date (ISO 8601 format)       |

**Example:**
```bash
curl -u TOKEN: \
-d titre="Christmas 2026" \
-d date="2026-12-25T00:00:00+01:00" \
$BASE_URL/occasion
```

**Success Response (200 OK):**
```json
{
  "id": 3,
  "date": "2026-12-25T00:00:00+01:00",
  "titre": "Christmas 2026"
}
```

#### GET /api/occasion/:idOccasion

**Purpose:** Get detailed information about an occasion.

**Authentication:** Required

**Authorization:**
- **Regular users**: Can only view occasions they participate in
- **Admins**: Can view any occasion

**Parameters:**

| Parameter    | Type | Location | Description  |
|--------------|------|----------|--------------|
| `idOccasion` | int  | URL      | Occasion ID  |

**Example:**
```bash
curl -u TOKEN: \
$BASE_URL/occasion/1
```

**Success Response (200 OK):**
```json
{
  "id": 1,
  "date": "2025-12-25T00:00:00+01:00",
  "titre": "Noël 2025",
  "participants": [
    {
      "genre": "F",
      "id": 1,
      "nom": "Alice"
    },
    {
      "genre": "M",
      "id": 2,
      "nom": "Bob"
    }
  ],
  "resultats": [
    {
      "idQuiOffre": 1,
      "idQuiRecoit": 2
    },
    {
      "idQuiOffre": 2,
      "idQuiRecoit": 1
    }
  ]
}
```

**Notes:**
- `resultats` contains draw results (who gives to whom)
- Regular users only see their own giving assignment
- Admins see all draw results

#### PUT /api/occasion/:idOccasion

**Purpose:** Update an occasion (admin only).

**Authentication:** Required (Admin)

**Authorization:** Admin only

**Parameters:**

| Parameter    | Type   | Location | Required | Description                     |
|--------------|--------|----------|----------|---------------------------------|
| `idOccasion` | int    | URL      | Yes      | Occasion ID                     |
| `titre`      | string | Body     | No       | Occasion name                   |
| `date`       | string | Body     | No       | Occasion date (ISO 8601 format) |

**Example:**
```bash
curl -u TOKEN: \
-d titre="Updated Title" \
-X PUT \
$BASE_URL/occasion/1
```

**Success Response (200 OK):**
```json
{
  "id": 1,
  "date": "2025-12-25T00:00:00+01:00",
  "titre": "Updated Title"
}
```

#### POST /api/occasion/:idOccasion/participant

**Purpose:** Add a participant to an occasion (admin only).

**Authentication:** Required (Admin)

**Authorization:** Admin only

**Parameters:**

| Parameter       | Type | Location | Required | Description    |
|-----------------|------|----------|----------|----------------|
| `idOccasion`    | int  | URL      | Yes      | Occasion ID    |
| `idUtilisateur` | int  | Body     | Yes      | User ID to add |

**Example:**
```bash
curl -u TOKEN: \
-d idUtilisateur=3 \
$BASE_URL/occasion/1/participant
```

**Success Response (200 OK):**
```json
{
  "id": 1,
  "date": "2025-12-25T00:00:00+01:00",
  "titre": "Noël 2025",
  "participants": [
    {
      "genre": "F",
      "id": 1,
      "nom": "Alice"
    },
    {
      "genre": "M",
      "id": 2,
      "nom": "Bob"
    },
    {
      "genre": "F",
      "id": 3,
      "nom": "Charlie"
    }
  ]
}
```

**Notes:**
- Participant receives email notification
- Cannot add same participant twice

---

### Gift Idea Endpoints

#### GET /api/idee

**Purpose:** List gift ideas for a specific user in an occasion.

**Authentication:** Required

**Query Parameters:**

| Parameter       | Type | Required | Description                    |
|-----------------|------|----------|--------------------------------|
| `idUtilisateur` | int  | Yes      | User the ideas are for         |
| `idOccasion`    | int  | No       | Filter by occasion (optional)  |

**Example:**
```bash
curl -u TOKEN: \
"$BASE_URL/idee?idUtilisateur=2&idOccasion=1"
```

**Success Response (200 OK):**
```json
{
  "utilisateur": {
    "genre": "M",
    "id": 2,
    "nom": "Bob"
  },
  "idees": [
    {
      "id": 1,
      "description": "une canne à pêche",
      "auteur": {
        "genre": "F",
        "id": 1,
        "nom": "Alice"
      },
      "dateProposition": "2025-12-06T10:30:00+01:00"
    },
    {
      "id": 2,
      "description": "un livre de recettes",
      "auteur": {
        "genre": "F",
        "id": 1,
        "nom": "Alice"
      },
      "dateProposition": "2025-12-07T15:20:00+01:00",
      "dateSuppression": "2025-12-08T09:00:00+01:00"
    }
  ]
}
```

**Notes:**
- Ideas with `dateSuppression` are soft-deleted but still visible to admins
- Users cannot see ideas others suggested for them (enforced by business logic)

#### POST /api/idee

**Purpose:** Create a new gift idea.

**Authentication:** Required

**Parameters:**

| Parameter       | Type   | Required | Description                              |
|-----------------|--------|----------|------------------------------------------|
| `idUtilisateur` | int    | Yes      | User the idea is for (can be yourself)   |
| `description`   | string | Yes      | Gift idea description                    |
| `idAuteur`      | int    | Yes      | Author of the idea (usually yourself)    |

**Example:**
```bash
curl -u TOKEN: \
-d idUtilisateur=2 \
-d description="un nouveau vélo" \
-d idAuteur=1 \
$BASE_URL/idee
```

**Success Response (200 OK):**
```json
{
  "id": 3,
  "description": "un nouveau vélo",
  "auteur": {
    "genre": "F",
    "id": 1,
    "nom": "Alice"
  },
  "dateProposition": "2025-12-09T19:30:00+01:00"
}
```

**Notes:**
- Users can suggest ideas for themselves (`idUtilisateur` = `idAuteur`)
- Notifications are sent based on recipient's preferences
- Cannot suggest ideas for users who don't want to see them (enforced by business logic)

#### POST /api/idee/:idIdee/suppression

**Purpose:** Soft delete a gift idea.

**Authentication:** Required

**Authorization:** Only the author can delete their own ideas

**Parameters:**

| Parameter | Type | Location | Description     |
|-----------|------|----------|-----------------|
| `idIdee`  | int  | URL      | Gift idea ID    |

**Example:**
```bash
curl -u TOKEN: \
-X POST \
$BASE_URL/idee/3/suppression
```

**Success Response (200 OK):**
```json
{
  "id": 3,
  "description": "un nouveau vélo",
  "auteur": {
    "genre": "F",
    "id": 1,
    "nom": "Alice"
  },
  "dateProposition": "2025-12-09T19:30:00+01:00",
  "dateSuppression": "2025-12-09T20:00:00+01:00"
}
```

**Notes:**
- This is a **soft delete** - the idea remains in the database with `dateSuppression` set
- Notifications are sent to relevant users
- Only the author can delete their ideas

---

### Exclusion Endpoints

#### GET /api/utilisateur/:idUtilisateur/exclusion

**Purpose:** List exclusions for a user (admin only).

**Authentication:** Required (Admin)

**Authorization:** Admin only

**Parameters:**

| Parameter       | Type | Location | Description |
|-----------------|------|----------|-------------|
| `idUtilisateur` | int  | URL      | User ID     |

**Example:**
```bash
curl -u TOKEN: \
$BASE_URL/utilisateur/1/exclusion
```

**Success Response (200 OK):**
```json
[
  {
    "quiNeDoitPasRecevoir": {
      "genre": "M",
      "id": 2,
      "nom": "Bob"
    }
  }
]
```

**Notes:**
- Returns list of users that the specified user cannot draw
- Used to prevent couples or siblings from drawing each other

#### POST /api/utilisateur/:idUtilisateur/exclusion

**Purpose:** Create an exclusion (admin only).

**Authentication:** Required (Admin)

**Authorization:** Admin only

**Parameters:**

| Parameter                    | Type | Location | Required | Description                          |
|------------------------------|------|----------|----------|--------------------------------------|
| `idUtilisateur`              | int  | URL      | Yes      | User who cannot give (giver)         |
| `idQuiNeDoitPasRecevoir`     | int  | Body     | Yes      | User who cannot receive from giver   |

**Example:**
```bash
curl -u TOKEN: \
-d idQuiNeDoitPasRecevoir=2 \
$BASE_URL/utilisateur/1/exclusion
```

**Success Response (200 OK):**
```json
{
  "quiNeDoitPasRecevoir": {
    "genre": "M",
    "id": 2,
    "nom": "Bob"
  }
}
```

**Notes:**
- Exclusions are typically bidirectional (if A excludes B, also create B excludes A)
- Used by draw algorithm to ensure valid assignments
- Cannot create duplicate exclusions

---

### Draw Endpoints

#### POST /api/occasion/:idOccasion/tirage

**Purpose:** Perform a random draw for an occasion (admin only).

**Authentication:** Required (Admin)

**Authorization:** Admin only

**Parameters:**

| Parameter    | Type | Location | Description |
|--------------|------|----------|-------------|
| `idOccasion` | int  | URL      | Occasion ID |

**Example:**
```bash
curl -u TOKEN: \
-X POST \
$BASE_URL/occasion/1/tirage
```

**Success Response (200 OK):**
```json
[
  {
    "idQuiOffre": 1,
    "idQuiRecoit": 3
  },
  {
    "idQuiOffre": 2,
    "idQuiRecoit": 1
  },
  {
    "idQuiOffre": 3,
    "idQuiRecoit": 2
  }
]
```

**Notes:**
- Generates random draw assignments for all participants
- Respects exclusion rules
- Each participant gives to exactly one other participant
- Each participant receives from exactly one other participant
- Email notifications are sent to all participants
- Cannot perform draw if one already exists (must delete first)

#### POST /api/occasion/:idOccasion/resultat

**Purpose:** Create or update draw results manually (admin only).

**Authentication:** Required (Admin)

**Authorization:** Admin only

**Parameters:**

| Parameter    | Type  | Location | Required | Description                                      |
|--------------|-------|----------|----------|--------------------------------------------------|
| `idOccasion` | int   | URL      | Yes      | Occasion ID                                      |
| `resultats`  | array | Body     | Yes      | Array of result objects                          |

**Result Object Structure:**
```json
{
  "idQuiOffre": int,
  "idQuiRecoit": int
}
```

**Example:**
```bash
curl -u TOKEN: \
-H "Content-Type: application/json" \
-d '{"resultats":[{"idQuiOffre":1,"idQuiRecoit":2},{"idQuiOffre":2,"idQuiRecoit":1}]}' \
$BASE_URL/occasion/1/resultat
```

**Success Response (200 OK):**
```json
{
  "id": 1,
  "date": "2025-12-25T00:00:00+01:00",
  "titre": "Noël 2025",
  "participants": [...],
  "resultats": [
    {
      "idQuiOffre": 1,
      "idQuiRecoit": 2
    },
    {
      "idQuiOffre": 2,
      "idQuiRecoit": 1
    }
  ]
}
```

**Notes:**
- Replaces existing draw results
- Validates that each participant gives and receives exactly once
- Respects exclusion rules
- Sends email notifications to participants

## Using curl for Testing

### Setting Up

Set up environment variables for easier testing:

```bash
# Set base URL (choose one)
export BASE_URL="http://localhost:8080/api"          # Development
# export BASE_URL="https://your-instance.com/api"    # Production

# Set token after logging in
export TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."
```

### Common curl Patterns

**GET request:**
```bash
curl -u $TOKEN: \
$BASE_URL/utilisateur
```

**POST request with form data:**
```bash
curl -u $TOKEN: \
-d param1=value1 \
-d param2=value2 \
$BASE_URL/endpoint
```

**POST request with JSON:**
```bash
curl -u $TOKEN: \
-H "Content-Type: application/json" \
-d '{"key":"value"}' \
$BASE_URL/endpoint
```

**PUT request:**
```bash
curl -u $TOKEN: \
-d param=value \
-X PUT \
$BASE_URL/endpoint
```

**DELETE request:**
```bash
curl -u $TOKEN: \
-X DELETE \
$BASE_URL/endpoint
```

### URL Encoding

Special characters in form data should be URL-encoded:

```bash
# Spaces become %20 or +
-d "nom=John Doe"
-d "nom=John+Doe"
-d "nom=John%20Doe"

# Special characters
-d "email=user%2Btest%40example.com"  # user+test@example.com
```

### Debugging Requests

Add `-v` flag for verbose output:

```bash
curl -v -u $TOKEN: \
$BASE_URL/utilisateur
```

Add `-i` flag to include response headers:

```bash
curl -i -u $TOKEN: \
$BASE_URL/utilisateur
```

### Testing Authentication

Test without token (should return 401):
```bash
curl $BASE_URL/utilisateur
```

Test with invalid token (should return 401):
```bash
curl -u INVALID_TOKEN: \
$BASE_URL/utilisateur
```

### Complete Example Workflow

```bash
# 1. Login and get token
RESPONSE=$(curl -X POST \
  -d identifiant=alice \
  -d mdp=mdpalice \
  $BASE_URL/connexion)

# 2. Extract token (requires jq)
TOKEN=$(echo $RESPONSE | jq -r '.token')

# 3. Use token for subsequent requests
curl -u $TOKEN: \
$BASE_URL/occasion

# 4. Create a new idea
curl -u $TOKEN: \
-d idUtilisateur=2 \
-d description="un livre" \
-d idAuteur=1 \
$BASE_URL/idee
```

## Response Examples

### Successful Login
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZFV0aWxpc2F0ZXVyIjoxLCJhZG1pbiI6dHJ1ZSwiaWF0IjoxNzAyMTI1NjAwLCJleHAiOjE3MDQ3MTc2MDB9.abc123...",
  "utilisateur": {
    "id": 1,
    "nom": "Alice",
    "genre": "F",
    "admin": true
  }
}
```

### User List
```json
[
  {
    "admin": true,
    "email": "alice@localhost",
    "genre": "F",
    "id": 1,
    "identifiant": "alice",
    "nom": "Alice",
    "prefNotifIdees": "N"
  },
  {
    "admin": false,
    "email": "bob@localhost",
    "genre": "M",
    "id": 2,
    "identifiant": "bob",
    "nom": "Bob",
    "prefNotifIdees": "I"
  }
]
```

### Occasion Details
```json
{
  "id": 1,
  "date": "2025-12-25T00:00:00+01:00",
  "titre": "Noël 2025",
  "participants": [
    {
      "genre": "F",
      "id": 1,
      "nom": "Alice"
    },
    {
      "genre": "M",
      "id": 2,
      "nom": "Bob"
    },
    {
      "genre": "M",
      "id": 3,
      "nom": "Charlie"
    }
  ],
  "resultats": [
    {
      "idQuiOffre": 1,
      "idQuiRecoit": 3
    },
    {
      "idQuiOffre": 2,
      "idQuiRecoit": 1
    },
    {
      "idQuiOffre": 3,
      "idQuiRecoit": 2
    }
  ]
}
```

### Gift Ideas List
```json
{
  "utilisateur": {
    "genre": "M",
    "id": 2,
    "nom": "Bob"
  },
  "idees": [
    {
      "id": 1,
      "description": "une canne à pêche",
      "auteur": {
        "genre": "F",
        "id": 1,
        "nom": "Alice"
      },
      "dateProposition": "2025-12-06T10:30:00+01:00"
    },
    {
      "id": 2,
      "description": "des lunettes de soleil",
      "auteur": {
        "genre": "M",
        "id": 3,
        "nom": "Charlie"
      },
      "dateProposition": "2025-12-07T14:15:00+01:00"
    },
    {
      "id": 3,
      "description": "un livre de recettes",
      "auteur": {
        "genre": "F",
        "id": 1,
        "nom": "Alice"
      },
      "dateProposition": "2025-12-07T15:20:00+01:00",
      "dateSuppression": "2025-12-08T09:00:00+01:00"
    }
  ]
}
```

### Error Responses

**Bad Request (400):**
```json
{
  "message": "identifiants invalides"
}
```

**Unauthorized (401):**
```json
{
  "message": "Unauthorized"
}
```

**Forbidden (403):**
```json
{
  "message": "pas utilisateur ni admin"
}
```

**Not Found (404):**
```json
{
  "message": "utilisateur inconnu"
}
```

## Related Documentation

- [Administrator Guide](./admin-guide.md) - Admin workflows using the API
- [Backend Development Guide](./backend-dev.md) - API architecture and implementation
- [Database Documentation](./database.md) - Database schema and entities
- [Testing Guide](./testing.md) - API testing strategies

---

**Questions or Issues?**

- Check the [Administrator Guide](./admin-guide.md) for practical examples
- Review controller implementations in `api/src/Appli/Controller/`
- Consult the [CONTRIBUTING guide](../../CONTRIBUTING.md)
