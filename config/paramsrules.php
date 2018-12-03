<?php
return [
    'system' => [
        'role' => [
            'add' => [
                'name'        => 'required',
                'desc'        => 'required',
                'status'      => 'required',
                'permissions' => 'required|array',
            ],
            'query' => [
                'page_size' => 'required',
                'page'      => 'required',          // 当前页最后一条数据的返回ID
                'status'    => 'required',
            ],
            'detail' => [
                'role_id' => 'required',
            ],
            'delete' => [
                'role_id' => 'required',
                'type'    => 'required',
            ],
            'modify' => [
                'role_id' => 'required',
            ],
        ],
        'account' => [
            'add' => [
                'username' => 'required',
                'mobile'   => 'required',
                'password' => 'required',
                'role_ids' => 'required|array',
                'status'   => 'required',
            ],
            'query' => [
                'page_size' => 'required',
                'page'      => 'required',          // 当前页最后一条数据的返回ID
                'status'    => 'required',
            ],
            'detail' => [
                'user_id' => 'required',
            ],
            'delete' => [
                'user_ids' => 'required|array',
                'type'     =>  'required',
            ],
            'modify' => [
                'user_id' => 'required',
            ],
            'reset_pwd' => [
                'user_id'  => 'required',
                'password' => 'required',
            ],
        ],
    ],
    'vip' => [
    ],
];
