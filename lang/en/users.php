<?php

declare(strict_types=1);

return [

    'resource' => [
        'label'          => 'Max пользователь',
        'plural_label'   => 'Max пользователи',
        'navigation_label' => 'Max users',
    ],

    'table' => [
        'user_id'           => 'ID',
        'first_name'        => 'First name',
        'last_name'         => 'Last name',
        'username'          => 'Username',
        'is_bot'            => 'Bot',
        'last_activity_time' => 'Last activity',
        'chats_count'       => 'Chats',
        'avatar'            => 'Avatar',
        'filter_is_bot'     => 'Type',
        'filter_is_bot_user' => 'User',
        'filter_is_bot_bot'  => 'Bot',
    ],

    'view' => [
        'title'              => 'User profile',
        'user_id'            => 'User ID',
        'first_name'         => 'First name',
        'last_name'          => 'Last name',
        'username'           => 'Username',
        'is_bot'             => 'Is bot',
        'is_bot_yes'         => 'Yes',
        'is_bot_no'          => 'No',
        'last_activity_time' => 'Last activity',
        'name'               => 'Display name',
        'description'        => 'Description',
        'avatar_url'         => 'Avatar URL',
        'full_avatar_url'    => 'Full avatar URL',
        'phone'              => 'Phone',
        'email'              => 'Email',
        'profile_checked_at' => 'Last synced',
        'chats_count'        => 'Chats count',
    ],

    'actions' => [
        'refresh'          => 'Refresh from MAX',
        'refresh_heading'  => 'Profile refresh',
        'refresh_description' => 'Request fresh profile data from MAX API. The profile will be updated if the user has an active chat with the bot.',
        'refresh_submit'   => 'Refresh',
        'refresh_success'  => 'User profile has been refreshed',
        'refresh_failure'  => 'Failed to refresh profile. The user may not have an active chat with the bot.',
    ],

];
