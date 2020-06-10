<?php
namespace Open\Api;

use Common\Lib\CommonApi;

/**
 * 工具性质的接口
 * @author dxq1994@gmail.com
 * @package Web\Api
 */
class Open extends CommonApi {

	public function getRules() {
        return [
            'overtime' => [
                'username' 	=> ['name' => 'username', 'type' => 'string', 'require' => true, 'desc'=>'OA账号'],
                'pwd' 	=> ['name' => 'pwd', 'type' => 'string', 'require' => true, 'desc' => 'OA密码'],
//                'JSESSIONID' 	=> ['name' => 'JSESSIONID', 'type' => 'string', 'require' => false, 'default' => '','desc'=>'这个在你登录后OA页面cookie里'],
                'month' 	=> ['name' => 'month', 'type' => 'string', 'default' => date('Y-m'), 'desc' => '查询的月份，注意格式xxxx-xx'],
            ],

            'sendOvertime' => [
                'username' 	=> ['name' => 'username', 'type' => 'string', 'require' => true, 'desc'=>'OA账号'],
                'pwd' 	=> ['name' => 'pwd', 'type' => 'string', 'require' => true, 'desc' => 'OA密码'],
                'comment' 	=> ['name' => 'comment', 'type' => 'string', 'require' => true, 'default' => '视频报告项目开发','desc'=>'加班原因'],
//                'JSESSIONID' 	=> ['name' => 'JSESSIONID', 'type' => 'string', 'require' => false,'default' => '','desc'=>'这个在你登录后OA页面cookie里'],
                'month' 	=> ['name' => 'month', 'type' => 'string', 'default' => date('Y-m'), 'desc' => '默认提交当月的加班，注意格式xxxx-xx'],
            ],

            'test'=>[],
            'task'=>[],


        ];
	}

    /**
     * 查看某月加班记录
     * @desc 提取某月的加班信息，该接口只会显示未提交的加班信息，已发送协同的则不显示
     * @author dxq1994@gmail.com
     * @version v2018/8/16 上午10:33 初版
     * @return array info 加班信息
     * @return string msg 提示信息
     * @return int info.a 哪月
     */
	public function overtime() {
        return resFormat([
            'a'=>1,
            'b'=>1,
            'c'=>1,
            'd'=>1
        ]);
        return \Open\Domain\Open::overtimeList($this);
	}

    /**
     * 提交加班到OA
     * @desc 提交某月的加班，该操作会一次性提交所有已产生的加班到OA
     * @author dxq1994@gmail.com
     * @version v2018/8/16 上午10:33 初版
     * @return array 加班信息
     */
    public function sendOvertime() {
        return \Open\Domain\Open::sendOvertime($this);
    }

    /**
     * 测试接口
     * @author dxq1994@gmail.com
     * @version v2018/8/21 下午1:48 初版
     * @return array
     */
    public function test() {

        return \Open\Domain\Open::test();
    }

    /**
     * 执行队列任务
     * @author dxq1994@gmail.com
     * @version v2018/8/16 上午10:34 初版
     */
    public function task() {
        \Open\Domain\Open::task();
    }
}
