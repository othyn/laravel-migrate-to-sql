-- 2019_08_19_000000_create_failed_jobs_table:
CREATE TABLE "failed_jobs" (
    "id" integer NOT NULL PRIMARY KEY autoincrement,
    "uuid" varchar NOT NULL, "connection" text NOT NULL,
    "queue" text NOT NULL, "payload" text NOT NULL,
    "exception" text NOT NULL, "failed_at" datetime DEFAULT CURRENT_TIMESTAMP NOT NULL
);

-- 2019_08_19_000000_create_failed_jobs_table:
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" ON "failed_jobs" ("uuid");