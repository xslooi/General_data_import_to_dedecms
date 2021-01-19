<?php
/**
 * 此文件为程序入口文件-ajax接口文件
 * 织梦数据导入主程序
 * ---------------------------------------------------------
 * # 版本功能说明：
 * V1：实现两个织梦对传数据（复制图片可选）
 * V2：实现其他PHP程序导入织梦（可以拖拉配置字段对应）—— 字段数据处理：1、转义；2、转换、3、截取
 * https://blog.csdn.net/zhangfeng2124/article/details/76672403  jqueryui 下一步
 * todo V2 问题：1、数据主表和附表字段名称相同； 2、一个字段可能需要多次替换内容处理
 * V3：实现asp或者aspx的数据导入
 * ---------------------------------------------------------
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/24
 * Time: 11:07
 */

ini_set('display_errors', 0);
error_reporting(E_ALL);
set_time_limit(0);  //不限制 执行时间
date_default_timezone_set('Asia/Shanghai');
header("content-Type: text/javascript; charset=utf-8"); //语言强制
header('Cache-Control:no-cache,must-revalidate');
header('Pragma:no-cache');
//===================================================================
//文件说明区
//===================================================================

//echo dirname(__FILE__);exit;
//===================================================================
//定义常量区
//===================================================================
define('IS_MAGIC_QUOTES_GPC', get_magic_quotes_gpc()); //todo 转义常量暂未使用
define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) ? true : false);
define('ROOT_DIR', str_replace("\\", '/', dirname(__FILE__)));
define('ROOT_WEB', str_replace(strrchr(ROOT_DIR, '/'), '', ROOT_DIR));
//echo ROOT_WEB;

//===================================================================
//路由逻辑区
//===================================================================
$respondData = array(
    'id' => 0,
    'state' => 0,
    'msg' => 'fail',
    'data' => null
);

//todo 此处过滤数据
$receiveData = $_POST;
if(!IS_MAGIC_QUOTES_GPC){

}

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
$action = 'act_' . $act;


if(IS_AJAX)
{
//    $respondData = array(
//        'id' => 1,
//        'state' => 1,
//        'msg' => 'success',
//        'data' => $receiveData
//    );

    if(function_exists($action))
    {
        $respondData = call_user_func($action, $receiveData);
    }

    respondMsg($respondData);
}
else
{
    respondMsg($respondData);
}

//===================================================================
//函数库区
//===================================================================
function respondMsg($data)
{
    exit(json_encode($data));
}

function configFileFormat($data)
{
    return "<?php\r\n" . "return " . var_export($data, true) . ";";
}

function config($name, $data=array())
{
    //todo 此处用数组还是json文件？
    $path = ROOT_DIR . '/config/' . $name . '.php';

    if(file_exists($path)) {
        if(empty($data)){
            $result = include($path);
            return $result;
        }else{
            file_put_contents($path, configFileFormat($data));
            return true;
        }
    }else{
        file_put_contents($path, configFileFormat($data));
        return true;
    }

}


//===================================================================
//动作函数区
//===================================================================
function act_from_cfg_save($data)
{

    config('from_db', $data);
    $rs = config('from_db');

    return array(
        'id' => 1,
        'state' => 1,
        'msg' => 'success',
        'data' => $rs
    );
}

function act_to_cfg_save($data)
{

    config('to_db', $data);
    $rs = config('to_db');

    return array(
        'id' => 2,
        'state' => 1,
        'msg' => 'success',
        'data' => $rs
    );
}

function act_field_cfg_reset($data)
{
    $config = config('from_db');
    $from_db = new DataBase($config);

    $maintable = getAddonTable($config['dbmaintable'], $from_db);
    $addontable = getAddonTable($config['dbaddontable'], $from_db);

    $rs = array_merge($maintable, $addontable);

    return array(
        'id' => 3,
        'state' => 1,
        'msg' => 'success',
        'data' => $rs
    );
}

function act_from_cfg_test($data)
{
    $config = config('from_db');
    $from_db = new DataBase($config);
    $rs = $from_db->testconnect();

    if($rs){
        return array(
            'id' => 4,
            'state' => 1,
            'msg' => 'success',
            'data' => $config
        );
    }
    else{
        return array(
            'id' => 4,
            'state' => 0,
            'msg' => 'fail',
            'data' => $config
        );
    }


}

function act_to_cfg_test($data)
{
    $config = config('to_db');
    $to_db = new DataBase($config);
    $rs = $to_db->testconnect();

    if($rs){
        return array(
            'id' => 5,
            'state' => 1,
            'msg' => 'success',
            'data' => $config
        );
    }
    else{
        return array(
            'id' => 5,
            'state' => 0,
            'msg' => 'fail',
            'data' => $config
        );
    }
}

function act_field_cfg_save($data){
    var_dump($data);

    $rs = array();

    return array(
        'id' => 6,
        'state' => 1,
        'msg' => 'success',
        'data' => $rs
    );
    $todataKey = array();
    $todata = array();
    $result = array();
    $temp = array();
    foreach($result as $item){

        foreach($todataKey as $key){
            if(in_array($key, array_keys($data))){
                $temp = $result[$data[$key]];
            }
        }

        $default = array();

        $todata[] = array_merge($default, $temp);
    }
}

function act_import_cfg_reset_from($data) {

    $rs = array();
//    来源数据
    $from_config = config('from_db');
    $from_db = new DataBase($from_config);
    $from_db->query(" SELECT id, reid AS pId, typename AS name FROM `#@__arctype` ");
    $from_rs = $from_db->fetch_array();
    if($from_rs){
        foreach($from_rs as $item){
            $item['name'] = '[' . $item['id'] . ']' . $item['name'];
            $rs[] = $item;
        }
    }else{
        $rs = array();
    }

    //返回数据
    return array(
        'id' => 7,
        'state' => 1,
        'msg' => 'success',
        'data' => $rs
    );
}

function act_import_cfg_reset_to($data) {

    $rs = array();
//导入数据
    $to_config = config('to_db');
    $to_db = new DataBase($to_config);
    $to_db->query(" SELECT id, reid AS pId, typename AS name FROM `#@__arctype` ");
    $to_rs = $to_db->fetch_array();
    if($to_rs){
        foreach($to_rs as $item){
            $item['name'] = '[' . $item['id'] . ']' . $item['name'];
            $rs[] = $item;
        }
    }else{
        $rs = array();
    }

    //返回数据
    return array(
        'id' => 7,
        'state' => 1,
        'msg' => 'success',
        'data' => $rs
    );
}

