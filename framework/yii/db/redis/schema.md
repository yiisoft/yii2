To allow AR to be stored in redis we need a special Schema for it.

HSET prefix:className:primaryKey


http://redis.io/commands

Current Redis connection:
https://github.com/jamm/Memory


# Queries

wrap all these in transactions MULTI

## insert

SET all attribute key-value pairs
SET all relation key-value pairs
make sure to create back-relations

## update

SET all attribute key-value pairs
SET all relation key-value pairs


## delete

DEL all attribute key-value pairs
DEL all relation key-value pairs
make sure to update back-relations


http://redis.io/commands/hmget sounds suiteable!