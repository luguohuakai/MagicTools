<?php

/**
 * Class Conf 需要手动配置的地方
 */
class Conf
{
    protected $token = '2734&^YG^R%%*&G%R&*&G^J39d'; // 与git服务器验证的token
    protected $error_log = 'git/error'; // 检出时错误日志
    protected $info_log = 'git/info'; // 检出时信息日志
    protected $git_path = '/usr/local/bin/git'; // git 所在绝对路径

    protected $dir = '/srun3/www/srun4-mgr/'; // 要检出或pull代码的地方
    protected $version = '/srun3/www/srun4-mgr/version.ini'; // 检出完成后版本文件
    protected $branch = 'srun_box'; // 要检出的分支

    // 按如下配置当前服务器要pull的仓库路径
    protected $projects = [
        'srun_box' => [ // 分支名
            'dir' => '/srun3/www/srun4-mgr/', // 仓库根目录
            'version' => '/srun3/www/srun4-mgr/version.ini', // 版本文件生成位置
        ],
        'eduroam_local' => [ // 分支名
            'dir' => '/srun3/www/eduroamlocal/', // 仓库根目录
            'version' => '/srun3/www/eduroamlocal/version.ini', // 版本文件生成位置
        ],
        'eduroam分析' => [ // 分支名
            'dir' => '/srun3/www/eduroamflr/', // 仓库根目录
            'version' => '/srun3/www/eduroamflr/version.ini', // 版本文件生成位置
        ],
    ];

    protected $is_prod = false; // 当前是否是生产环境
    protected $prod_url = 'http://47.104.1.91/autocheckout.php';
    protected $update_to_prod = ''; // 生产环境检出标识 {"commit_msg":"xxxx","update_to_prod":"1"} // 这个参数没用到

    protected $is_send_out = false; // 是否发送到其它服务器
    protected $servers = []; // 服务器列表 url
}

/**
 * Trait Tool 工具集
 */
trait Tool
{
    /**
     * @param $msg
     * @param string $file_name 如: error 或 srun/error/detail
     */
    function L($msg, $file_name)
    {
        $log_root_path = __DIR__ . '/My-logs/';
        $log_path_and_file = $log_root_path . $file_name;
        $log_final_path = str_replace(strrchr($log_path_and_file, '/'), '', $log_path_and_file);

        if (!is_dir($log_final_path)) {
            mkdir($log_final_path, 0777, true);
        }

        $msg = date('Y-m-d H:i:s')
            . "\r\n" . $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
            . "\r\n输出信息:" . $msg
            . "\r\n\r\n";
        error_log($msg, 3, $log_path_and_file . '_' . date('Y-m-d') . '.txt');
    }

    /**
     * @param $url
     * @param array|string $post_data
     * @return mixed
     */
    function post($url, $post_data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// SSL证书认证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);// SSL证书认证
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);

        return $output;
    }
}

/**
 * Class Run
 * Git 自动部署
 * Created by PhpStorm.
 * User: DM
 * Date: 2017/12/27
 * Time: 17:14
 */
class Git extends Conf
{
    use Tool;

    protected $data; // 接收到的数据
    protected $curr_branch_name = ''; // 当前推送的分支

    public function __construct()
    {
        $this->data = json_decode(file_get_contents('php://input'));
        if (!$this->data) exit;
        // 验证token
        $this->validateToken();
        // 发送到其它服务器
        $this->sendOut();
        // 只处理push
        $this->justDealPush();
        // 检查当前分支是否需要处理
        $this->curr_branch_name = $this->isDealCurrBranch();
        // 检查当前是否是生产环境
        $this->isProd();
    }

