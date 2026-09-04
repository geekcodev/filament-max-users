<?php

declare(strict_types=1);

return [

    'resource' => [
        'label'          => 'Max chat',
        'plural_label'   => 'Max chats',
        'navigation_label' => 'Max chats',
    ],

    'table' => [
        'id'              => 'ID',
        'user_id'         => 'User',
        'chat_id'         => 'Chat ID',
        'chat_type'       => 'Type',
        'status'          => 'Status',
        'last_activity_at' => 'Last activity',
        'user_name'       => 'User name',
    ],

    'chat_type' => [
        'dialog'  => 'Dialog',
        'group'   => 'Group',
        'channel' => 'Channel',
        'unknown' => 'Unknown',
    ],

    'view' => [
        'title'              => 'Chat details',
        'id'                 => 'Record ID',
        'user_id'            => 'User',
        'chat_id'            => 'Chat ID',
        'chat_type'          => 'Chat type',
        'status'             => 'Status',
        'last_activity_at'   => 'Last activity',
        'user_name'          => 'User name',
        'api_details'        => 'MAX API data',
        'api_unavailable'    => 'MAX API data unavailable',
    ],

    'actions' => [
        'delete'          => 'Delete record',
        'delete_heading'  => 'Delete chat record',
        'delete_description' => 'Delete the chat record from the local registry. Data in MAX itself is not affected.',
        'delete_submit'   => 'Delete',
        'delete_success'  => 'Chat record deleted',
    ],

];