function act_import_cfg_export_images($data){
    $rs = config('import_typeid');
    if(!isset($rs['to_typeid'])){
       $rs = array();
    }
    $rs['is_export_images'] = ($data['is_export_images'] == 'true') ? true : false;
    config('import_typeid', $rs);

    return array(
        'id' => 8,
        'state' => 1,
        'msg' => '恭喜，保存成功！',
        'data' => $rs
    );
}

function act_import_cfg_save($data){
    $rs = config('import_typeid');

    if(isset($rs['is_export_images']) && !isset($data['is_export_images'])){
        $data['is_export_images'] = $rs['is_export_images'];
    }

    if(isset($rs['to_typeid']) && !isset($data['to_typeid'])){
        $data['to_typeid'] = $rs['to_typeid'];
    }

    if(isset($rs['from_typeid']) && !isset($data['from_typeid'])){
        $data['from_typeid'] = $rs['from_typeid'];
    }

    $rs = config('import_typeid', $data);

    return array(
        'id' => 8,
        'state' => 1,
        'msg' => '恭喜，保存成功！',
        'data' => $rs
    );
}

function act_start($data) {
    $step = isset($data['step']) ? intval($data['step']) : 0;

    $import_typeid = config('import_typeid');

    //导出图片目录判断
    if($import_typeid['is_export_images']){
        if(!is_dir(ROOT_WEB . '/uploads')){
            return array(
                'id' => 6,
                'state' => -1,
                'msg' => '未查找到图片上传目录，请取消导出图片勾选！',
                'data' => array()
            );
        }
    }


    $from_config = config('from_db');
    $to_config = config('to_db');

    //转移数据库
    $FROM_DB = new DataBase($from_config);

    //目标数据库
    $TO_DB = new DataBase($to_config);

//    return array(
//        'id' => 6,
//        'state' => -1,
//        'msg' => '测试速度问题！',
//        'data' => array()
//    );

//    1、导出模型，并判断模型一致
    $fromAddonTable = getChannelTypeAddonTable($import_typeid['from_typeid'], $FROM_DB);
    $toAddonTable = getChannelTypeAddonTable($import_typeid['to_typeid'], $TO_DB);

    $is_equals_addontable = serialize($fromAddonTable) == serialize($toAddonTable) ? true : false;

    if(!$is_equals_addontable){
        return array(
            'id' => 6,
            'state' => -1,
            'msg' => '数据模型不一致，请重新选择栏目！',
            'data' => array()
        );
    }



//    2、获得导出数据库数据
    //TODO 此处每次获得所有数据但只用一条，内部可加个缓存，只调用需要的那一条
    $from_data = getFromData($import_typeid['from_typeid'], $FROM_DB);

//    4、循环插入数据
    $addontablestruct = array();
    foreach($toAddonTable as $value){
        $addontablestruct[] = $value['Field'];
    }

    $to_arcType = getArcType($import_typeid['to_typeid'], $TO_DB);
    $to_channelType = getChannelType($to_arcType['channeltype'], $TO_DB);

    if(isset($from_data[$step])){
        //    3、复制图片资料集（可选）
        if($import_typeid['is_export_images']){
            $rs_images = statistics_imgs($from_data[$step]);
            if($rs_images){
                foreach($rs_images as $item){
                    copy_img($item);
                }
            }
        }

        insertOne($from_data[$step], $import_typeid['to_typeid'], $to_channelType['addtable'], $addontablestruct, $TO_DB);
    }

//返回参数
   $total = count($from_data);

   $rs = array('total' => $total);

   if($step > $total){
       return array(
           'id' => 6,
           'state' => 0,
           'msg' => '恭喜，导入完成！',
           'data' => $rs
       );
   }
   else{
       return array(
           'id' => 6,
           'state' => 1,
           'msg' => 'success',
           'data' => $rs
       );
   }
}

//2018年11月28日17:25:34 xslooi 添加 V2 函数

function act_start_v2($data) {
    $step = isset($data['step']) ? intval($data['step']) : 0;

    $import_typeid = config('import_typeid');

    //导出图片目录判断
    if($import_typeid['is_export_images']){
        if(!is_dir(ROOT_WEB . '/uploads')){
            return array(
                'id' => 6,
                'state' => -1,
                'msg' => '未查找到图片上传目录，请取消导出图片勾选！',
                'data' => array()
            );
        }
    }


    $from_config = config('from_db');
    $to_config = config('to_db');

    //转移数据库
    $FROM_DB = new DataBase($from_config);

    //目标数据库
    $TO_DB = new DataBase($to_config);


//    1、导入数据表模型
    $toAddonTable = getChannelTypeAddonTable($import_typeid['to_typeid'], $TO_DB);

//    2、获得导出数据库数据
    //TODO 此处每次获得所有数据但只用一条，内部可加个缓存，只调用需要的那一条
    $from_data = getRelevanceData($import_typeid['from_typeid'], $FROM_DB);
//var_dump($from_data);
//    4、循环插入数据
    $addontablestruct = array();
    foreach($toAddonTable as $value){
        $addontablestruct[] = $value['Field'];
    }

    $to_arcType = getArcType($import_typeid['to_typeid'], $TO_DB);
    $to_channelType = getChannelType($to_arcType['channeltype'], $TO_DB);

    if(isset($from_data[$step])){
        //    3、复制图片资料集（可选）
        if($import_typeid['is_export_images']){
            $rs_images = statistics_imgs($from_data[$step]);
            if($rs_images){
                foreach($rs_images as $item){
                    copy_img($item);
                }
            }
        }
//var_dump($from_data[$step]);
        insertOne($from_data[$step], $import_typeid['to_typeid'], $to_channelType['addtable'], $addontablestruct, $TO_DB);
    }

//返回参数
    $total = count($from_data);

    $rs = array('total' => $total);

    if($step > $total){
        return array(
            'id' => 6,
            'state' => 0,
            'msg' => '恭喜，导入完成！',
            'data' => $rs
        );
    }
    else{
        return array(
            'id' => 6,
            'state' => 1,
            'msg' => 'success',
            'data' => $rs
        );
    }
}