    // 自动检出
    public function checkout()
    {
        // 要检出或pull代码的地方
        $git_path = $this->git_path;
        $this->L(json_encode($this->data), $this->info_log);

        // 检出
        if ($this->curr_branch_name) {
            $dir = $this->projects[$this->curr_branch_name]['dir'];
            $version = $this->projects[$this->curr_branch_name]['version'];
            // 拉代码
            $output = [];
//if (is_file($dir . $version)) {
            // 第二次及之后直接pull代码
            $command = "cd {$dir} && {$git_path} pull";
            $output[] = shell_exec($command);
            $this->L($command, $this->info_log);
//} else {
            // 有确认 应该也可以解决
//        // 第一次先克隆远程代码 切换到dev分支
//        $output[] = shell_exec("cd {$dir} && git clone {$data->repository->ssh_url} 2>&1");
//        $this->L("cd {$dir} && git clone {$data->repository->ssh_url} 2>&1",$this->file_name);
//        $output[] = shell_exec("cd {$dir}{$data->repository->name} && git -c core.quotepath=false -c log.showSignature=false checkout -b dev origin/dev 2>&1");
//        $this->L("cd {$dir}{$data->repository->name} && git -c core.quotepath=false -c log.showSignature=false checkout -b dev origin/dev 2>&1",$this->file_name);
//}

            // 生成版本信息
            $git_version_commit_num = shell_exec("cd {$dir} && {$git_path} rev-list HEAD | wc -l | awk '{print $1}'");
            $git_version_hash_min = shell_exec("cd {$dir} && {$git_path} rev-list HEAD --abbrev-commit --max-count=1");
            $git_version_hash_max = shell_exec("cd {$dir} && {$git_path} rev-parse HEAD");
            $arr['author'] = 'DM';
            $arr['update_time'] = date('Y-m-d H:i:s');
            $arr['git_version_commit_num'] = trim($git_version_commit_num);
            $arr['git_version_hash_min'] = trim($git_version_hash_min);
            $arr['git_version_hash_max'] = trim($git_version_hash_max);
            $content = '';
            foreach ($arr as $k => $v) {
                $content .= "{$k}={$v}\r\n";
            }

            $this->L($content, $this->info_log);
            file_put_contents($version, $content);
            $this->L(json_encode($output), $this->info_log);
        }
    }

    // 验证token
    private function validateToken()
    {
        if ($this->data->token !== $this->token) {
            $this->L('token error', $this->error_log);
            exit('token error');
        }
    }

    // 验证成功后是否发送到其它服务器
    private function sendOut()
    {
        if ($this->is_send_out) {
            if (!empty($this->servers)) {
                foreach ($this->servers as $server) {
                    $rs = $this->post($server, file_get_contents('php://input'));
                    if ($rs) {
                        $this->L(json_encode($rs), $this->info_log);
                    }
                }
            }
        }
    }

    // 只处理push
    private function justDealPush()
    {
        if ($this->data->event !== 'push') {
            $this->L('you are not push', $this->error_log);
            exit('you are not push');
        }
    }

    // 检查当前分支是否需要处理
    private function isDealCurrBranch()
    {
        $branches = array_keys($this->projects);
        $branches_str = implode(',', $branches);
        $ref_arr = explode('/', $this->data->ref);
        $curr_branch_name = $ref_arr[count($ref_arr) - 1];
        if (!in_array($curr_branch_name, $branches)) {
            $this->L("you are not in these branches {$branches_str}", $this->error_log);
            exit("you are not in these branches {$branches_str}");
        } else {
            return $curr_branch_name;
        }
    }

    // 是否生产环境
    private function isProd()
    {
        // 在commit && push 代码时这样填写提交到生产环境
        // {"commit_msg":"xxxx","update_to_prod":"1"}
        try {
            $commit_msg = $this->data->commits{0}->short_message;
            if ($commit_msg) {
                $commit_msg = json_decode($commit_msg);
            }
            if ($this->is_prod && $commit_msg->update_to_prod) {
//                goto begin;
            } else {
                if ($this->prod_url) {
                    // 当前环境不是生产环境且有生产环境url才发送到生产环境
                    if ($commit_msg->update_to_prod) {
                        // 发送到生产环境
                        $this->post($this->prod_url, file_get_contents('php://input'));
                    }
                }
            }
        } catch (\Exception $e) {
            $this->L($e->getMessage(), $this->error_log);
        }
    }
}

// 运行
// 请放置于网站中能访问到的地方
// 置于网站80端口对应的根目录 确保根目录权限 777 (创建文件和目录)
(new Git())->checkout();