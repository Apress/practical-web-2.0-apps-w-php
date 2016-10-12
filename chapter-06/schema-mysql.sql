create table users (
    user_id         serial          not null,
    username        varchar(255)    not null,
    password        varchar(32)     not null,
    user_type       varchar(20)     not null,
    ts_created      datetime        not null,
    ts_last_login   datetime,

    primary key (user_id),
    unique (username)
) type = InnoDB;

create table users_profile (
    user_id         bigint unsigned not null,
    profile_key     varchar(255)    not null,
    profile_value   text            not null,

    primary key (user_id, profile_key),
    foreign key (user_id) references users (user_id)
) type = InnoDB;
