# Logging

We use the following events in meldportaal-admin:

| Event                            | `event_code` (string) | Routing key (prefixed with `[app].[env].`[^1]) |
|----------------------------------|-----------------------|------------------------------------------------|
| Sign in                          | `091111`              | `user_login`                                   |
| Failed sign in                   | `091111`              | `user_login`                                   |
| Sign out                         | `092222`              | `user_logout`                                  |
| Two factor authentication failed | `093333`              | `user_login_two_factor_failed`                 |
| User created                     | `090002`              | `user_created`                                 |
| Reset credentials                | `090003`              | `reset_credentials`                            |
| Change user data                 | `900101`              | `account_change`                               |
| Change user roles                | `900102`              | `account_change`                               |
| Activate/deactive user           | `900104`              | `account_change`                               |

# Event details

### Sign in (091111)

Triggered when a user signs in with the correct email/password combination and valid OTP code.

```json
{
  "user_id": 1,
  "request": {
    "user_id": 1,
    "user_email": "admin@example.org",
    "user_roles": [
      "SUPER_ADMIN"
    ]
  },
  "created_at": "2023-03-14T09:39:45.822262Z",
  "event_code": "091111",
  "action_code": "E",
  "allowed_admin_view": false,
  "failed": false,
  "failed_reason": null,
  "email": "admin@example.org"
}
```

### Failed sign in (091111)

Triggered when either the email address or password is incorrect. The `failed_reason` field will be set to `invalid_email` or `invalid_password`
respectively.

```json
{
  "user_id": null,
  "request": {
    "user_id": 1,
    "user_roles": [
      "SUPER_ADMIN"
    ],
    "user_email": "admin@example.org",
    "partial_password_hash": "136ab8a720ff4b2a"
  },
  "created_at": "2023-03-14T09:35:10.849650Z",
  "event_code": "091111",
  "action_code": "E",
  "allowed_admin_view": false,
  "failed": true,
  "failed_reason": "invalid_password",
  "email": null
}
```

```json
{
  "user_id": null,
  "request": {
    "user_id": null,
    "user_roles": null,
    "user_email": "notexist@example.org",
    "partial_password_hash": "b97fef14179a07ef"
  },
  "created_at": "2023-03-14T09:36:04.640776Z",
  "event_code": "091111",
  "action_code": "E",
  "allowed_admin_view": false,
  "failed": true,
  "failed_reason": "invalid_email",
  "email": null
}
```

### Sign out (092222)

Triggered when a user signs out.

```json
{
  "user_id": 1,
  "request": {
    "source": "MELDPORTAAL_ADMIN"
  },
  "created_at": "2023-03-14T09:41:23.812558Z",
  "event_code": "092222",
  "action_code": "E",
  "allowed_admin_view": false,
  "failed": false,
  "failed_reason": null,
  "email": "admin@example.org"
}
```

### Two factor authentication failed (093333)

Triggered when the two factor authentication code is incorrect. Note that when this event is triggered, the user has supplied correct
email/password combination, but the sign-in (091111) event is ONLY triggered when the OTP code is also correct.

```json
{
  "user_id": 1,
  "request": [],
  "created_at": "2023-03-14T09:37:52.570183Z",
  "event_code": "093333",
  "action_code": "E",
  "allowed_admin_view": false,
  "failed": true,
  "failed_reason": null,
  "email": "admin@example.org"
}
```

### User created (090002)

Triggered when creating a new user. `is_api_user` will be set to `true` when a new API user is created.

```json
{
  "user_id": 1,
  "request": {
    "user_id": 4,
    "is_api_user": false,
    "table": "mp_users",
    "source": "MELDPORTAAL_ADMIN"
  },
  "created_at": "2023-03-14T09:45:34.181452Z",
  "event_code": "090002",
  "action_code": "C",
  "allowed_admin_view": false,
  "failed": false,
  "failed_reason": null,
  "email": "admin@example.org"
}
```

```json
{
  "user_id": 1,
  "request": {
    "user_id": 5,
    "is_api_user": true,
    "table": "mp_users",
    "source": "MELDPORTAAL_ADMIN"
  },
  "created_at": "2023-03-14T09:47:02.811313Z",
  "event_code": "090002",
  "action_code": "C",
  "allowed_admin_view": false,
  "failed": false,
  "failed_reason": null,
  "email": "admin@example.org"
}
```

### Reset credentials (090003)

Triggered when either the password or 2fa code is reset, or both.

```json
{
  "user_id": 1,
  "request": {
    "user_id": 6,
    "reset_password": true,
    "reset_2fa": true,
    "source": "MELDPORTAAL_ADMIN"
  },
  "created_at": "2023-03-14T09:51:48.294746Z",
  "event_code": "090003",
  "action_code": "U",
  "allowed_admin_view": false,
  "failed": false,
  "failed_reason": null,
  "email": "admin@example.org"
}
```

### Change user data (900101)

Triggered when user data has been changed.

```json
{
  "user_id": 1,
  "request": {
    "user_id": 6,
    "name_changed": true,
    "serial_changed": false,
    "source": "MELDPORTAAL_ADMIN"
  },
  "created_at": "2023-03-14T09:48:33.222689Z",
  "event_code": "900101",
  "action_code": "U",
  "allowed_admin_view": false,
  "failed": false,
  "failed_reason": null,
  "email": "admin@example.org"
} 
```

### Change user roles (900102)

Triggered when user roles have been changed.

```json
{
  "user_id": 1,
  "request": {
    "user_id": 6,
    "roles": [
      "USER",
      "SPECIMEN"
    ],
    "table": "mp_users",
    "source": "MELDPORTAAL_ADMIN"
  },
  "created_at": "2023-03-14T09:49:32.739144Z",
  "event_code": "900102",
  "action_code": "U",
  "allowed_admin_view": false,
  "failed": false,
  "failed_reason": null,
  "email": "admin@example.org"
}
```

### Activate/deactivate user (900103)

Triggered when a user has been activated or deactivated. `active` is either true or false.

```json
{
  "user_id": 1,
  "request": {
    "user_id": 6,
    "active": false,
    "table": "mp_users",
    "source": "MELDPORTAAL_ADMIN"
  },
  "created_at": "2023-03-14T09:51:00.206611Z",
  "event_code": "900104",
  "action_code": "U",
  "allowed_admin_view": false,
  "failed": false,
  "failed_reason": null,
  "email": "admin@example.org"
}
```
