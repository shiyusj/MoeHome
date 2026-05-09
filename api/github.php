<?php
/**
 * GitHub API 代理
 * 解决虚拟主机跨域限制和 API 请求限制问题
 * 
 * 支持端点:
 * - /api/github.php?type=repos&user=username - 获取项目列表
 * - /api/github.php?type=contributions&user=username - 获取贡献图数据
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$config = [];
$configFile = __DIR__ . '/config.php';
if (file_exists($configFile)) {
    require_once $configFile;
} else {
    require_once __DIR__ . '/config.example.php';
}

$type = isset($_GET['type']) ? $_GET['type'] : '';
$user = isset($_GET['user']) ? trim($_GET['user']) : '';

if (empty($user)) {
    echo json_encode(['error' => 'User parameter required'], JSON_UNESCAPED_UNICODE);
    exit;
}

$githubToken = isset($config['api']['github_token']) ? $config['api']['github_token'] : '';
$cacheTime = isset($config['api']['github_cache']) ? $config['api']['github_cache'] : 1800;
$sslVerify = isset($config['api']['ssl_verify']) ? $config['api']['ssl_verify'] : true;

if (!is_dir(__DIR__ . '/cache')) {
    @mkdir(__DIR__ . '/cache', 0755, true);
}

function fetchGitHubApi($url, $token, $sslVerify) {
    $contextOptions = [
        'http' => [
            'timeout' => 15,
            'ignore_errors' => true,
            'header' => [
                "User-Agent: MoeHome/1.0",
                "Accept: application/vnd.github.v3+json"
            ]
        ],
        'ssl' => [
            'verify_peer' => $sslVerify,
            'verify_peer_name' => $sslVerify
        ]
    ];

    if (!empty($token)) {
        $contextOptions['http']['header'][] = "Authorization: token " . $token;
    }

    $context = stream_context_create($contextOptions);
    $result = @file_get_contents($url, false, $context);

    if ($result === false) {
        return ['error' => 'Failed to fetch GitHub API'];
    }

    $headers = $http_response_header ?? [];
    $rateLimit = ['remaining' => 60, 'limit' => 60];

    foreach ($headers as $header) {
        if (stripos($header, 'X-RateLimit-Remaining:') === 0) {
            $rateLimit['remaining'] = intval(trim(substr($header, 22)));
        }
        if (stripos($header, 'X-RateLimit-Limit:') === 0) {
            $rateLimit['limit'] = intval(trim(substr($header, 19)));
        }
    }

    $data = json_decode($result, true);

    return [
        'data' => $data,
        'rate_limit' => $rateLimit
    ];
}

switch ($type) {
    case 'repos':
        $count = isset($_GET['count']) ? intval($_GET['count']) : 10;
        $exclude = isset($_GET['exclude']) ? explode(',', $_GET['exclude']) : ['.github'];
        $cacheKey = 'repos_' . md5($user . $count . implode(',', $exclude));

        $cacheFile = __DIR__ . '/cache/' . $cacheKey . '.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
            echo file_get_contents($cacheFile);
            exit;
        }

        $url = "https://api.github.com/users/{$user}/repos?sort=updated&per_page=100&type=owner";
        $response = fetchGitHubApi($url, $githubToken, $sslVerify);

        if (isset($response['error'])) {
            echo json_encode(['error' => $response['error']], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $repos = $response['data'];
        $filtered = [];

        foreach ($repos as $repo) {
            $shouldExclude = false;
            foreach ($exclude as $pattern) {
                if (preg_match('/' . preg_quote(trim($pattern), '/') . '/', $repo['name'])) {
                    $shouldExclude = true;
                    break;
                }
            }

            if (!$shouldExclude && !$repo['fork'] && $repo['private'] === false) {
                $filtered[] = [
                    'name' => $repo['name'],
                    'full_name' => $repo['full_name'],
                    'description' => $repo['description'],
                    'html_url' => $repo['html_url'],
                    'stargazers_count' => $repo['stargazers_count'],
                    'forks_count' => $repo['forks_count'],
                    'language' => $repo['language'],
                    'updated_at' => $repo['updated_at'],
                    'topics' => $repo['topics'] ?? [],
                    'homepage' => $repo['homepage']
                ];
            }

            if (count($filtered) >= $count) break;
        }

        usort($filtered, function($a, $b) {
            return $b['stargazers_count'] - $a['stargazers_count'];
        });

        $result = [
            'repositories' => array_slice($filtered, 0, $count),
            'total' => count($filtered),
            'fetched' => date('c'),
            'rate_limit' => $response['rate_limit'] ?? null
        ];

        $jsonResponse = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        @file_put_contents($cacheFile, $jsonResponse);
        echo $jsonResponse;
        break;

    case 'contributions':
        $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
        $cacheKey = 'contrib_' . md5($user . $year);

        $cacheFile = __DIR__ . '/cache/' . $cacheKey . '.json';

        if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
            echo file_get_contents($cacheFile);
            exit;
        }

        $url = "https://api.github.com/users/{$user}/events?per_page=100&page=1";

        if (!empty($githubToken)) {
            $contextOptions = [
                'http' => [
                    'timeout' => 15,
                    'ignore_errors' => true,
                    'header' => [
                        "User-Agent: MoeHome/1.0",
                        "Accept: application/vnd.github.v3+json",
                        "Authorization: token " . $githubToken
                    ]
                ],
                'ssl' => [
                    'verify_peer' => $sslVerify,
                    'verify_peer_name' => $sslVerify
                ]
            ];
            $context = stream_context_create($contextOptions);
            $result = @file_get_contents($url, false, $context);
        } else {
            $result = @file_get_contents($url);
        }

        if ($result === false) {
            $contributions = generateRandomContributions($year);
        } else {
            $events = json_decode($result, true);
            $contributions = processContributions($events, $year);
        }

        $result = [
            'username' => $user,
            'year' => $year,
            'contributions' => $contributions,
            'total' => array_sum(array_column($contributions, 'count')),
            'fetched' => date('c')
        ];

        $jsonResponse = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        @file_put_contents($cacheFile, $jsonResponse);
        echo $jsonResponse;
        break;

    default:
        echo json_encode([
            'error' => 'Invalid type. Use: repos, contributions',
            'usage' => [
                '/api/github.php?type=repos&user=username',
                '/api/github.php?type=contributions&user=username&year=2024'
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
}

function processContributions($events, $year) {
    $contributions = [];

    for ($month = 0; $month < 12; $month++) {
        for ($day = 0; $day < 31; $day++) {
            $contributions[sprintf('%d-%02d-%02d', $year, $month + 1, $day + 1)] = [
                'date' => sprintf('%d-%02d-%02d', $year, $month + 1, $day + 1),
                'count' => 0,
                'level' => 0
            ];
        }
    }

    if (!is_array($events)) return $contributions;

    foreach ($events as $event) {
        if (!isset($event['created_at'])) continue;

        $eventDate = date('Y-m-d', strtotime($event['created_at']));
        if (isset($contributions[$eventDate])) {
            $contributions[$eventDate]['count']++;
        }
    }

    $maxCount = max(array_column($contributions, 'count'));
    foreach ($contributions as $date => &$data) {
        $data['level'] = calculateLevel($data['count'], $maxCount);
    }

    return $contributions;
}

function generateRandomContributions($year) {
    $contributions = [];

    for ($month = 0; $month < 12; $month++) {
        for ($day = 0; $day < 31; $day++) {
            $date = sprintf('%d-%02d-%02d', $year, $month + 1, $day + 1);
            $dayOfWeek = date('w', strtotime($date));

            $probability = ($dayOfWeek == 0 || $dayOfWeek == 6) ? 0.4 : 0.7;
            $count = (mt_rand() / mt_getrandmax()) < $probability ? mt_rand(0, 8) : 0;

            $contributions[$date] = [
                'date' => $date,
                'count' => $count,
                'level' => calculateLevel($count, 8)
            ];
        }
    }

    return $contributions;
}

function calculateLevel($count, $max) {
    if ($count === 0) return 0;
    if ($max === 0) return 1;
    $ratio = $count / $max;
    if ($ratio > 0.75) return 4;
    if ($ratio > 0.5) return 3;
    if ($ratio > 0.25) return 2;
    return 1;
}
