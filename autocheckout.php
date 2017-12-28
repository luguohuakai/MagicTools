<?php

/**
 * Class Conf 需要手动配置的地方
 */
class Conf
{
    protected $token = '2734&^YG^R%%*&G%R&*&G^J39d'; // 与git服务器验证的token
    protected $dir = '/srun3/www/srun4-mgr/'; // 要检出或pull代码的地方
    protected $version = '/srun3/www/srun4-mgr/version.ini'; // 检出完成后版本文件
    protected $error_log = 'git/error'; // 检出时错误日志
    protected $info_log = 'git/info'; // 检出时信息日志
    protected $branch = 'srun_box'; // 要检出的分支
    protected $git_path = '/usr/local/bin/git'; // git 所在绝对路径

    protected $is_send_out = false; // 是否发送到其它服务器
    protected $servers = []; // 服务器列表 url

    protected $is_prod = false; // 当前是否是生成环境
    protected $prod_url = 'http://47.104.1.91/autocheckout.php';
    protected $update_to_prod = ''; // 生产环境检出标识 {"commit_msg":"xxxx","update_to_prod":"1"} // 这个参数没用到
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
            . "\r\n" . 'module' . '/' . 'controller' . '/' . 'action'
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

    // 自动检出
    public function checkout()
    {
        $token = $this->token;
// 要检出或pull代码的地方
        $dir = $this->dir;
        $version = $this->version;
        $git_path = $this->git_path;
        $data = json_decode(file_get_contents('php://input'));
        $this->L(json_encode($data), $this->info_log);

// 验证token
        if ($data->token !== $token) {
            $this->L('token error', $this->error_log);
            exit('token error');
        }

        // 验证成功后是否发送到其它服务器
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

// 只处理push
        if ($data->event !== 'push') {
            $this->L('you are not push', $this->error_log);
            exit('you are not push');
        }

// 只处理srun_box分支
        if (!preg_match("/{$this->branch}/", $data->ref)) {
            $this->L("you are not {$this->branch}", $this->error_log);
            exit("you are not {$this->branch}");
        }

        // 是否生产环境
        try {
            $commit_msg = $data->commits{0}->short_message;
            if ($commit_msg) {
                $commit_msg = json_decode($commit_msg);
            }
            if ($this->is_prod && $commit_msg->update_to_prod) {
                goto begin;
            } else {
                if ($this->prod_url) {
                    if ($commit_msg->update_to_prod) {
                        // 发送到生产环境
                        $this->post($this->prod_url, file_get_contents('php://input'));
                    }
                }
            }
        } catch (\Exception $e) {
            $this->L($e->getMessage(), $this->error_log);
        }

        begin:
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

// 运行
// 请放置于网站中能访问到的地方
// 置于网站80端口对应的根目录 确保根目录权限 777 (创建文件和目录)
(new Git())->checkout();