//2018年12月14日13:36:50 xslooi 添加 V3 函数
function act_start_v3($data) {
    $step = isset($data['step']) ? intval($data['step']) : 0;

    $import_typeid = config('import_typeid');

    //导出图片目录判断
    if($import_typeid['is_export_images']){
        if(!is_dir(ROOT_WEB . '/uploads')){
            return array(
                'id' => 6,
                'state' => -1,
                'msg' => '未查找到图片上传目录，请取消导出图片勾选！',
                'data' => array()
            );
        }
    }


    $to_config = config('to_db');

    //目标数据库
    $TO_DB = new DataBase($to_config);


//    1、导入数据表模型
    $toAddonTable = getChannelTypeAddonTable($import_typeid['to_typeid'], $TO_DB);

//    2、获得导出数据库数据
    //TODO 此处每次获得所有数据但只用一条，内部可加个缓存，只调用需要的那一条
    $from_data = getMssqlRelevanceData($import_typeid['from_typeid']);
//var_dump($from_data);
//exit;
//    4、循环插入数据
    $addontablestruct = array();
    foreach($toAddonTable as $value){
        $addontablestruct[] = $value['Field'];
    }

    $to_arcType = getArcType($import_typeid['to_typeid'], $TO_DB);
    $to_channelType = getChannelType($to_arcType['channeltype'], $TO_DB);

    if(isset($from_data[$step])){
        //    3、复制图片资料集（可选）
        if($import_typeid['is_export_images']){
            $rs_images = statistics_imgs($from_data[$step]);
            if($rs_images){
                foreach($rs_images as $item){
                    copy_img($item);
                }
            }
        }

        insertOne($from_data[$step], $import_typeid['to_typeid'], $to_channelType['addtable'], $addontablestruct, $TO_DB);
    }

//返回参数
    $total = count($from_data);

    $rs = array('total' => $total);

    if($step > $total){
        return array(
            'id' => 6,
            'state' => 0,
            'msg' => '恭喜，导入完成！',
            'data' => $rs
        );
    }
    else{
        return array(
            'id' => 6,
            'state' => 1,
            'msg' => 'success',
            'data' => $rs
        );
    }
}

function act_resetExportTablesList(){

    $db_config = config('from_db');
    $rs = get_mysql_tables($db_config);

    return array(
        'id' => 7,
        'state' => 1,
        'msg' => '恭喜，保存成功！',
        'data' => $rs
    );
}

function act_saveExportCateTable($data){

    $rs = config('export_cate_table', $data);

    return array(
        'id' => 9,
        'state' => 1,
        'msg' => '恭喜，保存成功！',
        'data' => $rs
    );
}

function act_resetExportCateField(){
    $db_config = config('from_db');
    $rs = get_mysql_fields($db_config);

    return array(
        'id' => 10,
        'state' => 1,
        'msg' => '恭喜，列表加载成功！',
        'data' => $rs
    );
}

function act_saveExportCateMap($data){
    $rs = config('exprot_cate_map', $data);

    return array(
        'id' => 11,
        'state' => 1,
        'msg' => '恭喜，保存成功！',
        'data' => $rs
    );
}

function act_resetExportCateList(){

    $rs = get_mysql_cates();

    return array(
        'id' => 12,
        'state' => 1,
        'msg' => '恭喜，保存成功！',
        'data' => $rs
    );
}

function act_saveExportMainTable($data){
    $rs = config('export_main_table', $data);

    return array(
        'id' => 13,
        'state' => 1,
        'msg' => '恭喜，保存成功！',
        'data' => $rs
    );
}

function act_resetExportFieldList(){
    $db_config = config('from_db');

    $rs = get_mysql_main_fields($db_config);

    return array(
        'id' => 14,
        'state' => 1,
        'msg' => '恭喜，保存成功！',
        'data' => $rs
    );
}

function act_resetImportFieldList(){
    $db_config = config('to_db');

    $rs = get_dedecms_import_fields($db_config);

    return array(
        'id' => 15,
        'state' => 1,
        'msg' => '恭喜，保存成功！',
        'data' => $rs
    );
}

function act_saveFieldRelevance($data){

    config('export_cate_name', array('category_id'=>$data['category_id']));
    unset($data['category_id']);

    if(!empty($data['master_id']) && !empty($data['slave_id'])){
        config('field_master_slave', array('master_id'=>$data['master_id'], 'slave_id'=>$data['slave_id']));
    }
    unset($data['master_id']);
    unset($data['slave_id']);

    $rs = config('field_relevance', $data);

    return array(
        'id' => 16,
        'state' => 1,
        'msg' => '恭喜，保存成功！',
        'data' => $rs
    );
}

function act_relevanceReload(){
    $rs = config('field_relevance');

    return array(
        'id' => 17,
        'state' => 1,
        'msg' => '恭喜，加载成功',
        'data' => $rs
    );
}

function act_relevanceReset(){

    $rs = unlink(ROOT_DIR . '/config/field_relevance.php');

    return array(
        'id' => 18,
        'state' => 1,
        'msg' => '恭喜，删除成功',
        'data' => $rs
    );
}

function act_relevanceSave($data){

    $rs = config('field_convert', $data);

    return array(
        'id' => 19,
        'state' => 1,
        'msg' => '恭喜，保存成功',
        'data' => $rs
    );
}

//2018年12月14日09:11:22 xslooi 添加 V3函数
function act_uploadAccessFile(){
    $rs = null;
    $upload_name = 'access_file';

    //简单的文件上传处理
    if(isset($_FILES[$upload_name]) && empty($_FILES[$upload_name]['error'])){
        $save_path = ROOT_DIR . '/config/' . md5($_FILES[$upload_name]['name']) . '.' . pathinfo($_FILES[$upload_name]['name'], PATHINFO_EXTENSION);
        $upload_state = move_uploaded_file($_FILES[$upload_name]['tmp_name'], $save_path);
        if($upload_state){
//            上传成功删除旧文件
          $old_path =  config('upload_access_path');
          if(file_exists($old_path['path']) && $save_path != $old_path['path']){
              unlink($old_path['path']);
          }

          config('upload_access_path', array('path' => $save_path));

            return array(
                'id' => 20,
                'state' => 1,
                'msg' => '恭喜上传成功',
                'data' => 'ok'
            );
        }else{
            return array(
                'id' => 20,
                'state' => -1,
                'msg' => '上传成功移动失败请检查',
                'data' => $rs
            );
        }

    }else{
        return array(
            'id' => 20,
            'state' => -1,
            'msg' => '上传失败请检查',
            'data' => $rs
        );
    }
}

