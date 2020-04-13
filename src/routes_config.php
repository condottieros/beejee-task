<?php
return [
    ['pattern'=>'#^/api/tasks/?$#', 'method'=>'get', 'action'=> 'getTasks'],
    ['pattern'=>'#^/api/task/(?P<taskid>\d+)/?$#', 'method'=>'get', 'action'=> 'getTaskItem'],
    ['pattern'=>'#^/api/login/?$#', 'method'=>'post', 'action'=> 'login'],
    ['pattern'=>'#^/api/logout/?$#', 'method'=>'get', 'action'=> 'logout'],
    ['pattern'=>'#^/api/task/?$#', 'method'=>'post', 'action'=> 'createTask'],
    ['pattern'=>'#^/api/check-access/?$#', 'method'=>'get', 'action'=> 'checkAccess'],
    ['pattern'=>'#^/api/task/(?P<taskid>\d+)/confirmed/?$#', 'method'=>'post', 'action'=> 'setConfirmed'],
    ['pattern'=>'#^/api/task/(?P<taskid>\d+)/edit/?$#', 'method'=>'post', 'action'=> 'editTask'],
];