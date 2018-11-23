<?php
namespace Common\Tool;
use Common\Lib\Auth\JWT;

/**
 *
 * Tools 辅助工具类
 * 提供通用的工具
 * @package Common\Lib\Tools
 * @author dxq1994@gmail.com
 * @version
 * v2018/5/16 下午2:40 初版
 */
class Tools {

    /**
     * 递归列取目录
     * @author dxq1994@gmail.com
     * @version v2018/11/7 下午6:05 初版
     * @param $dir
     * @return array
     */
    public static function dirToArray($dir) {
        $result = array();
        $cdir = scandir($dir);
        foreach ($cdir as $key => $value)
        {
            if (!in_array($value,array(".","..",".DS_Store")))
            {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value))
                {
                    $result[$value] = self::dirToArray($dir . DIRECTORY_SEPARATOR . $value);
                }
                else
                {
                    $result[] = $value;
                }
            }
        }

        return $result;
    }

    public static function token($uid,$source='pc',$time=2592000) {
        // 成功
        $tokenBody = array(
            'iss' => APP_NAME,
            'uid' => $uid,
            'iat' => time(),
            'exp' => time()+$time
        );
        $jwt = JWT::encode($tokenBody, SECRET_KEY, 'HS256');
        // 设置用户登录时间30天 redis
        \PhalApi\DI()->cache->set(TOKEN_KEY.$uid.':'.$source, $jwt, $time);
        return $jwt;
    }

    public static function checkToken($token){
        $tokenObj = JWT::decode($token, SECRET_KEY, ['HS256']);
        if(!$tokenObj) return false;
        if ($tokenObj->exp < time()) {
            return false;
        }
        return true;

    }

    /**
     * 获取当月第一天
     * @author dxq1994@gmail.com
     * @version v2018/7/30 下午2:20 初版
     * @param $time
     * @return false|string
     */
    public static function curMonthFirstDay($time=0)
    {
        if(!$time) return date('Y-m-01');
        return date('Y-m-01', $time);
    }

    /**
     * 获取当月最后一天
     * @author dxq1994@gmail.com
     * @version v2018/7/30 下午2:20 初版
     * @param $time
     * @return false|string
     */
    public static function curMonthLastDay($time=0)
    {
        if(!$time) return date('Y-m-d', strtotime(date('Y-m-01') . ' +1 month -1 day'));
        return date('Y-m-d', strtotime(date('Y-m-01', $time) . ' +1 month -1 day'));
    }

    /**
     * 格式输出函数
     *
     * @access        public
     * @param        array    $arr       数组
     * @return        void | int | string | bool | array        comment
     */
    public static function dump($arr){
        echo '<pre>';
        var_dump($arr);
    }
    /**
     * 获取rules里的参数
     * 
     *  $res = Tools::initParams(PhalApi_Api::getApiRules(), $_REQUEST);
     *  Tools::dump($res);
     * @access        public
     * @param        array    $vars      需求字段数组
     * @param        array    $params     $_REQUEST
     * @return        void | int | string | bool | array        comment
     */
    public static function initParams($vars, $params, $filter = array('service', 'ksid', 'token')){
        $data = array();
        if(is_array($vars) && !empty($vars)){
            $allow = array_keys($vars);
            foreach ($params as $key => $var) {
                if (isset($params[$key]) && in_array($key, $allow) && !in_array($key, $filter)) {
                    $data[$key] = $params[$key];
                }
            }
        }
        return $data;
    }

    /**
     * 高效率数组去重
     * @author dxq1994@gmail.com
     * @version v2018/5/29 下午8:41 初版
     * @param $arr
     * @return array|null
     */
    public static function arrayOne($arr) {
        // 使用键值互换去重
        $arr = array_flip($arr);
        $arr = array_flip($arr);
        return $arr;
    }

    public static function findKey($arr,$value){
        $keys = [];
        foreach($arr as $k=>$v){
            if(in_array($value,$v))$keys[]=$k;
        }
        if ($keys) return $keys;
        return false;
    }

    public static function postMan($url,$data) {

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLINFO_HEADER_OUT=> true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => [
                "cache-control: no-cache",
                "content-type: application/json",
            ]
        ]);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return false;
        } else {
            return $response;
        }
    }

    /**
     * 错误抛出
     *
     * @access       public
     * @param        $code
     * @param        $msg
     * @return       string
     */
    static public function E($code, $msg){
        $error = [
            'ret' => 200,
            'data' => [
                'code' => $code,
                'msg' => $msg,
            ],
            'msg' => '',
            'time' => date('Y/m/d H:i:s')
        ];
        echo json_encode($error,JSON_UNESCAPED_UNICODE);
        die;
    }

    /**
     * 获取随机位数验证码
     * @param  integer $len 长度
     * @return string
     */
    public static function randNumber($len = 6){
        $baseStr = '0123456789';
        $baseLen = strlen($baseStr);
        $number = ceil($len/$baseLen);
        $chars = str_repeat($baseStr, $number);
        $chars = str_shuffle($chars);
        $str   = substr($chars, 0, $len);
        return $str;
    }
    /**
     * 获取随机位数字母
     * @param  integer $len 长度
     * @return string
     */
    public static function randString($len = 6){
        $baseStr = 'abcdefghijklmnopqrstuvwxyz';
        $baseLen = strlen($baseStr);
        $number = ceil($len/$baseLen);
        $chars = str_repeat($baseStr, $number);
        $chars = str_shuffle($chars);
        $str   = substr($chars, 0, $len);
        return $str;
    }
    /**
     * 校验手机号
     *
     * @access        public
     * @param         string       $phone   手机号码
     * @return        int          成功返回1，失败返回0
     */
    static public function isMobile($phone)
    {
        return preg_match('/1[345789]\d{9}$/', $phone);
    }
    /**
     * 校验邮箱
     *
     * @access        public
     * @param         string       $email   邮箱
     * @return        int          成功返回1，失败返回0
     */
    static public function isEmail($email)
    {
        return preg_match('/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/', $email);
    }
    /**
     * 校验URL
     *
     * @access        public
     * @param         string       $str   字符串
     * @return        int          成功返回1，失败返回0
     */
    static public function isUrl($str){
        return preg_match('/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/', $str);
    }
    /**
     * 获取客户端ip
     *
     * @access        public
     * @return        string
     */
    public static function getIP()
    {
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $ip = getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $ip = getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $ip = getenv('HTTP_FORWARDED');
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    /**
     *  参数过滤重组，获取重组参数为一维数组
     *
     * @access        public
     * @param         array       $params        获取参数数组
     * @param         array       $filter        过滤参数
     * @param         array       $default       默认过滤
     * @return        array
     */
    public static function handle($params, $filter = array(), $default = array('service', 'ksid', 'page', 'token'))
    {
        $data = array();
        if(is_array($params) && !empty($params)){
            // 默认禁用参数+外部临时禁用参数
            $disable = array_merge($filter, $default);
            foreach ($params as $key => $var) {
                if (isset($params[$key]) && !is_null($params[$key]) && !in_array($key, $disable)) {
                    $data[$key] = $params[$key];
                }
            }
        }
        return $data;
    }
    /**
     * 获取随机生成的姓名
     *
     * @access        public
     * @param         int       $type
     * @return        array
     */
    static public function randChinaName($type = 0)
    {
        $xingArr = array('赵','钱','孙','李','周','吴','郑','王','冯','陈','褚','卫','蒋','沈','韩','杨','朱','秦','尤','许','何','吕','施','张','孔','曹','严','华','金','魏','陶','姜','戚','谢','邹','喻','柏','水','窦','章','云','苏','潘','葛','奚','范','彭','郎','鲁','韦','昌','马','苗','凤','花','方','任','袁','柳','鲍','史','唐','费','薛','雷','贺','倪','汤','滕','殷','罗','毕','郝','安','常','傅','卞','齐','元','顾','孟','平','黄','穆','萧','尹','姚','邵','湛','汪','祁','毛','狄','米','伏','成','戴','谈','宋','茅','庞','熊','纪','舒','屈','项','祝','董','梁','杜','阮','蓝','闵','季','贾','路','娄','江','童','颜','郭','梅','盛','林','钟','徐','邱','骆','高','夏','蔡','田','樊','胡','凌','霍','虞','万','支','柯','管','卢','莫','柯','房','裘','缪','解','应','宗','丁','宣','邓','单','杭','洪','包','诸','左','石','崔','吉','龚','程','嵇','邢','裴','陆','荣','翁','荀','于','惠','甄','曲','封','储','仲','伊','宁','仇','甘','武','符','刘','景','詹','龙','叶','幸','司','黎','溥','印','怀','蒲','邰','从','索','赖','卓','屠','池','乔','胥','闻','莘','党','翟','谭','贡','劳','逄','姬','申','扶','堵','冉','宰','雍','桑','寿','通','燕','浦','尚','农','温','别','庄','晏','柴','瞿','阎','连','习','容','向','古','易','廖','庾','终','步','都','耿','满','弘','匡','国','文','寇','广','禄','阙','东','欧','利','师','巩','聂','关','荆','司马','上官','欧阳','夏侯','诸葛','闻人','东方','赫连','皇甫','尉迟','公羊','澹台','公冶','宗政','濮阳','淳于','单于','太叔','申屠','公孙','仲孙','轩辕','令狐','徐离','宇文','长孙','慕容','司徒','司空');
        $xingNum = count($xingArr);
        $mingArr = array('伟','刚','勇','毅','俊','峰','强','军','平','保','东','文','辉','力','明','永','健','世','广','志','义','兴','良','海','山','仁','波','宁','贵','福','生','龙','元','全','国','胜','学','祥','才','发','武','新','利','清','飞','彬','富','顺','信','子','杰','涛','昌','成','康','星','光','天','达','安','岩','中','茂','进','林','有','坚','和','彪','博','诚','先','敬','震','振','壮','会','思','群','豪','心','邦','承','乐','绍','功','松','善','厚','庆','磊','民','友','裕','河','哲','江','超','浩','亮','政','谦','亨','奇','固','之','轮','翰','朗','伯','宏','言','若','鸣','朋','斌','梁','栋','维','启','克','伦','翔','旭','鹏','泽','晨','辰','士','以','建','家','致','树','炎','德','行','时','泰','盛','雄','琛','钧','冠','策','腾','楠','榕','风','航','弘','秀','娟','英','华','慧','巧','美','娜','静','淑','惠','珠','翠','雅','芝','玉','萍','红','娥','玲','芬','芳','燕','彩','春','菊','兰','凤','洁','梅','琳','素','云','莲','真','环','雪','荣','爱','妹','霞','香','月','莺','媛','艳','瑞','凡','佳','嘉','琼','勤','珍','贞','莉','桂','娣','叶','璧','璐','娅','琦','晶','妍','茜','秋','珊','莎','锦','黛','青','倩','婷','姣','婉','娴','瑾','颖','露','瑶','怡','婵','雁','蓓','纨','仪','荷','丹','蓉','眉','君','琴','蕊','薇','菁','梦','岚','苑','婕','馨','瑗','琰','韵','融','园','艺','咏','卿','聪','澜','纯','毓','悦','昭','冰','爽','琬','茗','羽','希','欣','飘','育','滢','馥','筠','柔','竹','霭','凝','晓','欢','霄','枫','芸','菲','寒','伊','亚','宜','可','姬','舒','影','荔','枝','丽','阳','妮','宝','贝','初','程','梵','罡','恒','鸿','桦','骅','剑','娇','纪','宽','苛','灵','玛','媚','琪','晴','容','睿','烁','堂','唯','威','韦','雯','苇','萱','阅','彦','宇','雨','洋','忠','宗','曼','紫','逸','贤','蝶','菡','绿','蓝','儿','翠','烟');
        $mingNum = count($mingArr);
        switch ($type) {
            case 1:
                // 2字
                $name = $xingArr[mt_rand(0, $xingNum)] . $mingArr[mt_rand(0, $mingNum)];
                break;
            case 2:
                // 随机2、3个字
                $name = $xingArr[mt_rand(0, $xingNum)] . $mingArr[mt_rand(0, $mingNum)];
                if (mt_rand (0, 100) > 50)
                    $name .= $mingArr[mt_rand(0, $mingNum)];
                break;
            case 3:
                // 只取姓
                $name = $xingArr[mt_rand(0, $xingNum)];
                break;
            case 4:
                // 只取名
                $name = $mingArr[mt_rand(0, $mingNum)];
                break;
            case 0:
            default:
                // 默认情况 1姓+2名
                $name = $xingArr[mt_rand(0, $xingNum)] . $mingArr[mt_rand(0, $mingNum)] . $mingArr[mt_rand(0, $mingNum)];
                break;
        }
        return $name;
    }
    /**
     * 上传图片
     *
     * @access        public
     * @param         string        $name        生成的头像名
     * @return        array
     */
    static public function uploadimg($name){
        if(empty($name)){
            return '请输入头像名称';
        }
        $allow = array('jpg', 'png', 'jpeg');
        $max_size = 1024 * 1024;
        $save_path = dirname(dirname(API_ROOT)) . '/upload/avatar/';
        if (! is_dir($save_path)) {
            mkdir($save_path, 0755, true);
        }
        // 有上传文件时
        if (empty($_FILES) === false) {
            $picname = $_FILES['avatar']['name'];
            $picsize = $_FILES['avatar']['size'];
            $tmp_name = $_FILES['avatar']['tmp_name'];
            if(! empty($picname)){
                if ($picsize > $max_size) {
                    return '文件大小不能超过' . $max_size / (1024*1024) . 'Mb';
                }
                $type = strtolower(trim(substr(strrchr($picname, '.'), 1, 10)));
                if(! in_array($type, $allow)){
                    return '选择的图片格式错误';
                }
                // 头像名
                $avatar_name = $name . '.' . $type;
                // 上传路径
                $pic_path = $save_path . $avatar_name;
                if(move_uploaded_file($tmp_name, $pic_path)){
                    $arr = array(
                        'src_name'=>$picname,
                        'avatar_name'=>$avatar_name,
                        'avatar_path'=>str_replace(dirname(dirname(API_ROOT)), URL_ROOT, $pic_path),
                        'avatar_size'=>$picsize
                    );
                    return $arr;
                }else{
                    return '上传失败';
                }
            }
        }else{
            return '请上传头像';
        }
    }
    /**
     * 上传图片
     *
     * @access        public
     * @return        array
     */
    static public function uploadLog(){
        $allow = array('zip', 'rar', '7z');
        $max_size = 10*1024 * 1024;
        $save_path = dirname(dirname(API_ROOT)) . '/../files/upload/log/';
        if (! is_dir($save_path)) {
            mkdir($save_path, 0755, true);
        }
        // 有上传文件时
        if (empty($_FILES) === false) {
            $name = $_FILES['data']['name'];
            if(file_exists($save_path . $name)){
                unlink($save_path . $name);
            }
            $size = $_FILES['data']['size'];
            $tmp_name = $_FILES['data']['tmp_name'];
            if(!empty($name)){
                if ($size > $max_size) {
                    return '文件大小不能超过' . $max_size / (1024*1024) . 'Mb';
                }
                $type = strtolower(trim(substr(strrchr($name, '.'), 1, 10)));
                if(! in_array($type, $allow)){
                    return '上传的文件格式错误';
                }
                // 上传路径
                $file_path = $save_path . $name;
                if(move_uploaded_file($tmp_name, $file_path)){
                    if(PATH_SEPARATOR == ':'){
                        $file_path = str_replace(dirname(dirname(API_ROOT)).'/../files', FILE_ROOT, $file_path);
                    }else{
                        $file_path = str_replace(dirname(dirname(API_ROOT)), URL_ROOT, $file_path);
                    }
                    $arr = array(
                        'file_path'=>$file_path
                    );
                    return $arr;
                }else{
                    return '上传失败';
                }
            }
        }else{
            return '请上传文件';
        }
    }
    /**
     * 上传二进制流图片
     *
     * @access        public
     * @param         string        $name        生成的头像名
     * @return        array
     */
    static public function uploadstreamimg($name,$dir_name = 'User',$url=0,$size_arr=array('100','480') ){
        if(empty($name)){
            return '请输入头像名称';
        }
        $allow = array('jpg','png','jpeg');
        $max_size = 1024 * 1024;
        //预先设置name
        $tmp_name = $name. self::getMillisecond().'.';

        if ( ($dir_name != 'User') && ($dir_name != 'Group') ) {
            //目录检测
            $save_path = dirname(dirname(API_ROOT)) . '/../files/upload/avatar/' . $dir_name . '/';
            if (!is_dir($save_path)) mkdir($save_path, 0755, true);

            //二进制数据流
            $data = DI()->request->get('avatar');

            //数据流不为空，则进行保存操作
            if(! empty($data) ){
                // base64解密为二进制
                $stream = base64_decode($data);
                // 判断文件大小
                $picsize = strlen($stream);
                if ($picsize > $max_size) {
                    return '文件大小不能超过' . $max_size / (1024*1024) . 'Mb';
                }
                $bin = substr($stream, 0, 2);
                $strInfo = @unpack("C2chars", $bin);
                $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
                $fileType = '';
                switch ($typeCode){
                    case 7790:
                        $fileType = 'exe';
                        break;
                    case 7784:
                        $fileType = 'midi';
                        break;
                    case 8297:
                        $fileType = 'rar';
                        break;
                    case 255216:
                        $fileType = 'jpg';
                        break;
                    case 7173:
                        $fileType = 'gif';
                        break;
                    case 6677:
                        $fileType = 'bmp';
                        break;
                    case 13780:
                        $fileType = 'png';
                        break;
                    default:
                        $fileType = 'unknown';
                        break;
                }

                if(! in_array($fileType, $allow)) return '选择的图片格式错误';

                // 头像名
                $avatar_name = $tmp_name.$fileType;
                // 上传路径
                $pic_path = $save_path . $avatar_name;
                //创建并写入数据流，然后保存文件
                if (@$fp = fopen($pic_path, 'w+')){
                    fwrite ($fp, $stream);
                    fclose ($fp);

                    // 用户头像名策略更改，如果上传新的头像，删除过去的头像
                    if ($url) {
                        //http://stage.api.kingsmith.com.cn/upload/avatar/User/100/20688xxxxx.jpg
                        $tmp = explode('/', $url);
                        $img_name = end($tmp);
                        if(file_exists($save_path . $img_name )){
                            unlink($save_path . $img_name );
                        }
                    }

                    $imageInfo = getimagesize($pic_path);
                    if($imageInfo === false){
                        return '非法图片';
                    }
                    if(PATH_SEPARATOR == ':'){
                        $avatar_path = str_replace(dirname(dirname(API_ROOT)).'/../files', FILE_ROOT, $pic_path);
                    }else{
                        $avatar_path = str_replace(dirname(dirname(API_ROOT)), URL_ROOT, $pic_path);
                    }
                    $arr = array(
                        'avatar_name'=>$avatar_name,
                        'avatar_path'=>$avatar_path,
                        'avatar_size'=>$picsize
                    );
                }else{
                    return '打开图片失败';
                }

            }else{
                return '没有接收到数据流';
            }
        }else{
            //循环保存图片
            foreach ($size_arr as $k => $v) {

                $save_path = dirname(dirname(API_ROOT)) . '/../files/upload/avatar/' . $dir_name . '/'.$v.'/';

                if (!is_dir($save_path)) mkdir($save_path, 0755, true);
                //兼容旧API
                if ( $v==480 && !DI()->request->get('avatar'.$v)){
                    copy($pic_path, $save_path.$avatar_name);
                    break;
                }

                //二进制数据流
                if ($v==100) {
                    $data = DI()->request->get('avatar');
                }else{
                    $data = DI()->request->get('avatar'.$v);
                }
                //数据流不为空，则进行保存操作

                if(! empty($data) ){
                    // base64解密为二进制
                    $stream = base64_decode($data);
                    // 判断文件大小
                    $picsize = strlen($stream);
                    if ($picsize > $max_size) {
                        return '文件大小不能超过' . $max_size / (1024*1024) . 'Mb';
                    }
                    $bin = substr($stream, 0, 2);
                    $strInfo = @unpack("C2chars", $bin);
                    $typeCode = intval($strInfo['chars1'] . $strInfo['chars2']);
                    $fileType = '';
                    switch ($typeCode){
                        case 7790:
                            $fileType = 'exe';
                            break;
                        case 7784:
                            $fileType = 'midi';
                            break;
                        case 8297:
                            $fileType = 'rar';
                            break;
                        case 255216:
                            $fileType = 'jpg';
                            break;
                        case 7173:
                            $fileType = 'gif';
                            break;
                        case 6677:
                            $fileType = 'bmp';
                            break;
                        case 13780:
                            $fileType = 'png';
                            break;
                        default:
                            $fileType = 'unknown';
                            break;
                    }

                    if(! in_array($fileType, $allow)) return '选择的图片格式错误';

                    // 头像名
                    $avatar_name = $tmp_name.$fileType;
                    // 上传路径
                    $pic_path = $save_path . $avatar_name;
                    //创建并写入数据流，然后保存文件
                    if (@$fp = fopen($pic_path, 'w+')){
                        fwrite ($fp, $stream);
                        fclose ($fp);

                        // 用户头像名策略更改，如果上传新的头像，删除过去的头像
                        if ($url) {
                            //http://stage.api.kingsmith.com.cn/upload/avatar/User/100/20688xxxxx.jpg
                            $tmp = explode('/', $url);
                            $img_name = end($tmp);
                            if(file_exists($save_path . $img_name )){
                                unlink($save_path . $img_name );
                            }
                        }

                        $imageInfo = getimagesize($pic_path);
                        if($imageInfo === false){
                            return '非法图片';
                        }
                        if(PATH_SEPARATOR == ':'){
                            $avatar_path = str_replace(dirname(dirname(API_ROOT)).'/../files', FILE_ROOT, $pic_path);
                        }else{
                            $avatar_path = str_replace(dirname(dirname(API_ROOT)), URL_ROOT, $pic_path);
                        }
                        $arr[$k] = array(
                            'avatar_name'=>$avatar_name,
                            'avatar_path'=>$avatar_path,
                            'avatar_size'=>$picsize
                        );
                    }else{
                        return '打开图片失败';
                    }

                }else{
                    return '没有接收到数据流';
                }

            }
        }
        return $arr;
    }
    /**
     * 获取微秒
     *
     * @access        public
     * @return        int
     */
    static public function getMillisecond() {
        list($t1, $t2) = explode(' ', microtime());
        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);
    }
    /**
     * 测试post上传json数据
     *
     * @access        public
     * @param         string        $json
     * @return        array
     */
    static public function postjson($json){
        // 初始化curl
        $ch = curl_init();
        // 设置链接
        curl_setopt($ch, CURLOPT_URL, 'http://192.168.1.170/running/V0.1/');
        // 设置是否返回信息
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // 设置HTTP头
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type:application/json;charset=UTF-8'));
        // 设置为POST方式
        curl_setopt($ch, CURLOPT_POST, 1);
        // POST数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        // 接收返回信息
        $response = curl_exec($ch);
        // 出错则显示错误信息
        if(curl_errno($ch)){
            return curl_error($ch);
        }
        // 关闭curl链接
        curl_close($ch);
        // 显示返回信息
        return $response;
    }
    /**
     * 是否移动设备访问接口（方便调试，调试信息pc端可见，手机移动端不可见）
     *
     * @access        public
     * @return        bool
     */
    static public function isMobileDev(){
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
            return true;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])){
            // 找不到为flase,否则为true
            return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])){
            $clientkeywords = array ('nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap',
                'mobile'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])){
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html'))))
            {
                return true;
            }
        }
        return false;
    }
    /**
     * 秒转换 (时)分秒格式
     *
     * @access        public
     * @return        bool
     */
    static public function second2date($seconds,$flag=false){
        $array = [];
        if($flag){
            $temp = floor($seconds / 3600);
            if($temp < 10){
                $temp = '0' . $temp;
            }
            $array[] = $temp;
            $seconds = $seconds % 3600;
        }

        $temp = floor($seconds / 60);
        if($temp < 10){
            $temp = '0' . $temp;
        }
        $array[] = $temp;
        $seconds = $seconds % 60;

        $temp = floor($seconds);
        if($temp < 10){
            $temp = '0' . $temp;
        }
        $array[] = $temp;
        return implode(':', $array);
    }
    /**
     * 秒转换秒表2'33"(返回时，跟字符串类型冲突2'33\")
     *
     * @access        public
     * @return        bool
     */
    static public function second2stopwatch($seconds){
        if($seconds == 0){
            return '0';
        }
        $minute = $second = $str = '';
        if($seconds >= 60){
            $minute = floor($seconds / 60);
            if ($minute<10){
                $minute = '0'.$minute;
            }else if($minute>99){
                return '99\'59"';
            }
            $str .= $minute . "'";
            $seconds = $seconds % 60;
        }
        $second = floor($seconds);
        if ($second<10){
            $second = '0'.$second;
        }
        $str .= $second . '"';
        return $str;
    }
    /**
     * 日期转化周几
     *
     * @param       $data
     * @param       string      $format
     * @return      string
     */
    static public function getWeekName($data, $format = '周'){
        $week = date("D ",$data);
        switch($week){
            case "Mon ":
                $current = $format."一";
                break;
            case "Tue ":
                $current = $format."二";
                break;
            case "Wed ":
                $current = $format."三";
                break;
            case "Thu ":
                $current = $format."四";
                break;
            case "Fri ":
                $current = $format."五";
                break;
            case "Sat ":
                $current = $format."六";
                break;
            case "Sun ":
                $current = $format."日";
                break;
        }
        return $current;
    }
    /**
     * 时间格式转化
     *
     * @param $datetime
     * @return string
     */
    static public function formatTime($datetime){
        $seconds = time() - strtotime($datetime);
        // 如果时间是今天，返回时间(H:i)
        if($seconds < 86400){
            return date('H:i', strtotime($datetime));
        }elseif($seconds < 86400*2){
            return '昨天';
        }elseif($seconds < 86400*3){
            return '前天';
        }elseif($seconds < 86400*30){
            return floor($seconds/86400) . '天前';
        }elseif($seconds < 86400*30*12){
            return floor($seconds/2592000) . '月前';
        }else{
            return floor($seconds/31536000) . '年前';
        }
    }
    /**
     * 时间格式转化1
     *
     * @param $timestamp
     * @return string
     */
    static public function agoTime($timestamp){
        $seconds = time() - $timestamp;
        if($seconds < 60){
            return '1分钟前';
        }elseif($seconds < 3600){
            return floor($seconds/60) . '分钟前';
        }elseif($seconds < 86400){
            return floor($seconds/3600) . '小时前';
        }elseif($seconds < 86400*30){
            return floor($seconds/86400) . '天前';
        }else{
            return '1月前';
        }
    }
    /**
     * 字符串加密、解密函数
     *
     *
     * @param	string	$txt		字符串
     * @param	string	$operation	ENCODE为加密，DECODE为解密，可选参数，默认为ENCODE，
     * @param	string	$key		密钥：数字、字母、下划线
     * @param	string	$expiry		过期时间
     * @return	string
     */
    static public function invitationCode($string, $operation = 'ENCODE', $key = '', $expiry = 0) {
        $key_length = 4;
        $key = md5($key != '' ? $key :'7SLahrT8anWkl7LfOBhW');
        $fixedkey = md5($key);
        $egiskeys = md5(substr($fixedkey, 16, 16));
        $runtokey = $key_length ? ($operation == 'ENCODE' ? substr(md5(microtime(true)), -$key_length) : substr($string, 0, $key_length)) : '';
        $keys = md5(substr($runtokey, 0, 16) . substr($fixedkey, 0, 16) . substr($runtokey, 16) . substr($fixedkey, 16));
        $string = $operation == 'ENCODE' ? sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$egiskeys), 0, 16) . $string : base64_decode(substr($string, $key_length));

        $i = 0; $result = '';
        $string_length = strlen($string);
        for ($i = 0; $i < $string_length; $i++){
            $result .= chr(ord($string{$i}) ^ ord($keys{$i % 32}));
        }
        if($operation == 'ENCODE') {
            return $runtokey . str_replace('=', '', base64_encode($result));
        } else {
            if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$egiskeys), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        }
    }
    /**
     * 发起一个post请求到指定接口
     *
     * @param string $api 请求的接口
     * @param array $params post参数
     * @param int $timeout 超时时间
     * @return string 请求结果
     */
    static public function postRequest( $api, array $params = array(), $timeout = 30 ) {
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $api );
        // 以返回的形式接收信息
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        // 设置为POST方式
        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query( $params ) );
        // 不验证https证书
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, 0 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, $timeout );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
            'Accept: application/json',
        ) );
        // 发送数据
        $response = curl_exec( $ch );
        // 不要忘记释放资源
        curl_close( $ch );
        return $response;
    }

    /**
     * @param array $data
     * @return string
     */
    static public function arrayToString($data){
        $where = '';
        foreach ($data as $k => $v) {
            $where .= is_int($k)? $v.' AND ' : '`' . $k . "`='" . $v . "' AND ";
        }
        return substr($where, 0, -5);
    }
    /**
     * 发起一个网络请求到指定接口
     *
     * @param string $URL 请求的接口
     * @param array $params post参数
     * @param int $timeout 超时时间
     * @return string 请求结果
     */
    function netHttp($URL,$type,$params,$headers=0){
        $ch = curl_init();
        $timeout = 5;
        curl_setopt ($ch, CURLOPT_URL, $URL); //发贴地址
        if($headers!=""){
            curl_setopt ($ch, CURLOPT_HTTPHEADER, $headers);
        }else {
            curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type: text/json'));
        }
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        switch ($type){
            case "GET" : curl_setopt($ch, CURLOPT_HTTPGET, true);break;
            case "POST": curl_setopt($ch, CURLOPT_POST,true);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$params);break;
            case "PUT" : curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS,$params);break;
            case "DELETE":curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_POSTFIELDS,$params);break;
        }
        $file_contents = curl_exec($ch);//获得返回值
        curl_close($ch);
        return $file_contents;
    }

    /**
     * 判断是几维数组
     * @param array
     */
    static function getArrLevel($vDim)
    {
      if(!is_array($vDim)) return 0;
      else
      {
        $max1 = 0;
        foreach($vDim as $item1)
        {
         $t1 = self::getArrLevel($item1);
         if( $t1 > $max1) $max1 = $t1;
        }
        return $max1 + 1;
      }
    }

}