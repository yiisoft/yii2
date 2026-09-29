DROP TABLE IF EXISTS "partitioned" CASCADE;

CREATE TABLE "partitioned" (
  city_id         int not null,
  logdate         date not null
) PARTITION BY RANGE ("logdate");