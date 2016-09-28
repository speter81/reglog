CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `password` varchar(40) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `users` ADD `status` TINYINT UNSIGNED NOT NULL DEFAULT '0' AFTER `name`;
ALTER TABLE `users` CHANGE `password` `password` VARCHAR(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL;


CREATE TABLE `access_log` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `ip` int(10) UNSIGNED NOT NULL,
  `first_attempt` datetime NOT NULL,
  `last_attempt` datetime NOT NULL,
  `expire_time` int(11) NOT NULL DEFAULT '0',
  `count` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `access_log`
  ADD PRIMARY KEY (`ip`,`first_attempt`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `ip` (`ip`) USING BTREE,
  ADD KEY `expire_time` (`expire_time`);
