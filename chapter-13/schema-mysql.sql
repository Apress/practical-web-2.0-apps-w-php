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

create table blog_posts (
    post_id         serial          not null,
    user_id         bigint unsigned not null,

    url             varchar(255)    not null,
    ts_created      datetime        not null,
    status          varchar(10)     not null,

    primary key (post_id),
    foreign key (user_id) references users (user_id)
) type = InnoDB;

create index blog_posts_url on blog_posts (url);

create table blog_posts_profile (
    post_id         bigint unsigned not null,
    profile_key     varchar(255)    not null,
    profile_value   text            not null,

    primary key (post_id, profile_key),
    foreign key (post_id) references blog_posts (post_id)
) type = InnoDB;

create table blog_posts_tags (
    post_id         bigint unsigned not null,
    tag             varchar(255)    not null,

    primary key (post_id, tag)
) type = InnoDB;

create table blog_posts_images (
    image_id        serial          not null,

    filename        varchar(255)    not null,

    post_id         bigint unsigned not null,
    ranking         int unsigned    not null,

    primary key (image_id),
    foreign key (post_id) references blog_posts (post_id)
) type = InnoDB;

create table blog_posts_locations (
    location_id     serial          not null,
    post_id         bigint unsigned not null,
    longitude       numeric(10, 6)  not null,
    latitude        numeric(10, 6)  not null,
    description     text            not null,

    primary key (location_id),
    foreign key (post_id) references blog_posts (post_id)
) type = InnoDB;
