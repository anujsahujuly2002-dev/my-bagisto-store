<?php

return [
    'errors' => [
        'not-found' => [
            'title' => '未找到商店',
            'message' => '此地址没有商店。请检查地址后重试。',
        ],

        'suspended' => [
            'title' => '商店不可用',
            'message' => '此商店目前不可用。请稍后再来。',
        ],

        'provisioning' => [
            'title' => '商店正在设置中',
            'message' => '此商店正在设置中，片刻后即可使用。请稍后刷新页面。',
        ],
    ],

    'validation' => [
        'subdomain' => [
            'format' => ':attribute 必须由 3 到 63 个小写字母、数字或连字符组成，且不能以连字符开头或结尾。',
            'reserved' => ':attribute 已被保留，不能用于商店。',
            'taken' => ':attribute 已被其他商店使用。',
        ],
    ],
];
