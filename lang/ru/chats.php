<?php

declare(strict_types=1);

return [

    'resource' => [
        'label'          => 'Max чат',
        'plural_label'   => 'Max чаты',
        'navigation_label' => 'Max чаты',
    ],

    'table' => [
        'chat_id'         => 'Chat ID',
        'display_name'    => 'Название',
        'title'           => 'Название из MAX',
        'icon'            => 'Иконка',
        'chat_type'       => 'Тип',
        'users_count'     => 'Пользователей',
        'status'          => 'Статус',
        'description'     => 'Описание',
        'link'            => 'Ссылка',
        'last_activity_at' => 'Последняя активность',
        'chat_checked_at' => 'Метаданные обновлены',
        'filter_chat_type' => 'Тип чата',
        'filter_status'   => 'Статус',
    ],

    'chat_type' => [
        'dialog'  => 'Диалог',
        'group'   => 'Группа',
        'channel' => 'Канал',
        'unknown' => 'Неизвестно',
    ],

    'title' => [
        'unknown'        => 'не получено из MAX',
        'never_checked'  => 'ещё не запрашивалось',
    ],

    'view' => [
        'display_name'       => 'Название',
        'chat_id'            => 'Chat ID',
        'chat_type'          => 'Тип чата',
        'title'              => 'Название из MAX',
        'description'        => 'Описание',
        'link'               => 'Ссылка',
        'icon_url'           => 'Иконка',
        'status'             => 'Статус',
        'last_activity_at'   => 'Последняя активность',
        'chat_checked_at'    => 'Метаданные обновлены',
        'users'              => 'Пользователи чата',
        'user_id'            => 'User ID',
        'user_first_name'    => 'Имя',
        'user_last_name'     => 'Фамилия',
        'user_status'        => 'Статус взаимодействия',
        'user_last_activity_at' => 'Активность в чате',
    ],

    'actions' => [
        'refresh'          => 'Обновить из MAX',
        'refresh_heading'  => 'Обновление метаданных чата',
        'refresh_description' => 'Запросить название, описание, ссылку и иконку чата из MAX API. Выполняется один запрос getChat.',
        'refresh_submit'   => 'Обновить',
        'refresh_success'  => 'Метаданные чата обновлены',
        'refresh_failure'  => 'Не удалось обновить метаданные чата. Проверьте токен MAX и доступность API.',
        'delete'          => 'Удалить запись',
        'delete_heading'  => 'Удаление записи чата',
        'delete_description' => 'Удалить запись чата и связи с пользователями из локального реестра. Данные в самом MAX не затрагиваются.',
        'delete_submit'   => 'Удалить',
        'delete_success'  => 'Запись чата удалена',
    ],

];
