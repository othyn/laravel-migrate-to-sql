-- 2014_10_12_000000_create_users_table:
CREATE TABLE `users` (
    `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `name` varchar(255) NOT NULL,
    `email` varchar(255) NOT NULL,
    `email_verified_at` timestamp NULL,
    `password` varchar(255) NOT NULL,
    `remember_token` varchar(100) NULL,
    `created_at` timestamp NULL,
    `updated_at` timestamp NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci';

-- 2014_10_12_000000_create_users_table:
ALTER TABLE
    `users`
ADD
    UNIQUE `users_email_unique`(`email`);

-- 2014_10_12_100000_create_password_resets_table:
CREATE TABLE `password_resets` (
    `email` varchar(255) NOT NULL,
    `token` varchar(255) NOT NULL,
    `created_at` timestamp NULL
) DEFAULT CHARACTER SET utf8mb4 COLLATE 'utf8mb4_unicode_ci';

-- 2014_10_12_100000_create_password_resets_table:
ALTER TABLE
    `password_resets`
ADD
    INDEX `password_resets_email_index`(`email`);