<?php
/**
 * 该命令是用再第一次采集，将当前所有文件上传，后续使用计划任务针对新增加的来上传
 * 注意：如果重复使用此命令请先删除数据库中已经存在的数据，避免后续的计划任务出问题
 *
 * php artisan collect:allresource
 */
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\CollectLog;

class CollectAll extends Command
{

    /**
     * @var array
     * @description  允许上传的文件类型
     */
    private $filter_ext = ['txt', 'jpg', 'jpeg', 'JPG', 'xml', 'JPEG', 'png', 'PNG', 'gif', 'GIF', 'TXT'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collect:allresource';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '采集指定目录中所有的媒资';

    private $api = '';
    private $appid = '';
    private $secret = '';
    private $siteid = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->api = env('COLLECT_UPLOAD_URL');
        $this->appid = env('COLLECT_OPEN_APPID');
        $this->secret = env('COLLECT_OPEN_SECRET');
        $this->siteid = env('COLLECT_TO_SITE');

        if (!$this->api || !$this->appid || !$this->secret || !$this->siteid) {
            die('请先配置采集参数');
        }

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 获取指定目录中所有的媒资
        $path = env('COLLECT_PATH');
        if (!$path) {
            return false;
        }
        $file_array = [];

        $this->tree($file_array, $path, $path);
        if (empty($file_array)) {
            return false;
        }
        try {
            // 循环上传获取所有的媒资
            foreach ($file_array as $file) {

                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $file_name = pathinfo($file, PATHINFO_FILENAME);

                if (!in_array($ext, $this->filter_ext)) {
                    continue;
                }
                $create = [
                    'file_path' => $file,
                    'file_name' => $file_name,
                    'file_dir' => substr($file_name, 0, 8),
                    'file_time' => substr($file_name, 9),
                ];
                if ($this->upload($file)) {
                    // 记录文件的类型、文件名
                    $create['file_status'] = 1;

                } else {
                    $create['file_status'] = 0;
                }

                CollectLog::create($create);
            }
        } catch (\Exception $exception) {

            die($exception->getMessage());

        }

        die('complate');

    }


    /**
     * @param $arr_file
     * @param $directory
     * @param string $dir_name
     *
     * @description 递归获取某个目录下的所有文件
     */
    private function tree(&$arr_file, $directory, $dir_name='')
    {

        $mydir = dir($directory);
        while($file = $mydir->read())
        {
            if((is_dir("$directory/$file")) AND ($file != ".") AND ($file != ".."))
            {
                $this->tree($arr_file, "$directory/$file", "$dir_name/$file");
            }
            else if(($file != ".") AND ($file != ".."))
            {
                $arr_file[] = "$dir_name/$file";
            }
        }
        $mydir->close();
    }

    /**
     * @param $file
     *
     * @description 上传附件到融媒体
     */
    private function upload($file)
    {
        $params['time'] = time();
        $params['site_id'] = $this->siteid;
        $params['appid'] = $this->appid;
        $params['sign'] = $this->getSign($params);
        $params['upload'] = new \CURLFile($file); // 文件信息等sign 生成在加

        $response = $this->requestImg($this->api, $params);

        $result = json_decode((string)$response, true);

        if ($result && $result['state']) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * @param $params
     */
    private function getSign($params)
    {
        ksort($params);
        $verify_sign = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        return md5(md5($verify_sign) . $this->appid . $this->secret . $params['time']);
    }

    /**
     * @param $url
     * @param $params
     */
    private function requestImg($url, $params)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

        $res = curl_exec($ch);

        return $res;
    }
}
