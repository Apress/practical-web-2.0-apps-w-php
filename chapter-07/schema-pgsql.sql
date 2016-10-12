create table users (
    user_id         serial          not null,
    username        varchar(255)    not null,
    password        varchar(32)     not null,
    user_type       varchar(20)     not null,
    ts_created      timestamptz     not null,
    ts_last_login   timestamptz,

    primary key (user_id),
    unique (username)
);

create table users_profile (
    user_id         int             not null,
    profile_key     varchar(255)    not null,
    profile_value   text            not null,

    primary key (user_id, profile_key),
    foreign key (user_id) references users (user_id)
);

create table blog_posts (
    post_id         serial          not null,
    user_id         int             not null,

    url             varchar(255)    not null,
    ts_created      timestamptz     not null,
    status          varchar(10)     not null,

    primary key (post_id),
    foreign key (user_id) references users (user_id)
);

create index blog_posts_url on blog_posts (url);

create table blog_posts_profile (
    post_id         int             not null,
    profile_key     varchar(255)    not null,
    profile_value   text            not null,

    primary key (post_id, profile_key),
    foreign key (post_id) references blog_posts (post_id)
);
