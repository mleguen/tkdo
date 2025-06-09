# API Platform Bootstrap

## Issues

- the API was built from routes, but it lacks some old V1 entities knowledge (such as participants for occasion)
- there are more getters/setters than necessary, and some are duplicated in French and English
- authentication is too manual: need to move to something more standard
- condition attributes and operations to entity state and/or user role:
  - use serialization groups (see <https://api-platform.com/docs/v2.1/core/serialization/>)
  - dynamically compute the context instead of just relying on default "read" and "write" groups
- password is returned when getting Utilisateur:
  - use serialization group as well
- description for custom operations are wrong in the API doc
- enums (genre, pref notif) are not selects in the admin
- update at is editable in the admin instead of being autocomputed
- creating a user from the admin without providing a non nullable property (e.g. pref notif) leads to a 500 because of a DB violation, instead of a 400 before attempting to create anything in the DB 
- tests are missing
- need to remove the old demo entity
- need to switch from postgresql to mysql
- perhaps the good time for translating all this to English?
