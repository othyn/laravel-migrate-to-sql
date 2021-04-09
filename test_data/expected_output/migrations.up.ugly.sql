-- 2014_10_12_000000_create_users_table:
create table `users` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(255) not null, `email` varchar(255) not null, `email_verified_at` timestamp null, `password` varchar(255) not null, `remember_token` varchar(100) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- 2014_10_12_000000_create_users_table:
alter table `users` add unique `users_email_unique`(`email`);

-- 2014_10_12_100000_create_password_resets_table:
create table `password_resets` (`email` varchar(255) not null, `token` varchar(255) not null, `created_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- 2014_10_12_100000_create_password_resets_table:
alter table `password_resets` add index `password_resets_email_index`(`email`);