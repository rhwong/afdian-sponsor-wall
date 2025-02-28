<?php
// config.php - 请根据实际信息修改
return [
    'user_id' => '114514***1919810',
    'token'   => '114514***1919810',
    'api_url' => 'https://afdian.com/api/open/query-sponsor',
    'avatar_url' => 'https://q1.qlogo.cn/g?b=qq&nk=你的QQ号&s=640',
    'afdian_url' => 'https://afdian.com/a/aobacore',
    'nick_name' => '你的名字，在顶部显示',
    'organization' => '你的组织名字，在页脚显示',
    'slogan' => '正在创作 饿饿饭饭V我50',
    'page_title' => '赞助者名单 - 网页标题',
    'beian_id' => '萌ICP备1145141919810号',
];

// 建议设置文件权限为 600
// 并在Nginx添加以下配置禁止直接访问：
/*
location ~ /config\.php$ {
    deny all;
    return 403;
}
*/