function act_v3_resetExportTablesList(){
    $rs = get_mssql_tables();

    return array(
        'id' => 20,
        'state' => 1,
        'msg' => '加载成功',
        'data' => $rs
    );
}

function act_v3_resetExportCateField(){
    $rs = get_mssql_fields();
//var_dump($rs);
    return array(
        'id' => 10,
        'state' => 1,
        'msg' => '恭喜，列表加载成功！',
        'data' => $rs
    );
}

function act_v3_resetExportCateList(){

    $rs = get_mssql_cates();

    return array(
        'id' => 12,
        'state' => 1,
        'msg' => '恭喜，保存成功！',
        'data' => $rs
    );
}

function act_v3_resetExportFieldList(){
    $rs = get_mssql_main_fields();

    return array(
        'id' => 12,
        'state' => 1,
        'msg' => '恭喜，保存成功！',
        'data' => $rs
    );
}
//===================================================================
//其他作用区
//===================================================================

/**
 * 向目标数据库中插入一条数据
 * @param $fromResults
 * @param $totype
 * @param $toLink
 */
function insertOne($fromResults, $totype, $addontablename, $addontablestruct, $db){

//    2018年12月11日17:30:03 xslooi 修改 增加默认模型id
    if(empty($fromResults['channel'])){
        $to_arcType = getArcType($totype, $db);
        $fromResults['channel'] = $to_arcType['channeltype'];
    }

    //    插入微表
    $iquery = " INSERT INTO `#@__arctiny` (`arcrank`,`typeid`,`typeid2`,`channel`,`senddate`, `sortrank`, `mid`)
          VALUES ('{$fromResults['arcrank']}','{$totype}','{$fromResults['typeid2']}' , '{$fromResults['channel']}','{$fromResults['senddate']}', '{$fromResults['sortrank']}', '{$fromResults['mid']}') ";
//echo $iquery;


    $intiny = $db->query($iquery);
    $arcID = $db->insert_id();

//    插入文章主表
    $toSql = " INSERT INTO `#@__archives`(id,typeid,typeid2,sortrank,flag,ismake,channel,arcrank,click,money,title,shorttitle,
color,writer,source,litpic,pubdate,senddate,mid,voteid,notpost,description,keywords,filename,dutyadmin,weight)
    VALUES ('{$arcID}' ,'{$totype}','{$fromResults['typeid2']}','{$fromResults['sortrank']}','{$fromResults['flag']}','{$fromResults['ismake']}','{$fromResults['channel']}','{$fromResults['arcrank']}','{$fromResults['click']}','{$fromResults['money']}','{$fromResults['title']}','{$fromResults['shorttitle']}',
    '{$fromResults['color']}','{$fromResults['writer']}','{$fromResults['source']}','{$fromResults['litpic']}','{$fromResults['pubdate']}','{$fromResults['senddate']}','{$fromResults['mid']}','{$fromResults['voteid']}','{$fromResults['notpost']}','{$fromResults['description']}','{$fromResults['keywords']}','{$fromResults['filename']}','{$fromResults['dutyadmin']}','{$fromResults['weight']}') ";

//echo $toSql;

    $resouse = $db->query($toSql);

//插入文章附加表
//    组合插入数据表值
    $addontablevalue = '';
    $addontableArray = array_slice($addontablestruct,2);
    foreach($addontableArray as $item){
        //TODO 1、字符串转义； 2、编码转换
        if(false !== strpos($fromResults[$item], "'")){
            $fromResults[$item] = addslashes($fromResults[$item]);
        }
//        var_dump(IS_MAGIC_QUOTES_GPC);
//        var_dump(strpos($fromResults[$item], "'"));
        $addontablevalue .= ",'{$fromResults[$item]}'";
    }


    $query = " INSERT INTO `{$addontablename}` (" . implode(',', $addontablestruct) . ") VALUES ('$arcID','$totype'{$addontablevalue})";

//    echo "<hr>" . $query;
    $intiny = $db->query($query);

    if(!$intiny)
    {
        $db->query(" DELETE FROM `#@__archives` WHERE id='$arcID' ");
        $db->query(" DELETE FROM `#@__arctiny` WHERE id='$arcID' ");

        return array(
            'id' => 6,
            'state' => -1,
            'msg' => '把数据保存到数据库附加表时出错!',
            'data' => array()
        );
    }

    return true;
}

/**
 * 得到栏目数据
 * @param $tid
 * @param $link
 * @param $config
 * @return array
 */
function getArcType($tid, $db){

    //栏目表
    $sql = " SELECT * FROM `#@__arctype` WHERE id = {$tid} LIMIT 1 ";
    $db->query($sql);
    $result = $db->fetch_array();
    //栏目表
    if($result){
        return $result[0];
    }else{
        return array(
            'id' => 6,
            'state' => -1,
            'msg' => __FUNCTION__ . "栏目表数据异常",
            'data' => array()
        );
    }


}

/**
 * 得到模型数据
 * @param $cid
 * @param $link
 * @param $config
 * @return array
 */
function getChannelType($cid, $db){

    $sql = " SELECT * FROM `#@__channeltype` WHERE id = {$cid} LIMIT 1 ";
    $db->query($sql);
    $result = $db->fetch_array();
    //模型表
    if($result){
        return $result[0];
    }else{
        return array(
            'id' => 6,
            'state' => -1,
            'msg' =>  __FUNCTION__ . "模型表数据异常",
            'data' => array()
        );
    }

}

/**
 * 查询附加表结构信息
 * @param $tid
 * @param $link
 * @param $config
 * @return array
 */
function getChannelTypeAddonTable($tid, $db){

    //栏目表
    $result = getArcType($tid, $db);

    //模型表
    if($result){
        $result = getChannelType($result['channeltype'], $db);
    }else{
        return array(
            'id' => 6,
            'state' => -1,
            'msg' =>  __FUNCTION__ . "栏目表数据异常",
            'data' => array()
        );
    }

    //查询 附加表 信息
    $addonTable = array();
    if($result){
        $addonTable = getAddonTable($result['addtable'], $db);
    }else{
        return array(
            'id' => 6,
            'state' => -1,
            'msg' =>  __FUNCTION__ . "模型表数据异常",
            'data' => array()
        );
    }

    return $addonTable;

}

/**
 * 得到附加表 结构 数据
 * @param $tablename
 * @param $link
 * @return array
 */
