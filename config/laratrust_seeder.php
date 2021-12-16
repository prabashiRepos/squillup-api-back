<?php

return [
    'role_structure' => [
        'developer' => [
            'role' => 'c,v,u,d,as,dt',
            'permission' => 'c,v,u,d,as,dt',
            'user' => 'c,v,u,d',
            'examboard' => 'c,v,u,d',
            'keystage' => 'c,v,u,d',
            'year' => 'c,v,u,d',
            'subject' => 'c,v,u,d',
            'chapter' => 'c,v,u,d',
            'lesson' => 'c,v,u,d',
            'plan' => 'c,v,u,d',
            'qa' => 'c,v,u,d',
            'selftest' => 'c,v,u,d',
            'question' => 'c,v,u,d',
            'worksheet' => 'c,v,u,d',
            'pastpaper' => 'c,v,u,d',
        ],
        'superadmin' => [
            'role' => 'c,v,u,d,as,dt',
            'permission' => 'c,v,u,d,as,dt',
            'user' => 'c,v,u,d',
            'examboard' => 'c,v,u,d',
            'keystage' => 'c,v,u,d',
            'year' => 'c,v,u,d',
            'subject' => 'c,v,u,d',
            'chapter' => 'c,v,u,d',
            'lesson' => 'c,v,u,d',
            'plan' => 'c,v,u,d',
            'qa' => 'c,v,u,d',
            'selftest' => 'c,v,u,d',
            'question' => 'c,v,u,d',
            'worksheet' => 'c,v,u,d',
            'pastpaper' => 'c,v,u,d',
        ],
        'contentwriter' => [
            'qa' => 'c,v,u,d',
            'selftest' => 'c,v,u,d',
            'question' => 'c,v,u,d',
            'worksheet' => 'c,v,u,d',
            'pastpaper' => 'c,v,u,d',
        ],
        'parent' => [
            'plan' => 'v',
        ],
        'student' => [

        ],
    ],
    'permission_structure' => [
        
    ],
    'permissions_map' => [
        'c' => 'create',
        'v' => 'view',
        'u' => 'update',
        'd' => 'delete',
        'as' => 'assign',
        'dt' => 'detach',
    ]
];
