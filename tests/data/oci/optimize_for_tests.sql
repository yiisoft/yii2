-- === Test-only settings: sacrifice durability for speed ===
-- === NEVER use in production ===

-- Make commits return instantly (biggest win for write-heavy test suites)
ALTER SYSTEM SET commit_logging = BATCH SCOPE=BOTH;
ALTER SYSTEM SET commit_wait = NOWAIT SCOPE=BOTH;

-- Disable block integrity checks (saves 10-15% CPU)
ALTER SYSTEM SET db_block_checking = OFF SCOPE=BOTH;
ALTER SYSTEM SET db_block_checksum = OFF SCOPE=BOTH;

-- Reduce undo retention (default 900s -> 60s)
ALTER SYSTEM SET undo_retention = 60 SCOPE=BOTH;

-- Disable the recycle bin
ALTER SYSTEM SET recyclebin = OFF SCOPE=SPFILE;

-- Defer segment creation (tables get no storage until first INSERT)
ALTER SYSTEM SET deferred_segment_creation = TRUE SCOPE=BOTH;

-- Increase cursor cache for ORM-heavy workloads
ALTER SYSTEM SET open_cursors = 300 SCOPE=BOTH;
ALTER SYSTEM SET session_cached_cursors = 100 SCOPE=SPFILE;

-- Disable automatic optimizer stats collection
BEGIN
    DBMS_AUTO_TASK_ADMIN.DISABLE(
        client_name => 'auto optimizer stats collection',
        operation => NULL, window_name => NULL);
END;
/
