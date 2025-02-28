<?php
try {
    // 加载配置文件
    if (!file_exists(__DIR__.'/config/config.php')) {
        throw new Exception('配置文件缺失');
    }
    
    $config = include __DIR__.'/config/config.php';
    
    // 验证必要参数
    $required = ['user_id', 'token', 'api_url'];
    foreach ($required as $key) {
        if (empty($config[$key])) {
            throw new Exception("配置项 {$key} 未设置");
        }
    }

    // 分页参数处理
    $current_page = isset($_GET['p']) ? max(1, (int)$_GET['p']) : 1;
    $params = [
        'page'     => $current_page,
        'per_page' => 20
    ];

    // 生成签名
    $ts = time();
    $params_json = json_encode($params);
    $sign_str = "params{$params_json}ts{$ts}user_id{$config['user_id']}";
    $sign = md5($config['token'] . $sign_str);

    // 构建请求数据
    $request_data = [
        'user_id' => $config['user_id'],
        'params'  => $params_json,
        'ts'      => $ts,
        'sign'    => $sign
    ];

    // 发送API请求
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $config['api_url'],
        CURLOPT_POST           => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode($request_data),
        CURLOPT_TIMEOUT        => 10
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error) {
        throw new Exception("API请求失败: {$error}");
    }

    // 解析响应
    $data = json_decode($response, true) ?? [];
    
    if ($http_code !== 200 || !isset($data['ec']) || $data['ec'] != 200) {
            $error_code = isset($data['ec']) ? $data['ec'] : '未知';
            $debug_info = isset($data['data']['debug']['kv_string']) ? $data['data']['debug']['kv_string'] : '';
            $default_msg = isset($data['em']) ? $data['em'] : '未知错误';
        
            switch ((int)$error_code) {
                case 400001:
                    $error_msg = '参数不完整';
                    break;
                case 400002:
                    $error_msg = '请求已过期（时间戳误差超过1小时）';
                    break;
                case 400003:
                    $error_msg = '参数格式错误';
                    break;
                case 400004:
                    $error_msg = 'Token验证失败';
                    break;
                case 400005:
                    $error_msg = '签名错误: '.$debug_info;
                    break;
                default:
                    $error_msg = $default_msg;
            }
            
            throw new Exception("API错误[{$error_code}]: {$error_msg}");
        }

    // 提取数据
    $sponsors = $data['data']['list'] ?? [];
    $total_pages = max(1, $data['data']['total_page'] ?? 1);
    $current_page = min($current_page, $total_pages);
    
    // 排序
    usort($sponsors, function($a, $b) {
        $amountA = isset($a['all_sum_amount']) ? (float)$a['all_sum_amount'] : 0;
        $amountB = isset($b['all_sum_amount']) ? (float)$b['all_sum_amount'] : 0;
        return $amountB <=> $amountA; // 降序排列
    });

} catch (Exception $e) {
    // 安全显示错误信息
    $error_message = htmlspecialchars($e->getMessage());
    die("<div style='padding:20px;background:#ffecec;border:2px solid red;'>
            <h3>系统错误</h3>
            <p>{$error_message}</p>
            <small>请检查配置或稍后重试</small>
         </div>");
}

// HTML部分
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($config['page_title']) ?></title>
    <link rel="stylesheet" href="https://fastly.jsdelivr.net/npm/normalize.css@8.0.1/normalize.min.css">
    <link rel="stylesheet" href="css/styles.css">
<style>
    body {
        background-image: url('https://pic1.afdiancdn.com/static/img/hand-heart@2x.png');
        background-color: #ffffff;
        color: #946cee;
        background-repeat: no-repeat;
        background-position: calc(100% + 200px) bottom;
        background-attachment: fixed;
        background-size: auto;
        min-height: 100vh; 
    }


</style>
</head>
<body>
    <div class="afdian-top">
        <img src="https://pic1.afdiancdn.com/static/img/electriclove@2x.png" 
             class="top-electric-line"
             alt="电线装饰图">
        <div class="afdian-card">
            <!-- 个人资料卡 -->
            <div class="profile-card">
                <!--img src="./img/avatar.gif"-->
                <img src="<?= $config['avatar_url'] ?>"
                     class="profile-avatar"
                     alt="头像">
                <div class="profile-info">
                    <h1><?= htmlspecialchars($config['nick_name']) ?></h1>
                    <h2><?= htmlspecialchars($config['slogan']) ?></h2>
                        <a href="<?= htmlspecialchars($config['afdian_url']) ?>" 
                           target="_blank" 
                           class="sponsor-button">
                           立即发电
                           <svg class="lightning-icon" viewBox="0 0 24 24" width="20" height="20">
                             <path d="M13 3v6h5l-8 12v-6H6l7-12z" 
                                   fill="#fff" 
                                   stroke="#946cee" 
                                   stroke-width="1.2"/>
                           </svg>
                        </a>
                </div>
            </div>
        </div>
    </div>
    <!-- 赞助者名单 -->
    </div>
    <div class="container">
        <div class="header">
            <h2>服务器由以下群友赞助 😊</h2>
        </div>

        <div class="sponsors-grid">
            <?php foreach ($sponsors as $sponsor): ?>
                <div class="sponsor-card">
                    <a href="https://afdian.com/u/<?= htmlspecialchars($sponsor['user']['user_id']) ?>" 
                       target="_blank"
                       class="sponsor-link">
                        <img src="<?= htmlspecialchars($sponsor['user']['avatar']) ?>" 
                             class="sponsor-avatar"
                             alt="<?= htmlspecialchars($sponsor['user']['name']) ?>">
                        <h3 class="sponsor-name">
                            <?= htmlspecialchars($sponsor['user']['name']) ?>
                        </h3>
                        <p class="sponsor-amount">
                            累计赞助: ￥<?= number_format($sponsor['all_sum_amount'], 2) ?>
                        </p>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?p=<?= $i ?>" 
                   class="page-link <?= $i == $current_page ? 'current' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    </div>
    <!-- 页脚 -->
    <footer class="site-footer">
        <div class="footer-content">
            <p class="copyright">
                &copy; 2021-<span id="currentYear"></span> <?= htmlspecialchars($config['organization']) ?> . 由<?= htmlspecialchars($config['nick_name']) ?>用<span class="heart">❤️</span>维护
            </p>
             <!-- 备案信息如果没有就注释掉 -->
            <p class="beian">
                <a href="https://beian.miit.gov.cn/" 
                   target="_blank" 
                   rel="nofollow noopener">
                    <?= htmlspecialchars($config['beian_id']) ?>
                </a>
            </p>
            <!-- beian end -->
        </div>
    </footer>
    <script>
        document.getElementById('currentYear').textContent = new Date().getFullYear();
    </script>
</body>
</html>