function getAddonTable($tablename, $db){
    // todo 根据数据库配置设置表前缀 var_dump($db->db_config);
    $sql = " DESC {$tablename} ";
    $db->query($sql);
    $result = $db->fetch_array();

    //模型表
    if($result){
        //处理附加表 结构信息
        $addonTable = array();
        foreach($result as $i=>$item){
            foreach($item as $key=>$value){
                if('Field' == $key || 'Type' == $key){
                    $addonTable[$i][$key] = $value;
                }
            }

        }

        return $addonTable;

    }else{
        return array(
            'id' => 6,
            'state' => -1,
            'msg' => __FUNCTION__ . "附加表数据异常",
            'data' => array()
        );
    }
}

/**
 * 递归 获得 分级栏目名称
 * @param $link
 * @param $config
 * @param int $pid
 * @param array $result
 * @param int $leve
 * @return array
 */
function transionType($db, $pid = 0, &$result = array(), $leve = 0){
    $sql = " SELECT id, reid, typename FROM #@__arctype WHERE reid = {$pid} ORDER BY sortrank ";
    $db->query($sql);
    $res = $db->fetch_array();

    $leve++;
    foreach($res as $row){
        if(0 != $row['reid']){
            $row['typename'] = str_repeat('&nbsp;&nbsp;', $leve) . '|--' . $row['typename'];
        }
        $result[] = $row;
        transionType($db, $row['id'], $result, $leve);
    }

    return $result;
}

/**
 * 得到来源数据 - V1
 * @param $tid
 * @param $db
 * @return mixed
 */
function getFromData($tid, $db){
    //栏目表
    $arcType = getArcType($tid, $db);
    //模型表
    $from_channelType = getChannelType($arcType['channeltype'], $db);

    $fromQuery = " SELECT arc.*,addon.* FROM #@__archives AS arc LEFT JOIN " . $from_channelType['addtable'] . " AS addon ON arc.id = addon.aid WHERE arc.typeid = {$tid} AND arcrank > -1 ";

    $db->query($fromQuery);
    $fromResults = $db->fetch_array();

    //todo 数据可能需要转换编码

    return $fromResults;
}

/**
 * 得到关联并且已经转换过的数据 - V2
 * @param $tid
 * @param $db
 * @return array
 */
function getRelevanceData($tid, $db){

    $result = array();

    $mainTableConfig = config('export_main_table');
    $cateNameConfig = config('export_cate_name');

    $field_relevanceConfig = config('field_relevance');
    $field_convertConfig = config('field_convert');

    $dedecms_default_value = config('dedecms_default_value');

    if(!empty($mainTableConfig['slave'])){
        $field_master_slave = config('field_master_slave');
    }

    if(!empty($field_master_slave['master_id']) && !empty($field_master_slave['slave_id']) && !empty($mainTableConfig['slave'])){
        $fromQuery = "SELECT master.*, slave.* FROM `{$mainTableConfig['master']}` AS `master` LEFT JOIN `{$mainTableConfig['slave']}` AS `slave` ON `master`.{$field_master_slave['master_id']} = `slave`.{$field_master_slave['slave_id']}  WHERE `master`.{$cateNameConfig['category_id']} = {$tid} ";
    }else{
        $fromQuery = "SELECT * FROM `{$mainTableConfig['master']}` WHERE {$cateNameConfig['category_id']} = {$tid} ";
    }
//echo $fromQuery;

    $db->query($fromQuery);
    $fromResults = $db->fetch_array();
//    var_dump($fromResults);

//    1、最外层循环导出所有数据
//    2、内层循环关联关系
//    3、不为空进入转换过程


    foreach($fromResults as $i=>$item){

        //开始处理字段转换配置
        if(empty($field_relevanceConfig)){
            //直接赋值数据库内容
            $result[$i] = $item;
        }
        else{
            foreach($field_relevanceConfig as $key=>$val){

                $convert_encode = explode('|', $field_convertConfig[$key][0]);
                $convert_opera = explode('|', $field_convertConfig[$key][1]);

                //转换编码
                if(!empty($convert_encode[1])){
                    $fromResults[$i][$val] = iconv($convert_encode[1], 'utf-8//IGNORE', $item[$val]);
                }

//               var_dump($convert_encode);
//               var_dump($convert_opera);
                //根据配置转换内容
                switch ($convert_opera[0]){
                    case 'time':
                        $result[$i][$key] = strtotime($fromResults[$i][$val]);
                        break;
                    case 'substr':
                        $result[$i][$key] = mb_substr($fromResults[$i][$val], 0, $convert_opera[1]);
                        break;
                    case 'defaultvalue':
                        $result[$i][$key] = $convert_opera[1];
                        break;
                    case 'replace':
                        $result[$i][$key] = str_replace(explode('#', $convert_opera[1]), explode('#', $convert_opera[2]), $fromResults[$i][$val]);
                        break;
                    default :
                        $result[$i][$key] = $fromResults[$i][$val];
                }

                //额外附加操作
                if(!empty($result[$i]['litpic'])){
                    $result[$i]['flag'] = 'p';
                }

            }
        }
    }

//    var_dump($result);
//    exit;
    return $result;
}

/**
 * 得到关联并且已经转换过的数据 - V3
 * @param $tid
 * @return array
 */
