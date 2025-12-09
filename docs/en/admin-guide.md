# Tkdo Administrator Guide

This guide covers all administrative functions in Tkdo, including user management, occasion creation, draw generation, and API access via command line.

## Table of Contents

- [Administrator Role](#administrator-role)
- [Accessing Admin Functions](#accessing-admin-functions)
- [Authentication](#authentication)
- [User Management](#user-management)
- [Occasion Management](#occasion-management)
- [Exclusion Management](#exclusion-management)
- [Draw Management](#draw-management)
- [API Reference Quick Guide](#api-reference-quick-guide)
- [Best Practices](#best-practices)
- [Troubleshooting](#troubleshooting)

## Administrator Role

### What is an Administrator?

Administrators have special privileges in Tkdo that regular users don't have:

**Extended Access:**
- View any user's profile information
- View all occasions in the system (not just their own)
- View detailed information about any occasion

**Exclusive Operations:**
- Create new user accounts
- Reset user passwords
- Create new occasions
- Modify occasion details
- Add participants to occasions
- Create exclusions between users
- Generate draws (automatic or manual)

**Access Methods:**
- Web interface: Administration page with documentation
- Command-line API: Full control via `curl` commands

### Administrator Account Security

**Important security considerations:**

1. **Change default password immediately** after first login
2. **Use a strong, unique password** for administrator accounts
3. **Keep authentication tokens secure** - they provide full admin access
4. **Don't share tokens** - generate new ones for each session if needed
5. **Log out when done** - tokens expire with sessions for security

## Accessing Admin Functions

### Web Interface

1. Log in with an administrator account
2. Navigate to the **Administration** page from the menu
3. View API documentation and examples with your current session token

**The admin page shows:**
- Your current authentication token
- Complete API documentation
- curl command examples
- Response format examples

### Command Line API Access

All administrative functions are available via the REST API using `curl`.

**Two authentication options:**

1. **Use your current session token** (shown on admin page)
2. **Create a new token** via the API

See [Authentication](#authentication) section for details.

## Authentication

### Setting Up Environment Variables

For easier curl command usage, set up environment variables:

```bash
# Set base URL (choose appropriate one for your environment)
export BASE_URL="https://your-tkdo-instance.com/api"    # Production
# export BASE_URL="http://localhost:8080/api"            # Development

# Set token (after obtaining it)
export TOKEN="YOUR_TOKEN_HERE"
```

### Using Your Current Session Token

**On the Administration page**, your current authentication token is displayed. Use it with curl:

```bash
curl -u $TOKEN: \
$BASE_URL/endpoint
```

**Important:** The colon (`:`) after the token is required. It tells curl to use HTTP Basic Authentication with the token as the username and no password.

**Token properties:**
- Valid only for your current session
- Expires when you log out
- Expires after period of inactivity
- Specific to your user account

### Creating a New Token

Generate a new authentication token via the API:

```bash
curl \
-d identifiant='your_username' \
-d mdp='your_password' \
-X POST $BASE_URL/connexion
```

**Response:**
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
  "utilisateur": {
    "id": 1,
    "nom": "Your Name",
    "admin": true
  }
}
```

**Use the token** in subsequent requests:
```bash
curl -u eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...: \
$BASE_URL/endpoint
```

## User Management

### Listing All Users

**Purpose:** View all users in the system

```bash
curl -u $TOKEN: \
$BASE_URL/utilisateur
```

**Response:**
```json
[
  {
    "email": "admin@tkdo.org",
    "admin": true,
    "genre": "M",
    "id": 1,
    "identifiant": "admin",
    "nom": "Administrator",
    "prefNotifIdees": "N"
  },
  {
    "email": "alice@example.com",
    "admin": false,
    "genre": "F",
    "id": 2,
    "identifiant": "alice",
    "nom": "Alice",
    "prefNotifIdees": "I"
  }
]
```

**Field descriptions:**

| Field              | Description                                        | Values                    |
|--------------------|----------------------------------------------------|-----------------------------|
| `id`               | Unique user identifier                             | Integer                     |
| `identifiant`      | Username for login                                 | String                      |
| `nom`              | Display name                                       | String                      |
| `email`            | Email address for notifications                    | String (email format)       |
| `genre`            | Gender                                             | "M" (male) or "F" (female)  |
| `admin`            | Administrator privileges                           | true or false               |
| `prefNotifIdees`   | Gift idea notification preference                  | "N", "I", or "Q"            |

### Creating a User Account

**Purpose:** Create a new user account

```bash
curl -u $TOKEN: \
-d identifiant='newuser' \
-d email='newuser@example.com' \
-d nom='New User' \
-d genre=F \
-d admin=0 \
-d prefNotifIdees=N \
-X POST $BASE_URL/utilisateur
```

**Parameters:**

| Parameter          | Required | Description                                  | Example Values              |
|--------------------|----------|----------------------------------------------|-----------------------------|
| `identifiant`      | Yes      | Username (must be unique)                    | "alice", "bob123"           |
| `email`            | Yes      | Email address (must be valid)                | "user@example.com"          |
| `nom`              | Yes      | Display name                                 | "Alice Smith"               |
| `genre`            | Yes      | Gender                                       | "M" or "F"                  |
| `admin`            | No       | Admin privileges (default: false)            | 0 or 1, false or true       |
| `prefNotifIdees`   | No       | Notification preference (default: "N")       | "N", "I", or "Q"            |

**Response:**
```json
{
  "email": "newuser@example.com",
  "admin": false,
  "genre": "F",
  "id": 3,
  "identifiant": "newuser",
  "nom": "New User",
  "prefNotifIdees": "N"
}
```

**Important notes:**
- Password is automatically generated and emailed to the user
- User will receive an account creation email
- User should change the password after first login
- Username (`identifiant`) must be unique in the system

### Viewing a User

**Purpose:** Get detailed information about a specific user

```bash
curl -u $TOKEN: \
$BASE_URL/utilisateur/USER_ID
```

**Example:**
```bash
curl -u $TOKEN: \
$BASE_URL/utilisateur/2
```

**Response:** Same format as listing users, but for a single user.

**Use cases:**
- Verify user details before modifications
- Check user's current settings
- Get user ID for other operations

### Modifying a User

**Purpose:** Update user information

```bash
curl -u $TOKEN: \
-d nom='Updated Name' \
-d email='newemail@example.com' \
-d genre=F \
-d admin=1 \
-d prefNotifIdees=I \
-X PUT $BASE_URL/utilisateur/USER_ID
```

**Important notes:**
- **All parameters are optional** - only include fields you want to change
- **Cannot change password** of other users (use password reset instead)
- **Cannot change username** (`identifiant`) once created
- **URL encoding required** for special characters (`+` becomes `%2B`, etc.)

**Examples:**

Change only the email:
```bash
curl -u $TOKEN: \
-d email='updated@example.com' \
-X PUT $BASE_URL/utilisateur/2
```

Grant admin privileges:
```bash
curl -u $TOKEN: \
-d admin=1 \
-X PUT $BASE_URL/utilisateur/2
```

Change notification preference:
```bash
curl -u $TOKEN: \
-d prefNotifIdees=Q \
-X PUT $BASE_URL/utilisateur/2
```

### Resetting a User's Password

**Purpose:** Generate a new password for a user who forgot theirs

```bash
curl -u $TOKEN: \
-X POST $BASE_URL/utilisateur/USER_ID/reinitmdp
```

**Example:**
```bash
curl -u $TOKEN: \
-X POST $BASE_URL/utilisateur/2/reinitmdp
```

**Response:**
```json
{
  "email": "alice@example.com",
  "admin": false,
  "genre": "F",
  "id": 2,
  "identifiant": "alice",
  "nom": "Alice",
  "prefNotifIdees": "I"
}
```

**What happens:**
1. A new random password is generated
2. The password is emailed to the user's registered email
3. User receives a password reset notification email
4. User should change the password after logging in

**Security note:** Even administrators cannot see or set user passwords. This ensures password security.

## Occasion Management

### Listing All Occasions

**Purpose:** View all occasions in the system

```bash
curl -u $TOKEN: \
$BASE_URL/occasion
```

**Response:**
```json
[
  {
    "id": 1,
    "date": "2025-12-25",
    "titre": "Christmas 2025"
  },
  {
    "id": 2,
    "date": "2026-12-25",
    "titre": "Christmas 2026"
  }
]
```

### Creating an Occasion

**Purpose:** Create a new gift exchange occasion

```bash
curl -u $TOKEN: \
-d titre='New Years 2026' \
-d date='2026-01-01' \
-X POST $BASE_URL/occasion
```

**Parameters:**

| Parameter   | Required | Description                    | Format              |
|-------------|----------|--------------------------------|---------------------|
| `titre`     | Yes      | Occasion name                  | String              |
| `date`      | Yes      | Occasion date                  | YYYY-MM-DD          |

**Response:**
```json
{
  "id": 3,
  "date": "2026-01-01",
  "titre": "New Years 2026"
}
```

**Notes:**
- Occasion is created without participants initially
- Add participants separately (see below)
- Date determines if occasion is upcoming or past

### Viewing an Occasion

**Purpose:** Get detailed information about an occasion

```bash
curl -u $TOKEN: \
$BASE_URL/occasion/OCCASION_ID
```

**Response:**
```json
{
  "id": 1,
  "date": "2025-12-25",
  "titre": "Christmas 2025",
  "participants": [
    {
      "genre": "F",
      "id": 2,
      "nom": "Alice"
    },
    {
      "genre": "M",
      "id": 3,
      "nom": "Bob"
    }
  ],
  "resultats": [
    {
      "idQuiOffre": 2,
      "idQuiRecoit": 3
    },
    {
      "idQuiOffre": 3,
      "idQuiRecoit": 2
    }
  ]
}
```

**Information provided:**
- Basic occasion details (id, date, title)
- List of all participants
- Draw results (if draw has been performed)

### Modifying an Occasion

**Purpose:** Update occasion details

```bash
curl -u $TOKEN: \
-d titre='Updated Title' \
-d date='2026-01-02' \
-X PUT $BASE_URL/occasion/OCCASION_ID
```

**Notes:**
- All parameters are optional
- Cannot modify participants or draw results via this route
- Use dedicated routes for participants and draws

### Adding a Participant

**Purpose:** Add a user to an occasion

```bash
curl -u $TOKEN: \
-d idParticipant=USER_ID \
-X POST $BASE_URL/occasion/OCCASION_ID/participant
```

**Example:**
```bash
curl -u $TOKEN: \
-d idParticipant=4 \
-X POST $BASE_URL/occasion/1/participant
```

**Response:**
```json
{
  "genre": "F",
  "id": 4,
  "nom": "Carol"
}
```

**What happens:**
- User is added to the occasion's participant list
- User receives an email notification about the new occasion
- User can now add gift ideas for other participants
- User will be included in future draws

**Notes:**
- User must exist in the system
- User cannot be added twice to the same occasion
- User receives "new occasion participation" email

## Exclusion Management

### Understanding Exclusions

**Exclusions** prevent specific people from drawing each other in the gift exchange.

**Common use cases:**
- Couples (spouses, partners)
- Siblings
- Close family members
- Anyone who regularly exchanges gifts outside the occasion

**How it works:**
- Exclusion is **directional**: A cannot give to B
- For mutual exclusion, create two: A→B and B→A
- Draw algorithm automatically respects all exclusions
- Exclusions are **user-level**, not occasion-specific

### Listing a User's Exclusions

**Purpose:** View who a user cannot draw

```bash
curl -u $TOKEN: \
$BASE_URL/utilisateur/USER_ID/exclusion
```

**Response:**
```json
[
  {
    "quiNeDoitPasRecevoir": {
      "genre": "M",
      "id": 3,
      "nom": "Bob"
    }
  }
]
```

**Interpretation:** User (Alice, id=2) cannot draw Bob (id=3) in any draw.

### Creating an Exclusion

**Purpose:** Define who a user cannot give gifts to

```bash
curl -u $TOKEN: \
-d idQuiNeDoitPasRecevoir=TARGET_USER_ID \
-X POST $BASE_URL/utilisateur/USER_ID/exclusion
```

**Example:** Prevent Alice (id=2) from drawing Bob (id=3):
```bash
curl -u $TOKEN: \
-d idQuiNeDoitPasRecevoir=3 \
-X POST $BASE_URL/utilisateur/2/exclusion
```

**Response:**
```json
{
  "quiNeDoitPasRecevoir": {
    "genre": "M",
    "id": 3,
    "nom": "Bob"
  }
}
```

**Creating mutual exclusions:**

To prevent both Alice and Bob from drawing each other:

```bash
# Alice cannot draw Bob
curl -u $TOKEN: \
-d idQuiNeDoitPasRecevoir=3 \
-X POST $BASE_URL/utilisateur/2/exclusion

# Bob cannot draw Alice
curl -u $TOKEN: \
-d idQuiNeDoitPasRecevoir=2 \
-X POST $BASE_URL/utilisateur/3/exclusion
```

**Notes:**
- Both users must exist in the system
- Cannot create duplicate exclusions
- Exclusions apply to all future draws
- Exclusions affect draw algorithm complexity

## Draw Management

### Understanding Draws

A **draw** (tirage) randomly assigns who gives gifts to whom.

**Draw rules:**
- Each participant gives to exactly one person
- Each participant receives from exactly one person
- No one can draw themselves
- Respects user exclusions
- Avoids repeat assignments from past occasions (when possible)

**Draw states:**
- **Not started:** No draw performed yet
- **In progress:** Should not happen (atomic operation)
- **Completed:** Draw results exist and are valid
- **Failed:** Algorithm couldn't find valid assignment (rare)

### Automatic Draw Generation

**Purpose:** Generate a random, valid draw for an occasion

```bash
curl -u $TOKEN: \
-d force=0 \
-d nbMaxIter=10 \
-X POST $BASE_URL/occasion/OCCASION_ID/tirage
```

**Parameters:**

| Parameter     | Required | Description                                    | Default | Values           |
|---------------|----------|------------------------------------------------|---------|------------------|
| `force`       | No       | Overwrite existing draw                        | 0       | 0, 1, false, true|
| `nbMaxIter`   | No       | Max algorithm iterations                       | 10      | Integer (1-100)  |

**Response:**
```json
{
  "id": 1,
  "date": "2025-12-25",
  "titre": "Christmas 2025",
  "participants": [
    {"genre": "F", "id": 2, "nom": "Alice"},
    {"genre": "M", "id": 3, "nom": "Bob"},
    {"genre": "F", "id": 4, "nom": "Carol"}
  ],
  "resultats": [
    {"idQuiOffre": 2, "idQuiRecoit": 3},
    {"idQuiOffre": 3, "idQuiRecoit": 4},
    {"idQuiOffre": 4, "idQuiRecoit": 2}
  ]
}
```

**What happens:**
1. Algorithm generates valid random assignment
2. Results are saved to database
3. All participants receive email notifications
4. Emails reveal who each person should give to
5. Draw results appear on occasion page

**Requirements:**
- Occasion must not be in the past (date hasn't passed yet)
- No existing draw, unless `force=1`
- At least 2 participants
- Valid assignment must be possible given exclusions

**Force parameter:**

Use `force=1` to overwrite an existing draw:
```bash
curl -u $TOKEN: \
-d force=1 \
-X POST $BASE_URL/occasion/1/tirage
```

**When to use force:**
- Mistake in previous draw
- Participant added/removed
- Exclusions changed
- Draw failed and you want to retry

**Warning:** Forcing a new draw will:
- Delete previous results
- Send new notification emails to everyone
- May cause confusion if people already saw their assignments

### Manual Draw Entry

**Purpose:** Enter draw results from an external draw (paper slips, etc.)

```bash
curl -u $TOKEN: \
-d idQuiOffre=GIVER_USER_ID \
-d idQuiRecoit=RECEIVER_USER_ID \
-X POST $BASE_URL/occasion/OCCASION_ID/resultat
```

**Example:** Alice (id=2) gives to Bob (id=3):
```bash
curl -u $TOKEN: \
-d idQuiOffre=2 \
-d idQuiRecoit=3 \
-X POST $BASE_URL/occasion/1/resultat
```

**Response:**
```json
{
  "idQuiOffre": 2,
  "idQuiRecoit": 3
}
```

**Requirements:**
- Both users must be participants in the occasion
- Each participant can only give once
- Each participant can only receive once
- Cannot give to yourself

**Use cases:**
- Draw was done offline (paper, hat, etc.)
- External tool used for draw
- Special circumstances requiring manual assignment

**Process:**
1. Perform draw externally
2. Enter each result one by one via API
3. Verify all participants have assignments
4. Participants won't receive automatic notifications (manual entry)

### Draw Troubleshooting

**Draw fails with "Algorithm couldn't find valid assignment":**

Possible causes:
1. **Exclusions too restrictive** - Too many exclusions making assignment impossible
2. **Too few participants** - Need at least 2 participants
3. **Circular exclusions** - Exclusions create impossible constraint loop

**Solutions:**
- Review and remove unnecessary exclusions
- Add more participants to occasion
- Increase `nbMaxIter` parameter (default is 10)
- Use `force=1` to overwrite and retry

**Example with increased iterations:**
```bash
curl -u $TOKEN: \
-d nbMaxIter=50 \
-X POST $BASE_URL/occasion/1/tirage
```

## API Reference Quick Guide

### Base URL

Replace with your Tkdo instance URL:
```
https://your-tkdo-instance.com/api
```

### Authentication

All requests require authentication:
```bash
curl -u $TOKEN: \
[API_URL]
```

### HTTP Methods

| Method   | Purpose                    | Example Use Case              |
|----------|----------------------------|-------------------------------|
| `GET`    | Retrieve data              | List users, view occasion     |
| `POST`   | Create new resource        | Create user, add participant  |
| `PUT`    | Update existing resource   | Modify user, update occasion  |
| `DELETE` | Delete resource            | (Currently not used)          |

### Common Patterns

**Passing parameters:**
```bash
-d parameter='value' \
-d another='value'
```

**Specifying HTTP method:**
```bash
-X POST  # or PUT, DELETE
```

**Multiple parameters:**
```bash
curl -u $TOKEN: \
-d param1='value1' \
-d param2='value2' \
-d param3='value3' \
-X POST [URL]
```

### Complete Route List

| Endpoint                                      | Method | Purpose                          | Admin Only |
|-----------------------------------------------|--------|----------------------------------|------------|
| `/connexion`                                  | POST   | Create authentication token      | No         |
| `/connexion`                                  | DELETE | Logout                           | No         |
| `/utilisateur`                                | GET    | List all users                   | Yes        |
| `/utilisateur`                                | POST   | Create user                      | Yes        |
| `/utilisateur/:id`                            | GET    | View user (any user if admin)    | Partial    |
| `/utilisateur/:id`                            | PUT    | Modify user                      | Partial    |
| `/utilisateur/:id/reinitmdp`                  | POST   | Reset password                   | Yes        |
| `/utilisateur/:id/exclusion`                  | GET    | List exclusions                  | Yes        |
| `/utilisateur/:id/exclusion`                  | POST   | Create exclusion                 | Yes        |
| `/occasion`                                   | GET    | List occasions (all if admin)    | Partial    |
| `/occasion`                                   | POST   | Create occasion                  | Yes        |
| `/occasion/:id`                               | GET    | View occasion (any if admin)     | Partial    |
| `/occasion/:id`                               | PUT    | Modify occasion                  | Yes        |
| `/occasion/:id/participant`                   | POST   | Add participant                  | Yes        |
| `/occasion/:id/tirage`                        | POST   | Generate draw                    | Yes        |
| `/occasion/:id/resultat`                      | POST   | Add manual draw result           | Yes        |
| `/idee`                                       | GET    | List ideas                       | No         |
| `/idee`                                       | POST   | Create idea                      | No         |
| `/idee/:id/suppression`                       | POST   | Delete idea                      | No         |

## Best Practices

### User Account Management

**Creating accounts:**
- Use descriptive usernames (first names or nicknames)
- Always provide valid email addresses
- Set notification preferences based on user preference
- Create accounts well before occasions start

**Password management:**
- Instruct users to change default passwords immediately
- Reset passwords promptly when users forget them
- Never share or reuse administrator passwords

**Admin privileges:**
- Only grant admin rights when necessary
- Regularly review who has admin access
- Consider having multiple admins for backup

### Occasion Planning

**Before the occasion:**
1. Create the occasion with correct date
2. Add all participants
3. Set up exclusions for couples/families
4. Verify all participants are added
5. Test with a small draw first (if possible)

**Performing draws:**
- Do draws at least a few days before the occasion
- Check that all participants received notification emails
- Verify no error messages during draw generation
- Keep a backup record of draw results

**After the occasion:**
- Don't delete occasions (history is valuable)
- Review feedback for next year
- Update exclusions if family situations changed

### API Usage

**Security:**
- Don't commit tokens to version control
- Use HTTPS for all API calls (required)
- Log out or invalidate tokens when done
- Rotate tokens regularly for automated scripts

**Error handling:**
- Check response status codes
- Read error messages carefully
- Validate data before sending requests
- Keep backups before making bulk changes

**Automation:**
- Consider scripts for bulk operations
- Test scripts on a non-production instance first
- Document your scripts for future reference
- Handle errors gracefully in scripts

## Troubleshooting

### Authentication Issues

**Problem:** "Authorization header required" or "Invalid token"

**Solutions:**
1. Verify token is correct (copy-paste carefully)
2. Ensure colon (`:`) is after token in curl command
3. Check if session expired (get new token)
4. Verify you're using an admin account
5. Try creating a fresh token via API

**Example of correct authentication:**
```bash
curl -u eyJ0eXAiOiJKV1QiLCJh...: \
https://your-instance.com/api/utilisateur
```

### User Creation Fails

**Problem:** "Email invalide" or "Identifiant déjà utilisé"

**Solutions:**
- Verify email format is correct (user@domain.com)
- Check username isn't already taken (list users first)
- Ensure all required fields are provided
- Check for typos in parameter names

**Verify username availability:**
```bash
curl -u $TOKEN: \
https://your-instance.com/api/utilisateur | grep 'desired_username'
```

### Draw Generation Fails

**Problem:** "Tirage échoué" or "Algorithm couldn't find valid assignment"

**Possible causes:**
1. Too many exclusions creating impossible constraints
2. Insufficient participants (need at least 2)
3. Circular exclusion pattern

**Debugging steps:**

1. **Check participant count:**
   ```bash
   curl -u $TOKEN: \
   https://your-instance.com/api/occasion/OCCASION_ID
   ```

2. **Review exclusions:**
   ```bash
   # For each participant
   curl -u $TOKEN: \
   https://your-instance.com/api/utilisateur/USER_ID/exclusion
   ```

3. **Try with more iterations:**
   ```bash
   curl -u $TOKEN: \
   -d nbMaxIter=100 \
   -X POST https://your-instance.com/api/occasion/OCCASION_ID/tirage
   ```

4. **Temporarily remove exclusions** (if possible) and try again

### Email Notifications Not Sent

**Problem:** Users not receiving emails

**Check:**
1. Verify user email addresses are correct
2. Check server email configuration (TKDO_MAILER_* variables)
3. Ask users to check spam/junk folders
4. Test with your own email first
5. Check server logs for email errors

**View user email:**
```bash
curl -u $TOKEN: \
https://your-instance.com/api/utilisateur/USER_ID
```

### curl Command Doesn't Work

**Problem:** Command fails or gives unexpected results

**Common issues:**

1. **Missing backslash for line continuation:**
   ```bash
   # Wrong:
   curl -u $TOKEN:
   -d param='value'

   # Correct:
   curl -u $TOKEN: \
   -d param='value'
   ```

2. **URL encoding needed:**
   ```bash
   # Wrong (+ in value):
   -d name='John+Smith'

   # Correct:
   -d name='John%2BSmith'
   ```

3. **Quotes needed for spaces:**
   ```bash
   # Wrong:
   -d name=John Smith

   # Correct:
   -d name='John Smith'
   ```

4. **HTTP method not specified:**
   ```bash
   # Wrong (defaults to GET):
   curl -u $TOKEN: \
   -d param='value' \
   [URL]

   # Correct:
   curl -u $TOKEN: \
   -d param='value' \
   -X POST [URL]
   ```

### Getting More Help

If issues persist:

1. **Check server logs** - May contain detailed error messages
2. **Review API responses** - Error messages often indicate the problem
3. **Test in isolation** - Try simplest possible request first
4. **Verify prerequisites** - Ensure all requirements are met
5. **Contact support** - Provide error messages and steps to reproduce

---

## Quick Reference

### Notification Preference Codes

| Code   | Meaning                  | Behavior                                      |
|--------|--------------------------|-----------------------------------------------|
| **N**  | None (Aucune)            | No gift idea notifications                    |
| **I**  | Instant (Instantanée)    | Immediate email for each change               |
| **Q**  | Daily (Quotidienne)      | Daily summary email                           |

### Gender Codes

| Code   | Meaning                  |
|--------|--------------------------|
| **M**  | Male (Masculin)          |
| **F**  | Female (Féminin)         |

### Common HTTP Status Codes

| Code   | Meaning                  | Typical Cause                                 |
|--------|--------------------------|-----------------------------------------------|
| **200**| OK                       | Request successful                            |
| **400**| Bad Request              | Invalid parameters or constraint violation    |
| **401**| Unauthorized             | Missing or invalid authentication             |
| **403**| Forbidden                | Not an administrator or insufficient rights   |
| **404**| Not Found                | Resource (user, occasion) doesn't exist       |
| **500**| Internal Server Error    | Server-side error (contact support)           |

---

**Need more help?** Refer to:
- [User Guide](user-guide.md) - For general Tkdo usage
- [API Reference](api-reference.md) - Complete API documentation
- [Troubleshooting Guide](troubleshooting.md) _(coming soon)_ - Common issues and solutions

**Administrator responsibilities:**
- Keep user accounts secure
- Respond to user support requests
- Manage occasions fairly and timely
- Maintain system configuration
- Back up important data regularly
