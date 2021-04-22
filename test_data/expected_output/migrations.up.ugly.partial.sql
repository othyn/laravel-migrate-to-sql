-- 2019_08_19_000000_create_failed_jobs_table:
create table "failed_jobs" ("id" integer not null primary key autoincrement, "uuid" varchar not null, "connection" text not null, "queue" text not null, "payload" text not null, "exception" text not null, "failed_at" datetime default CURRENT_TIMESTAMP not null);

-- 2019_08_19_000000_create_failed_jobs_table:
create unique index "failed_jobs_uuid_unique" on "failed_jobs" ("uuid");