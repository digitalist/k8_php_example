/*

username — имя 
email — емейл 
validts — unix ts до которого действует ежемесячная подписка 
confirmed — 0 или 1 в зависимости от того, подтвердил ли пользователь свой емейл по ссылке (пользователю после регистрации приходит письмо с уникальный ссылкой на указанный емейл, если он нажал на ссылку в емейле в этом поле устанавливается 1)


Таблица emails в DB с данными проверки емейл на валидность: 

email — емейл 
checked — 0 или 1 (был ли проверен) 
valid — 0 или 1 (является ли валидным)

*/
/*
-- create database k8;
-- use k8;
drop table `k8`.`users`;
drop table `k8`.`emails`;
*/
-- вообще как правило на пользователя регистрируется только один email. 
-- остальные к нему линкуются, но допустим. 
create table `k8`.`users`(
    oid BIGINT(20) NOT NULL AUTO_INCREMENT,
    username VARCHAR (64) NOT NULL DEFAULT '', -- let's pretend,
    email VARCHAR(254) NOT NULL DEFAULT '',  -- RFC 5321 
    validts DATETIME,
    confirmed BOOLEAN DEFAULT 0,
    PRIMARY KEY (oid),
    key(username, email, validts),
    key(email)
) ;

create table `k8`.`emails`(
oid BIGINT(20) NOT NULL AUTO_INCREMENT,
email VARCHAR(254)  NOT NULL DEFAULT '', -- RFC 5321
checked BOOLEAN DEFAULT 0,
valid BOOLEAN DEFAULT 0,
PRIMARY KEY (oid),
key(email, checked, valid)
);

/* -- example inserts

INSERT  INTO `k8`.`users` (oid, username, email, validts, confirmed) values
(0, 'test_user', 'test_user@example.com', DATE_ADD(NOW(), INTERVAL +3 DAY), 0);

INSERT  INTO `k8`.`emails` (oid, email, checked, valid) values
(0, 'test_user@example.com', 0, 0);

*/