function getMssqlRelevanceData($tid){

    $result = array();

    $connid = get_mssql_odbc_connect();

    $mainTableConfig = config('export_main_table');
    $cateNameConfig = config('export_cate_name');

    $field_relevanceConfig = config('field_relevance');
    $field_convertConfig = config('field_convert');

    $dedecms_default_value = config('dedecms_default_value');

    if(!empty($mainTableConfig['slave'])){
        $field_master_slave = config('field_master_slave');
    }

    if(!empty($field_master_slave['master_id']) && !empty($field_master_slave['slave_id']) && !empty($mainTableConfig['slave'])){
        $fromQuery = "SELECT master.*, slave.* FROM `{$mainTableConfig['master']}` AS `master` LEFT JOIN `{$mainTableConfig['slave']}` AS `slave` ON `master`.{$field_master_slave['master_id']} = `slave`.{$field_master_slave['slave_id']}  WHERE `master`.{$cateNameConfig['category_id']} = {$tid} ";
    }else{
        $fromQuery = "SELECT * FROM `{$mainTableConfig['master']}` WHERE {$cateNameConfig['category_id']} = {$tid} ";
    }
//echo $fromQuery;
//    ini_set("odbc.defaultlrl", "100000"); //解决字段字符长度4096 方法一

    $resource = odbc_exec($connid, $fromQuery);
    $dataSet = array();

    if($resource){
        odbc_longreadlen($resource, "100000"); //解决字段字符长度4096 方法二 最大为1亿+

        while($row = odbc_fetch_array($resource)){
            $dataSet[] = $row;
        }
    }
//    1、最外层循环导出所有数据
//    2、内层循环关联关系
//    3、不为空进入转换过程
    $fromResults = $dataSet;
//var_dump($dataSet);
//    exit;

    foreach($fromResults as $i=>$item){
        //赋默认值
        $result[$i] = $dedecms_default_value;

        foreach($field_relevanceConfig as $key=>$val){
            if(empty($val)){
//                $result[$i][$key] = '';
            }else{

                $convert_encode = explode('|', $field_convertConfig[$key][0]);
                $convert_opera = explode('|', $field_convertConfig[$key][1]);

                //转换编码
                if(!empty($convert_encode[1])){
                    $fromResults[$i][$val] = iconv($convert_encode[1], "utf-8//IGNORE", $item[$val]);
                }

//               var_dump($convert_encode);
//               var_dump($convert_opera);
                //根据配置转换内容
                switch ($convert_opera[0]){
                    case 'time':
                        $result[$i][$key] = strtotime($fromResults[$i][$val]);
                        break;
                    case 'substr':
                        $result[$i][$key] = mb_substr($fromResults[$i][$val], 0, $convert_opera[1]);
                        break;
                    case 'defaultvalue':
                        $result[$i][$key] = $convert_opera[1];
                        break;
                    case 'replace':
                        $result[$i][$key] = str_replace(explode('#', $convert_opera[1]), explode('#', $convert_opera[2]), $fromResults[$i][$val]);
                        break;
                    default :
                        $result[$i][$key] = $fromResults[$i][$val];
                }

                //额外附加操作
                if(!empty($result[$i]['litpic'])){
                    $result[$i]['flag'] = 'p';
                }
            }
        }
    }

    if($connid){
        odbc_close($connid);
    }

    return $result;
}

//=========================================================================
//图片资源处理
//=========================================================================

/**
 * 统计整个转移资料中有多少个图片 包括：litpic 缩略图 及 body 内的图片
 * @param $fromResults
 */
function statistics_imgs($fromResults){
    $img_str_length = 20; //最小图片路径长度
    $temp_imgs = array();

    if(is_array($fromResults)){

        foreach($fromResults as $key=>$value) {
            if(is_array($value)){
                foreach($value as $k=>$v){
                    if($img_str_length < strlen($v)){
                        $temp = get_match_imgs($v); //正则匹配 图片
                        if($temp){
                            foreach($temp as $t){
                                $temp_imgs[] = $t;
                            }
                        }
                    }
                }

            }else{

                if($img_str_length < strlen($value)){
                    $temp = get_match_imgs($value); //正则匹配 图片
                    if($temp){
                        foreach($temp as $t){
                            $temp_imgs[] = $t;
                        }
                    }
                }
            }
        }
    }

    return $temp_imgs;
}

/**
 * 正则 匹配 字符串中的 图片 /<img.*?src="(.*?)".*?>/is
 * @param $string
 * @return array
 */
function get_match_imgs(&$string){
    $img_ext = array('.jpg', '.jpeg', '.png', '.bmp', '.gif');
    $matchimgs = array();
    $result = array();

    if(in_array(strrchr($string, '.'), $img_ext)){
        $result[] = $string;
    }else{
        preg_match_all('/<img.*?src="(.*?)".*?>/is', $string, $matchimgs);

        if(0 < count($matchimgs[1])) {
            foreach ($matchimgs[1] as $k => $v) {
                $result[] = $v;
            }
        }
    }

    return $result;
}

/**
 * 复制 一张图片 到 备份目录 backups/后面相同
 * @param $imgPath
 * @return bool
 */
function copy_img($imgStr){
    if(empty($imgStr)){
        return false;
    }

    $imgPath = ROOT_WEB . $imgStr;
    if(file_exists($imgPath)){
        $backupPath = str_replace('uploads', '__backups', $imgPath);
        if(createDir($backupPath)){
            copy($imgPath, $backupPath);
        }
    }

}

/**
 * 创建 多层次目录 如：/uploads/allimg/20173323
 * @param $aimUrl
 * @return bool
 */
function createDir($aimUrl) {
    $aimUrl = substr($aimUrl,0,strrpos($aimUrl,'/'));
    $aimUrl = str_replace('uploads', '__backups', $aimUrl);
    $aimUrl = str_replace('', '/', $aimUrl);
    if(is_dir($aimUrl)){return true;}

    $aimDir = '';
    $arr = explode('/', $aimUrl);
    $result = true;
    foreach ($arr as $str) {
        $aimDir .= $str . '/';
        if (!file_exists($aimDir)) {
            $result = mkdir($aimDir);
        }
    }
    return $result;
}


//======================================================================================================================
//2018年11月28日18:00:54 xslooi V2 添加

/**
 * 获取mysql数据库的数据表列表
 * @param $db_config
 * @return array
 */
function get_mysql_tables($db_config){
    $tables = array();

    $DB = new DataBase($db_config);
    $sql = "SELECT `table_name` FROM information_schema.TABLES WHERE table_schema = '{$db_config['dbname']}' AND table_type = 'base table';";

    $DB->query($sql);
    $rows = $DB->fetch_array();

    if($rows){
        foreach($rows as $item){
            $tables[] = $item['table_name'];
        }
    }

    return $tables;
}

/**
 * 获取mysql导出数据表字段列表
 * @param $db_config
 * @return array
 */
function get_mysql_fields($db_config){
    $filelds = array();

    $export_cate_table = config('export_cate_table');

    $DB = new DataBase($db_config);
    $sql = 'DESC ' . $export_cate_table['export_cate_table'];

    $DB->query($sql);
    $rows = $DB->fetch_array();

    if($rows){
        foreach($rows as $item){
            $filelds[] = $item['Field'];
        }
    }

    return $filelds;
}

/**
 * 得到mysql 分类数据列表
 * @return array
 */
