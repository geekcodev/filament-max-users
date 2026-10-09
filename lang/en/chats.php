<?php

declare(strict_types=1);

return [

    'resource' => [
        'label'          => 'Max chat',
        'plural_label'   => 'Max chats',
        'navigation_label' => 'Max chats',
    ],

    'table' => [
        'chat_id'         => 'Chat ID',
        'display_name'    => 'Name',
        'title'           => 'Name from MAX',
        'icon'            => 'Icon',
        'chat_type'       => 'Type',
        'users_count'     => 'Users',
        'status'          => 'Status',
        'description'     => 'Description',
        'link'            => 'Link',
        'last_activity_at' => 'Last activity',
        'chat_checked_at' => 'Metadata checked',
        'filter_chat_type' => 'Chat type',
        'filter_status'   => 'Status',
    ],

    'chat_type' => [
        'dialog'  => 'Dialog',
        'group'   => 'Group',
        'channel' => 'Channel',
        'unknown' => 'Unknown',
    ],

    'title' => [
        'unknown'        => 'not fetched from MAX',
        'never_checked'  => 'never requested',
    ],

    'view' => [
        'display_name'       => 'Name',
        'chat_id'            => 'Chat ID',
        'chat_type'          => 'Chat type',
        'title'              => 'Name from MAX',
        'description'        => 'Description',
        'link'               => 'Link',
        'icon_url'           => 'Icon',
        'status'             => 'Status',
        'last_activity_at'   => 'Last activity',
        'chat_checked_at'    => 'Metadata checked',
        'users'              => 'Chat users',
        'user_id'            => 'User ID',
        'user_first_name'    => 'First name',
        'user_last_name'     => 'Last name',
        'user_status'        => 'Interaction status',
        'user_last_activity_at' => 'Activity in chat',
    ],

    'actions' => [
        'refresh'          => 'Refresh from MAX',
        'refresh_heading'  => 'Chat metadata refresh',
        'refresh_description' => 'Request the chat name, description, link and icon from MAX API. A single getChat call is made.',
        'refresh_submit'   => 'Refresh',
        'refresh_success'  => 'Chat metadata has been refreshed',
        'refresh_failure'  => 'Failed to refresh chat metadata. Check the MAX token and API availability.',
        'delete'          => 'Delete record',
        'delete_heading'  => 'Delete chat record',
        'delete_description' => 'Delete the chat record and its user links from the local registry. Data in MAX itself is not affected.',
        'delete_submit'   => 'Delete',
        'delete_success'  => 'Chat record deleted',
    ],

];
