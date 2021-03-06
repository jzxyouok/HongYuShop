<?php
/**
 * Created by PhpStorm.
 * User: Shadow
 * Date: 2016/6/5
 * Time: 12:17
 */
namespace Admin\Model;
use Think\Model;
class AdminModel extends Model{

    //批处理验证-数组方式批量显示错误
    //protected $patchValidate = true;

    //验证规则
    protected $_validate = array(
        array('username','require','用户名必须填写！',1),
        array('password','require','密码必须填写！',1,'regex',1),
        array('password','require','密码必须填写！',1,'regex',4),
        array('username','','用户名已经存在！',1,'unique',1),
        array('username','','用户名已经存在！',1,'unique',2),
        //array('captcha','checkCaptcha','验证码不正确',0,'callback',4),
        array('email','email','邮箱格式不正确！',2 ),
        array('mobile_phone','/^1[0-9]\d{9}$/','请填写正确的手机号码！',2),
        //array('value',array(1,2,3),'值的范围不正确！',2,'in'), // 当值不为空的时候判断是否在一个范围内
    );

    public function login(){
        //当调用find方法之后tp会把数据库的记录付给这个模型,所以需要在调用find之前从模型中取出表单中的密码
        $password = $this->password;
        //根据用户名查询数据库中有没有此账号
        //相当于: SELECT * FROM hy_admin WHERE username='xx' LIMIT 1
        //find: 返回一维数组
        $info = $this->where("username='$this->username'")->find();
        if($info){
            if($info['password'] == md5($password)){
                session('id',$info['id']);
                session('username',$info['username']);
                return TRUE;
            }else{
                return 2;
            }
        }else{
            return 1;
        }
    }

    //判断用户名是否存在
    public function checkName($name){
        $info = $this ->getByusername($name);
        if($info != null){
            return $info['username'];
        }else{
            return false;
        }
    }

    //检查验证码
    public function checkCaptcha($captcha){
        $verify = new \Think\Verify();
        if(!$verify ->check($captcha)){
            return false;
        }else{
            return true;
        }
    }

    //插入数据之前执行钩子函数
    protected function _before_insert(&$data, $option){
        $data['password'] = md5($data['password']);
    }

    //修改数据之前执行钩子函数
    protected function _before_update(&$data, $option){
        if($data['password']){
            $data['password'] = md5($data['password']);
        }else{
            unset($data['password']);
        }
    }

    //搜索
    public function search(){
        $where = 1;
        if(isset($_GET['searchText']) && $_GET['searchText']){
            $where .= ' AND username LIKE "%'.$_GET['searchText'].'%"';
        }
        //每页条数
        $perpage = 15;
        //获取总的记录数
        $totalRecord = $this->where($where)->count();

        $Page = new \Think\Page($totalRecord,$perpage);
        $Page->setConfig('header','个管理员');
        $Page->setConfig('prev','上一页');
        $Page->setConfig('next','下一页');
        $Page->setConfig('first','首页');
        $Page->setConfig('ned','尾页');
        return array(
            'data' => $this->where($where)->limit($Page->firstRow,$Page->listRows)->select(),
            'page' => $Page->show(),
        );
    }
}