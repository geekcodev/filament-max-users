<?php

declare(strict_types=1);

return [

    'resource' => [
        'label'          => 'Max user',
        'plural_label'   => 'Max users',
        'navigation_label' => 'Max users',
    ],

    'table' => [
        'user_id'           => 'ID',
        'first_name'        => 'First name',
        'last_name'         => 'Last name',
        'username'          => 'Username',
        'phone'             => 'Phone',
        'phone_hint'        => 'Phone received from a verified contact',
        'email'             => 'Email',
        'is_bot'            => 'Bot',
        'last_activity_time' => 'Last activity',
        'chats_count'       => 'Chats',
        'avatar'            => 'Avatar',
        'full_avatar_url'   => 'Full avatar',
        'name'              => 'Display name',
        'description'       => 'Description',
        'phone_verified_at' => 'Phone verified at',
        'profile_checked_at' => 'Last synced',
        'filter_is_bot'     => 'Type',
        'filter_is_bot_user' => 'User',
        'filter_is_bot_bot'  => 'Bot',
        'filter_has_phone'  => 'With phone',
    ],

    'form' => [
        'first_name'  => 'First name',
        'last_name'   => 'Last name',
        'username'    => 'Username',
        'name'        => 'Display name',
        'description' => 'Description',
        'phone'       => 'Phone',
        'email'       => 'Email',
        'is_bot'      => 'Is bot',
        'reset_phone_verification' => 'Reset phone verification',
        'reset_phone_verification_hint' => 'The number stays, but the verification mark is removed. Changing the number resets verification automatically.',
    ],

    'pages' => [
        'edit_title' => 'Edit user',
    ],

    'view' => [
        'user_id'            => 'User ID',
        'first_name'         => 'First name',
        'last_name'          => 'Last name',
        'username'           => 'Username',
        'is_bot'             => 'Is bot',
        'last_activity_time' => 'Last activity',
        'name'               => 'Display name',
        'description'        => 'Description',
        'avatar_url'         => 'Avatar',
        'phone'              => 'Phone',
        'phone_verified_at'  => 'Phone verified at',
        'phone_unverified'   => 'no verified contact',
        'email'              => 'Email',
        'profile_checked_at' => 'Last synced',
        'never_checked'      => 'never synced',
        'chats'              => 'Chats',
        'chat_name'          => 'Name',
        'chat_id'            => 'Chat ID',
        'chat_type'          => 'Chat type',
        'chat_last_activity_at' => 'Last activity',
    ],

    'actions' => [
        'refresh'          => 'Refresh from MAX',
        'refresh_heading'  => 'Profile refresh',
        'refresh_description' => 'Request fresh profile data from MAX API. The profile will be updated if the user has an active chat with the bot.',
        'refresh_submit'   => 'Refresh',
        'refresh_success'  => 'User profile has been refreshed',
        'refresh_failure'  => 'Failed to refresh profile. The user may not have an active chat with the bot.',
        'edit_success'     => 'User profile has been saved',
        'delete'           => 'Delete',
        'delete_heading'   => 'Delete user',
        'delete_description' => 'The record will be deleted from the local max_users registry together with chat links. The user data in MAX itself is not changed. If the record is referenced by the application, deletion is rejected.',
        'delete_submit'    => 'Delete',
        'delete_success'   => 'User has been deleted from the local registry',
        'delete_failure'   => 'The user could not be deleted: the record is referenced by the application.',
    ],

];