function get_mysql_cates(){
    $cates = array();
    $sql_select_field = '';

    $db_config = config('from_db');
    $exprot_cate_map = config('exprot_cate_map');
    $export_cate_table = config('export_cate_table');

    $DB = new DataBase($db_config);

    foreach($exprot_cate_map as $key=>$value){
        if(0 != strcasecmp($key, $value)){
            $sql_select_field .= '`' . $value . '` AS ' . '`' . $key . '`,';
        }else{
            $value = strtolower($value);
            $sql_select_field .= '`' . $value . '`,';
        }
    }

    $sql_select_field = trim($sql_select_field, ',');

    $sql = "SELECT {$sql_select_field} FROM `{$export_cate_table['export_cate_table']}`;";

    $DB->query($sql);
    $rows = $DB->fetch_array();

    if($rows){
        foreach($rows as $item){
            $cates[] = $item;
        }
    }

    return $cates;
}

/**
 * 得到mysql 主表字段列表
 * @param $db_config
 * @return array
 */
function get_mysql_main_fields($db_config){
    $filelds = array();

    $export_main_table = config('export_main_table');

    $DB = new DataBase($db_config);
    $sql = 'DESC ' . $export_main_table['master'];

    $DB->query($sql);
    $rows = $DB->fetch_array();

    if($rows){
        foreach($rows as $item){
            $filelds[] = $item['Field'];
        }
    }

    //附加表字段
    if(!empty($export_main_table['slave'])){
        $sql = 'DESC ' . $export_main_table['slave'];

        $DB->query($sql);
        $rows = $DB->fetch_array();

        if($rows){
            foreach($rows as $item){
                $filelds[] = $item['Field'];
            }
        }
    }

    return $filelds;
}

/**
 * 得到织梦导入数据的字段列表 （以去除默认字段）
 * @param $db_config
 * @return array
 */
function get_dedecms_import_fields($db_config){
    $filelds = array();

    $import_typeid = config('import_typeid');

    $DB = new DataBase($db_config);
    $sql = 'DESC ' . $db_config['dbprefix'] . 'archives';

    $DB->query($sql);
    $rows = $DB->fetch_array();

    $main_table_must_fields = array('id', 'typeid', 'typeid2', 'flag', 'ismake', 'channel', 'arcrank', 'money', 'color', 'mid', 'lastpost', 'scores', 'goodpost', 'badpost', 'voteid', 'notpost', 'filename', 'dutyadmin', 'tackid', 'mtype');

    if($rows){
        foreach($rows as $item){
            if(!in_array($item['Field'], $main_table_must_fields)){
                $filelds[] = $item['Field'];
            }
        }
    }


    $addon_table = getChannelTypeAddonTable($import_typeid['to_typeid'], $DB);
    $addon_table_must_fields = array('aid', 'typeid', 'redirecturl', 'templet', 'userip');

    if($addon_table){
        foreach($addon_table as $item){
            if(!in_array($item['Field'], $addon_table_must_fields)){
                $filelds[] = $item['Field'];
            }
        }
    }

    return $filelds;
}


//======================================================================================================================
//2018年12月14日09:53:28 xslooi V3 添加函数
function get_mssql_odbc_connect(){
    if(!function_exists('odbc_connect')){
        $error = array(
            'id' => 20,
            'state' => -1,
            'msg' => ' odbc 扩展未开启，请开启 php_pdo_odbc 扩展 ！',
            'data' => array()
        );
        exit(json_encode($error));
    }
    $data_path = config('upload_access_path');
    $connstr = "DRIVER={Microsoft Access Driver (*.mdb)}; DBQ=" . realpath($data_path['path']);
    
    $connid = odbc_connect($connstr,"","",SQL_CUR_USE_ODBC);

    if(!$connid){
        $error = array(
            'id' => 20,
            'state' => -1,
            'msg' => ' odbc 数据库连接失败，请检查！',
            'data' => array()
        );
        exit(json_encode($error));
    }
    return $connid;
}

function get_mssql_tables(){
    $result = array();
    $connid = get_mssql_odbc_connect();
    $resource = odbc_tables($connid);
    if($resource){
        $dataSet = array();
        while($row = odbc_fetch_array($resource)){
            if('SYSTEM TABLE' != $row['TABLE_TYPE'] && '~TMP' != substr($row['TABLE_NAME'], 0, 4)){
                $dataSet[] = $row['TABLE_NAME'];
            }
        }
        $result = $dataSet;
    }
    if($connid){
        odbc_close($connid);
    }
    return $result;
}

function get_mssql_fields(){
    $result = array();
    $connid = get_mssql_odbc_connect();
    $export_cate_table = config('export_cate_table');
    $sql = "SELECT TOP 1 * FROM {$export_cate_table['export_cate_table']}";
    $resource = odbc_exec($connid, $sql);
    if($resource){
        $dataSet = array();
        while($row = odbc_fetch_array($resource)){
            $dataSet[] = $row;
        }
        if(isset($dataSet[0])){
            $result = array_keys($dataSet[0]);
        }
    }
    if($connid){
        odbc_close($connid);
    }
    return $result;
}

function get_mssql_cates(){
    $result = array();
    $sql_select_field = '';

    $connid = get_mssql_odbc_connect();
    $export_cate_table = config('export_cate_table');
    $exprot_cate_map = config('exprot_cate_map');

    foreach($exprot_cate_map as $key=>$value){
        if(0 != strcasecmp($key, $value)){
            $sql_select_field .= '`' . $value . '` AS ' . '`' . $key . '`,';
        }else{
            $value = strtolower($value);
            $sql_select_field .= '`' . $value . '`,';
        }
    }

    $sql_select_field = trim($sql_select_field, ',');

    $sql = "SELECT {$sql_select_field} FROM `{$export_cate_table['export_cate_table']}` ";

//echo $sql;
    $resource = odbc_exec($connid, $sql);
    if($resource){
        $dataSet = array();
        while($row = odbc_fetch_array($resource)){
            $temp = array();
            foreach($row as $key=>$val){
                $temp[$key] = iconv('gb2312', "utf-8//IGNORE", $val);
            }
            $dataSet[] = $temp;
        }
        $result = $dataSet;
//        var_dump($result);
    }
    if($connid){
        odbc_close($connid);
    }
    return $result;
}

function get_mssql_main_fields(){
    $filelds = array();

    $export_main_table = config('export_main_table');
    $connid = get_mssql_odbc_connect();

    $sql = "SELECT TOP 1 * FROM {$export_main_table['master']}";
    $resource = odbc_exec($connid, $sql);
    if($resource){
        $dataSet = array();
        while($row = odbc_fetch_array($resource)){
            $dataSet[] = $row;
        }
        if(isset($dataSet[0])){
            $filelds = array_keys($dataSet[0]);
        }
    }

    //附加表字段
    if(!empty($export_main_table['slave'])){
        $sql = "SELECT TOP 1 * FROM {$export_main_table['slave']}";
        $resource = odbc_exec($connid, $sql);
        if($resource){
            $dataSet = array();
            while($row = odbc_fetch_array($resource)){
                $dataSet[] = $row;
            }
            if(isset($dataSet[0])){
             foreach(array_keys($dataSet[0]) as $item){
                 $filelds[] = $item;
             }
            }
        }
    }
    if($connid){
        odbc_close($connid);
    }
    return $filelds;
}


//======================================================================================================================

//数据库操作类 统一类 todo 注意本地链接要写127.0.0.1 否则会有一秒“回环延迟”
class DataBase
{
    protected $conTypeArray = array('pdo', 'mysqli_connect', 'mysql_connect');
    protected $conType = '';
    protected $db = null;
    public $db_config = array();


    function __construct($config)
    {
        $this->db_config = $config;

        foreach($this->conTypeArray as $item)
        {
            $this->conType = 'DataBase_' . $item;

            if(class_exists($this->conType))
            {
                if(!in_array('mysql', PDO::getAvailableDrivers()))
                {
                    continue;
                }
                $this->db = new $this->conType($config['dbhost'], $config['dbuser'], $config['dbpwd'], $config['dbname']);
                return $this->db;
            }
            elseif(function_exists($item))
            {

                $this->conType = str_ireplace('_connect', '', $this->conType);
                if(!class_exists($this->conType))
                {
                    exit('class ' . $this->conType . ' Not Found!');
                }

                $this->db = new $this->conType($config['dbhost'], $config['dbuser'], $config['dbpwd'], $config['dbname']);
                return $this->db;
            }
        }

        exit(json_encode(array(
            'id' => 0,
            'state' => -1,
            'msg' => 'fail',
            'data' => ' All DataBase Driver Not Found!'
        )));
    }

    function testconnect()
    {
        return $this->db->testconnect();
    }

    function connect()
    {

    }

    function select_db($dbname)
    {

    }

    function connect_error()
    {

    }

    function query($sql)
    {
        $sql = str_replace('#@__', $this->db_config['dbprefix'], $sql);
        return $this->db->query($sql);
    }

    function insert_id()
    {
        return $this->db->insert_id();
    }

    function fetch_array()
    {
        return $this->db->fetch_array();
    }

    function fetch_row()
    {
        return $this->db->fetch_row();
    }

    function close()
    {

    }

}


class DataBase_pdo
{
    protected $conn = null;
    protected $resource = null;

    function __construct($host, $user, $pwd, $dbname='')
    {
        $dns = "mysql:dbname={$dbname};host=" . $host;
        try
        {
            $this->conn = new PDO($dns, $user, $pwd);
        }
        catch (Exception $e)
        {
            exit(json_encode(array(
                'id' => 0,
                'state' => -1,
                'msg' => 'PDO connect fail',
                'data' => $e->getMessage()
            )));
        }
    }

    function testconnect()
    {
        if($this->conn){
            return true;
        }else{
            return false;
        }
    }

    function query($sql)
    {
        if($this->conn){
            return $this->resource = $this->conn->query($sql);
        }else{
            return false;
        }
    }

    function fetch_array()
    {
        if($this->resource){
            return $this->resource->fetchAll(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }

    function fetch_row()
    {
        if($this->resource){
            return $this->resource->fetch(PDO::FETCH_ASSOC);
        }else{
            return false;
        }
    }

    function insert_id()
    {
        if($this->conn){
            return $this->conn->lastInsertId();
        }else{
            return false;
        }
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->conn = null;
    }
}


class DataBase_mysqli
{
    protected $conn = null;
    protected $resource = null;


    function __construct($host, $user, $pwd, $dbname='')
    {
        $this->conn = mysqli_connect($host, $user, $pwd, $dbname);
        if (!$this->conn) {
            exit(json_encode(array(
                'id' => 0,
                'state' => -1,
                'msg' => 'Mysqli connect fail',
                'data' => 'To Database Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error()
            )));
        }
        mysqli_query($this->conn, "SET NAMES utf8");

    }

    function testconnect()
    {
        if($this->conn){
            return true;
        }else{
            return false;
        }
    }

    function query($sql)
    {
        return $this->resource = mysqli_query($this->conn, $sql);
    }

    function fetch_array()
    {
        $result = $row = array();
        while($row = mysqli_fetch_array($this->resource, MYSQLI_ASSOC))
        {
            $result[] = $row;
        }
        return $result;
    }

    function fetch_row()
    {
        return mysqli_fetch_row($this->resource);
    }

    function insert_id()
    {
        return mysqli_insert_id($this->conn);
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
        if($this->conn){
            mysqli_close($this->conn);
        }
    }
}


class DataBase_mysql
{
    protected $conn = null;
    protected $resource = null;
    protected $dbname = '';

    function __construct($host, $user, $pwd, $dbname='')
    {
        $this->dbname = $dbname;
        $this->conn = mysql_connect($host, $user, $pwd);
        if (!$this->conn) {
            exit(json_encode(array(
                'id' => 0,
                'state' => 0,
                'msg' => 'Mysql connect fail',
                'data' => 'To Database Connect Error (' . mysql_error($this->conn) . ') '
            )));
        }
        mysql_select_db($this->dbname, $this->conn);
        mysql_query("SET NAMES utf8", $this->conn);
    }

    function testconnect()
    {
        if($this->conn){
            return true;
        }else{
            return false;
        }
    }

    function query($sql)
    {
        mysql_select_db($this->dbname, $this->conn);
        $this->resource = mysql_query($sql, $this->conn);
        if(!$this->resource)
        {
            exit(json_encode(array(
                'id' => 0,
                'state' => 0,
                'msg' => 'mysql query error (' . mysql_error($this->conn) . ') ',
                'data' => $sql
            )));
        }
        return $this->resource;
    }

    function fetch_array()
    {
        $result = $row = array();
        while($row = mysql_fetch_array($this->resource, MYSQL_ASSOC))
        {
            $result[] = $row;
        }
        return $result;
    }

    function fetch_row()
    {
        return mysql_fetch_row($this->resource);
    }

    function insert_id()
    {
        return mysql_insert_id($this->conn);
    }

    function __destruct()
    {
        // TODO: Implement __destruct() method.
    }
}