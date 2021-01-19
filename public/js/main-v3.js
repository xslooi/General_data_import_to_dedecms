// 全局变量设置
var myDomain = (function(){
    var cur_href = window.location.href;
    var cur_href_arr = cur_href.split('/');
    return cur_href.replace(cur_href_arr[cur_href_arr.length-1], '');
})();
// 私有全局变量
var _GLOBALS = {};
// console.log(myDomain);
// requirejs配置
requirejs.config({
    baseUrl: "public/js",
    shim: {
        'jquery.ui': ['jquery'],
        'jquery.dragsort': ['jquery'],
        'jquery.ztree.all.min': ['jquery'],
        'overhang.min': ['jquery'],
    },
    paths : {
        jquery : 'lib/jquery',
        custom : 'lib/custom',
        'jquery.ui' : 'lib/jquery-ui',
        'jquery.dragsort' : 'lib/jquery.dragsort',
        'jquery.ztree.all.min' : 'lib/jquery.ztree.all.min',
        'overhang.min' : 'lib/overhang.min',
    },
    waitSeconds: 5,
});

// 目录中其他js直接引入
// require(['other']);

// requirejs 主逻辑
requirejs(['jquery', 'custom', 'jquery.ui', 'jquery.ztree.all.min'], function($, custom){
    // ===========================================================
    // 初始化操作start
    // ===========================================================
    // 配置状态
    $( "#controlgroup" ).controlgroup();

    // 折叠菜单
    $( "#accordion" ).accordion();

    //进度条初始化
    $( "#progressbar" ).progressbar({
        value: 0,
    });

    // ===========================================================
    // 初始化操作end
    // ===========================================================


    // ===========================================================
    // 函数库start
    // ===========================================================
    // 自定义函数库变量
    var functionLib = {};

    functionLib.checkExportDBConfig = function() {
        var formData = {};

        $.ajax({
            url: myDomain + 'api.php?act=from_cfg_test',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                console.log(data);
                if(1 == data.state){
                    $('#from-db-state').removeClass('ui-state-error').addClass('ui-state-active').html('已OK');

                    functionLib.resetExportTreeData();
                }else{
                    $('#from-db-state').html('连接失败');
                }
                // $("#messageBox").html(data.desc);
            },
            error: function (data) {

            },
            complete: function (XHR, TS) {

            }
        });
    };

    functionLib.checkImportDBConfig = function() {
        var formData = {};

        $.ajax({
            url: myDomain + 'api.php?act=to_cfg_test',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                console.log(data);
                if(1 == data.state){
                    $('#to-db-state').removeClass('ui-state-error').addClass('ui-state-active').html('已OK');
                    functionLib.resetImportTreeData();
                }else{
                    $('#to-db-state').html('连接失败');
                }
                // $("#messageBox").html(data.desc);
            },
            error: function (data) {

            },
            complete: function (XHR, TS) {

            }
        });
    };

    functionLib.saveExportDBConfig = function() {
        var formData = $("#from_cfg_form").serialize();

        $.ajax({
            url: myDomain + 'api.php?act=from_cfg_save',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                console.log(data);
                if(1 == data.state){

                    //选择状态
                    functionLib.checkExportDBConfig();

                    custom.alert('保存成功！', 'success');
                }else{
                    custom.alert('保存失败，请检查！');
                }
                // $("#messageBox").html(data.desc);
            },
            error: function (data) {

            },
            complete: function (XHR, TS) {

            }
        });
    };

    functionLib.saveImportDBConfig = function() {
        var formData = $("#to_cfg_form").serialize();

        $.ajax({
            url: myDomain + 'api.php?act=to_cfg_save',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                console.log(data);
                if(1 == data.state){

                    //多选勾选状态
                    functionLib.checkImportDBConfig();

                    custom.alert('保存成功！', 'success');
                }else{
                    custom.alert('保存失败，请检查！');
                }
                // $("#messageBox").html(data.desc);
            },
            error: function (data) {

            },
            complete: function (XHR, TS) {

            }
        });
    };

    functionLib.saveExportFieldConfig = function() {

    };

    functionLib.resetExportFieldConfig = function() {

    };

    functionLib.saveImportTypeidConfig = function() {
        var to_tree = $.fn.zTree.getZTreeObj("toTree");


        if(to_tree == null) {
            custom.alert('未加载导入数据库配置！');
            return;
        }

        var to_nodes = to_tree.getCheckedNodes();

        if(typeof to_nodes[0] == 'undefined') {
            custom.alert('未选择导入数据库配置！');
            return;
        }

        var formData = {to_typeid: to_nodes[0].id};


        $.ajax({
            url: myDomain + 'api.php?act=import_cfg_save',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {

                if(data.state == 1){
                    //保存成功
                    custom.alert(data.msg, 'success');
                    $('#to-type-state').removeClass('ui-state-error').addClass('ui-state-active').html(to_nodes[0].name);

                }else{
                    custom.alert(data.msg);
                }
            }
        });
    };

    functionLib.saveExportTypeidConfig = function() {
        var from_tree = $.fn.zTree.getZTreeObj("fromTree");
        var to_tree = $.fn.zTree.getZTreeObj("toTree");

        if(from_tree == null) {
            custom.alert('未加载导出数据库配置！');
            return;
        }

        if(to_tree == null) {
            custom.alert('未加载导入数据库配置！');
            return;
        }

        var from_nodes = from_tree.getCheckedNodes();
        var to_nodes = to_tree.getCheckedNodes();

        console.log(from_nodes);
        console.log(to_nodes);

        if(typeof from_nodes[0] == 'undefined') {
            custom.alert('未选择导出数据库配置！');
            return;
        }

        if(typeof to_nodes[0] == 'undefined') {
            custom.alert('未选择导入数据库配置！');
            return;
        }

        var formData = {from_typeid: from_nodes[0].id, to_typeid: to_nodes[0].id};


        $.ajax({
            url: myDomain + 'api.php?act=import_cfg_save',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {

                if(data.state == 1){
                    //保存成功
                    custom.alert(data.msg, 'success');
                    $('#from-type-state').removeClass('ui-state-error').addClass('ui-state-active').html(from_nodes[0].name);
                    $('#to-type-state').removeClass('ui-state-error').addClass('ui-state-active').html(to_nodes[0].name);

                }else{
                    custom.alert(data.msg);
                }
            }
        });
    };

    functionLib.resetExportTreeData = function() {

        var formData = {};

        // 栏目树插件
        var from_tree_setting = {
            check: {
                enable: true,
                chkStyle: "radio",
                radioType: "all"
            },
            data: {
                simpleData: {
                    enable: true
                }
            }
        };

        // 树插件节点
        var zNodes;

        $.ajax({
            url: myDomain + 'api.php?act=import_cfg_reset_from',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                console.log(data);
                zNodes = data.data;

                if(zNodes){
                    $.fn.zTree.init($("#fromTree"), from_tree_setting, zNodes);
                }else{
                    console.log('formTree Data Error!');
                    custom.alert('formTree Data Error!');
                }
            }
        });
    };

    functionLib.resetImportTreeData = function() {
        var formData = {};

        // 栏目树插件
        var to_tree_setting = {
            check: {
                enable: true,
                chkStyle: "radio",
                radioType: "all"
            },
            data: {
                simpleData: {
                    enable: true
                }
            }
        };

        // 树插件节点
        var zNodes;

        $.ajax({
            url: myDomain + 'api.php?act=import_cfg_reset_to',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                console.log(data);
                zNodes = data.data;

                if(data.state == -1){
                    custom.alert(data.msg + ' 错误请检查');
                    return;
                }

                if(zNodes){
                    $.fn.zTree.init($("#toTree"), to_tree_setting, zNodes);
                }else{
                    console.log('toTree Data Error!');
                    custom.alert('toTree Data Error!');
                }

            }
        });
    };

    functionLib.saveIsExportImages = function($isExportImages) {
        // console.log($isExportImages);
        var formData = {is_export_images: $isExportImages};

        $.ajax({
            url: myDomain + 'api.php?act=import_cfg_export_images',
            data: formData,
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function (data) {
                console.log(data);
            }
        });

    };

    // 2018年11月28日17:22:49 xslooi v2 函数库
    functionLib.resetExportTablesList = function(){
        $.ajax({
            url: myDomain + 'api.php?act=v3_resetExportTablesList',
            data: {},
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function (data) {
                // console.log(data);
                if(data.state == -1){
                    custom.alert(data.msg + ' 错误请检查');
                    return;
                }


                if(data.data){
                    var arr = data.data;
                    var list_html = '';

                    for(var j = 0,len = arr.length; j < len; j++){
                        list_html += '<li><i>X</i><div data-id="'+ j +'" style="cursor: pointer;">'+ arr[j] +'</div></li>';
                    }

                    $("#from_tables_lists").html(list_html);

                    $("#from_tables_lists li i").click(function(){
                        $(this).parent().remove()
                    });
                }
            }
        });
    };
    functionLib.saveExportCateTableConfig = function(){
        var export_cate_table = $("#form_table_cate li div").text();
        // console.log(export_cate_table);
        if(!export_cate_table){
            custom.alert('请选择栏目表');
            return false;
        }

        $.ajax({
            url: myDomain + 'api.php?act=saveExportCateTable',
            data: {export_cate_table: export_cate_table},
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function (data) {
                console.log(data);
                if(1 == data.state){
                    custom.alert('保存成功！', 'success');
                }else{
                    custom.alert('保存失败，请检查！');
                }
            }
        });
    };
    functionLib.resetExportCateField = function(){
        $.ajax({
            url: myDomain + 'api.php?act=v3_resetExportCateField',
            data: {},
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function (data) {
                // console.log(data);
                if(data.state == -1){
                    custom.alert(data.msg + ' 错误请检查');
                    return;
                }

                
                if(data.data){
                    var arr = data.data;
                    var list_html = '';

                    for(var j = 0,len = arr.length; j < len; j++){
                        list_html += '<li><i>X</i><div data-id="'+ j +'" style="cursor: pointer;">'+ arr[j] +'</div></li>';
                    }

                    $("#from_field_lists").html(list_html);

                    $("#from_field_lists li i").click(function(){
                        $(this).parent().remove()
                    });
                }
            }
        });
    };
    functionLib.saveExprotCateMapConfig = function(){
        var id = $("#to_field_id li div").text();
        var pid = $("#to_field_pid li div").text();
        var name = $("#to_field_name li div").text();

        var formData = {id: id, pId: pid, name: name};

        console.log(formData);
        if(!id || !pid || !name){
            custom.alert('请选择导入数据表字段列表');
            return false;
        }

        $.ajax({
            url: myDomain + 'api.php?act=saveExportCateMap',
            data: formData,
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function (data) {
                console.log(data);
                if(1 == data.state){
                    custom.alert('保存成功！', 'success');
                }else{
                    custom.alert('保存失败，请检查！');
                }
            }
        });
    };
    functionLib.resetExportCateList = function() {

        var formData = {};

        // 栏目树插件
        var from_tree_setting = {
            check: {
                enable: true,
                chkStyle: "radio",
                radioType: "all"
            },
            data: {
                simpleData: {
                    enable: true
                }
            }
        };

        // 树插件节点
        var zNodes;

        $.ajax({
            url: myDomain + 'api.php?act=v3_resetExportCateList',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                console.log(data);
                zNodes = data.data;

                if(data.state == -1){
                    custom.alert(data.msg + ' 错误请检查');
                    return;
                }

                if(zNodes){
                    $.fn.zTree.init($("#fromTree"), from_tree_setting, zNodes);
                }else{
                    console.log('formTree Data Error!');
                    custom.alert('formTree Data Error!');
                }
            }
        });
    };
    functionLib.saveExportTypeid = function() {
        var from_tree = $.fn.zTree.getZTreeObj("fromTree");

        if(from_tree == null) {
            custom.alert('未加载导出数据库配置！');
            return;
        }

        var from_nodes = from_tree.getCheckedNodes();

        console.log(from_nodes);

        if(typeof from_nodes[0] == 'undefined') {
            custom.alert('未选择导出数据库配置！');
            return;
        }

        var formData = {from_typeid: from_nodes[0].id};


        $.ajax({
            url: myDomain + 'api.php?act=import_cfg_save',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {

                if(data.state == 1){
                    //保存成功
                    custom.alert(data.msg, 'success');
                    $('#from-type-state').removeClass('ui-state-error').addClass('ui-state-active').html(from_nodes[0].name);

                }else{
                    custom.alert(data.msg);
                }
            }
        });
    };
    functionLib.resetExportTables = function(){
        $.ajax({
            url: myDomain + 'api.php?act=v3_resetExportTablesList',
            data: {},
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function (data) {
                // console.log(data);

                if(-1 == data.state){
                    custom.alert(data.msg + ' 错误请检查');
                    return;
                }

                if(data.data){
                    var arr = data.data;
                    var list_html = '';

                    for(var j = 0,len = arr.length; j < len; j++){
                        list_html += '<li><i>X</i><div data-id="'+ j +'" style="cursor: pointer;">'+ arr[j] +'</div></li>';
                    }

                    $("#export_table_lists").html(list_html);

                    $("#export_table_lists li i").click(function(){
                        $(this).parent().remove()
                    });
                }
            }
        });
    };
    functionLib.saveExportMainTable = function(){
        var master = $("#from_table_master li div").text();
        var slave = $("#from_table_additional li div").text();


        if(!master){
            custom.alert('请选择导出数据主表');
            return false;
        }

        var formData = {master: master, slave: slave};

        console.log(formData);

        $.ajax({
            url: myDomain + 'api.php?act=saveExportMainTable',
            data: formData,
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function (data) {
                console.log(data);
                if(1 == data.state){
                    custom.alert('保存成功！', 'success');
                }else{
                    custom.alert('保存失败，请检查！');
                }
            }
        });
    };
    functionLib.resetExportFieldList = function(){
        $.ajax({
            url: myDomain + 'api.php?act=v3_resetExportFieldList',
            data: {},
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function (data) {
                // console.log(data);
                if(-1 == data.state){
                    custom.alert(data.msg + ' 错误请检查');
                    return;
                }

                if(data.data){
                    var arr = data.data;
                    var list_html = '';

                    for(var j = 0,len = arr.length; j < len; j++){
                        list_html += '<li><i>X</i><div data-id="'+ j +'" style="cursor: pointer;">'+ arr[j] +'</div></li>';
                    }

                    $("#export_fields_lists").html(list_html);

                    $("#export_fields_lists li i").click(function(){
                        $(this).parent().remove()
                    });
                }
            }
        });
    };
    functionLib.resetImportFieldList = function(){
        $.ajax({
            url: myDomain + 'api.php?act=resetImportFieldList',
            data: {},
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function (data) {
                // console.log(data);
                if(-1 == data.state){
                    custom.alert(data.msg + ' 错误请检查');
                    return;
                }

                if(data.data){
                    var arr = data.data;
                    var list_html = '<li><label>（类别关联字段-typeid）：</label><ul class="to-field importField" id="category_id"></ul></li>';
                    list_html += '<li><label>（主表关联字段-可选）：</label><ul class="to-field importField" id="master_id"></ul></li>';
                    list_html += '<li><label>（附表关联字段-关联2）：</label><ul class="to-field importField" id="slave_id"></ul></li>';
                    var selector = '#export_fields_lists';
                    var Xselector = '#category_id li i,#master_id li i,#slave_id li i,'; //拖拉关闭按钮选择器

                    for(var j = 0,len = arr.length; j < len; j++){
                        list_html += '<li><label>（'+ arr[j] +'）：</label><ul class="to-field importField" id="to_field_'+ arr[j] +'"></ul></li>';

                        selector += ',#category_id,#master_id,#slave_id,#to_field_' + arr[j];

                        if(j == 0){
                            Xselector += '#to_field_' + arr[j] + ' li i';
                        }else{
                            Xselector += ',#to_field_' + arr[j] + ' li i';
                        }
                    }

                    $("#import_fields_lists").html(list_html);

                    // 步骤七：加载导入数据表拖拉配置与导入表字段关联
                    var setp7 = function(){
                        $(Xselector).hide();

                        $("#export_fields_lists li i").show();
                    };

                    // console.log(selector);
                    setTimeout(function(){
                        $(""+ selector+"").dragsort({ dragSelector: "div", dragBetween: true, dragEnd: setp7, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });
                    }, 1000);

                }
            }
        });
    };
    functionLib.saveFieldRelevance = function(){

        var formData = {};

        $(".importField").each(function(){
            var that = $(this);
            // console.log(that);
            var inputField = that.attr('id').replace('to_field_', '');
            var outputField = that.text().substring(1);
            // formData.that.attr('id').replace('to_field_', '')
            // console.log(that.attr('id').replace('to_field_', ''));
            // console.log(that.children("li div").text());
            formData[inputField] = outputField;
        });

        console.log(formData);

        if($("#export_fields_lists li").length < 2){
            custom.alert('请重置导出数据表字段列表');
            return false;
        }

        if($("#import_fields_lists li").length < 2){
            custom.alert('请重置导入数据表字段列表');
            return false;
        }

        if(!formData.category_id){
            custom.alert('请选择类别字段名称');
            return false;
        }

        if(!formData.title){
            custom.alert('请选择导入标题字段（最少这个一个）');
            return false;
        }

        $.ajax({
            url: myDomain + 'api.php?act=saveFieldRelevance',
            data: formData,
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function (data) {
                console.log(data);
                if(1 == data.state){
                    custom.alert('保存成功！', 'success');
                }else{
                    custom.alert('保存失败，请检查！');
                }
            }
        });
    };
    functionLib.relevanceReload = function(){
        $.ajax({
            url: myDomain + 'api.php?act=relevanceReload',
            data: {},
            type: 'POST',
            dataType: 'json',
            async: false,
            success: function (data) {
                // console.log(data);
                if(-1 == data.state){
                    custom.alert(data.msg + ' 错误请检查');
                    return;
                }

                if(data.data){
                    var arr = data.data;
                    var list_html = '<li><b>导入字段名</b><b>导出字段名</b><b>字段转换配置</b></li>';
                    var i = 0;
                    _GLOBALS.inputFields = [];//全局 关联字段变量

                    for(var key in arr){
                        _GLOBALS.inputFields[i++] = key; //全局 关联字段变量

                        list_html += '<li><em>'+ key +'</em><em>'+ arr[key] +'</em>\n' +
                            '                <div class="relevance_box">\n' +
                            '                    <div class="attr_convert_coding">\n' +
                            '                        <input type="radio" name="convert_coding_'+ key +'" value="GB2312" > GB2312\n' +
                            '                        <input type="radio" name="convert_coding_'+ key +'" value="GBK"> GBK 转 UTF-8\n' +
                            '                    </div>\n' +
                            '                    操作：\n' +
                            '                    <select class="rele_select" name="rele_key_'+ key +'">\n' +
                            '                        <option value="0">请选择操作</option>\n' +
                            '                        <option value="time">字符串转时间</option>\n' +
                            '                        <option value="substr">截取字符</option>\n' +
                            '                        <option value="defaultvalue">默认内容</option>\n' +
                            '                        <option value="replace">替换内容</option>\n' +
                            '                    </select>\n' +
                            '                    <div class="relevance_attr arrt_'+ key +' substr_'+ key +'">\n' +
                            '                        截取长度：<input type="text" value="" name="substr_length_'+ key +'">\n' +
                            '                    </div>\n' +
                            '                    <div class="relevance_attr arrt_'+ key +' defaultvalue_'+ key +'">\n' +
                            '                        默认内容：<input type="text" value="" name="defaulttext_'+ key +'">\n' +
                            '                    </div>\n' +
                            '                    <div class="relevance_attr arrt_'+ key +' replace_'+ key +'">\n' +
                            '                        查找内容：<input type="text" value="" name="searchvalue_'+ key +'">\n' +
                            '                        替换内容：<input type="text" value="" name="replacevalue_'+ key +'">\n' +
                            '                    </div>\n' +
                            '                </div>\n' +
                            '            </li>';
                    }

                    $("#relevance_field_lists").html(list_html);

                    //选择内容后操作
                    $(".rele_select").change(function(){
                        var that = $(this);
                        var type = that.val();
                        var suffix = that.attr('name').substr(9);

                        //隐藏操作内容
                        $(".arrt_" + suffix).hide();

                        //显示操作内容
                        $("." + type + "_" + suffix).attr('style', 'display: inline-block;');

                    });
                }
            }
        });
    };
    functionLib.relevanceReset = function() {
        $.ajax({
            url: myDomain + 'api.php?act=relevanceReset',
            data: {},
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                // console.log(data);
                ResponseData = data.data;

                if(ResponseData){
                    custom.alert('relevanceReset重置成功', 'success');
                }else{
                    custom.alert('relevanceReset重置失败');
                }

            }
        });
    };
    functionLib.relevanceSave = function() {
       var formData = {};
        if(!_GLOBALS.inputFields){
            custom.alert('没有数据请先加载列表并配置！');
            return;
        }

        // console.log(_GLOBALS);
        // 组装字段配置HTML
        for(var i=0,len=_GLOBALS.inputFields.length; i < len; i++){
            var fieldConfig = [];
            var convertValue = $('input:radio[name="convert_coding_' + _GLOBALS.inputFields[i] + '"]:checked').val();
            if(!convertValue){
                convertValue = '';
            }
            fieldConfig[0] = 'convert|' + convertValue;

            var selectValue = $('select[name="rele_key_' + _GLOBALS.inputFields[i] + '"]').val();
            var selectConfig = '';

            switch(selectValue){
                case 'time':
                    selectConfig = 'time|';
                    break;
                case 'substr':
                    var substr_len = $('input[name="substr_length_' + _GLOBALS.inputFields[i] + '"]').val();
                    selectConfig = 'substr|' + substr_len;
                    break;
                case 'defaultvalue':
                    var defultvalue = $('input[name="defaulttext_' + _GLOBALS.inputFields[i] + '"]').val();
                    selectConfig = 'defaultvalue|' + defultvalue;
                    break;
                case 'replace':
                    var searchvalue = $('input[name="searchvalue_' + _GLOBALS.inputFields[i] + '"]').val();
                    var replacevalue = $('input[name="replacevalue_' + _GLOBALS.inputFields[i] + '"]').val();
                    selectConfig = 'replace|' + searchvalue + '|' + replacevalue;
                    break;
            }

            fieldConfig[1] = selectConfig;

            formData[_GLOBALS.inputFields[i]] = fieldConfig; //对象动态添加属性和值
        }

        // console.log(formData);
        // return;

        // 测试数据
        // var formData = {
        //     "click" : ["convert|", "time"],
        //     "title" : ["convert|gb2312", "substr|85"],
        //     "shorttitle" : ["convert|gb2312", "defaultvalue|"],
        //     "writer" : ["convert|gb2312", "defaultvalue|网络"],
        //     "source" : ["convert|gb2312", "defaultvalue|未知"],
        //     "litpic" : ["convert|", "replace|../|/"],
        // };


        $.ajax({
            url: myDomain + 'api.php?act=relevanceSave',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                console.log(data);
                if(1 == data.state){
                    custom.alert('保存成功！', 'success');
                }else{
                    custom.alert('保存失败，请检查！');
                }
            }
        });
    };
    // ===========================================================
    // 函数库end
    // ===========================================================


    //返回菜单
    $( "#backMenu" ).off("click").click(function( event ) {
        window.location.href=myDomain;
        event.preventDefault();
    });
    // 检测导出配置
    $( "#from-cfg-button" ).off("click").click(function( event ) {
        // alert('from-cfg-button');
        functionLib.checkExportDBConfig();

        event.preventDefault();
    });
    // 检测导入配置
    $( "#to-cfg-button" ).off("click").click(function( event ) {
        // alert('to-cfg-button');
        functionLib.checkImportDBConfig();
        event.preventDefault();
    });



    // 保存导出配置
    $( "#from-cfg-save-button" ).off("click").click(function( event ) {
        // alert('from-cfg-save-button');
        functionLib.saveExportDBConfig();

        event.preventDefault();
    });

    // 保存导入配置
    $( "#to-cfg-save-button" ).off("click").click(function( event ) {
        // alert('to-cfg-save-button');
        functionLib.saveImportDBConfig();

        event.preventDefault();
    });


    // 保存导入字段配置
    $( "#field-cfg-save-button" ).off("click").click(function( event ) {
        // alert('field-cfg-save-button');
        var formData = {
            'title': $('#to_field_title li div').attr("data-field"),
            'litpic': $('#to_field_litpic li div').attr("data-field"),
            'pubdate': $('#to_field_pubdate li div').attr("data-field"),
            'body': $('#to_field_body li div').attr("data-field"),
        };

        $.ajax({
            url: myDomain + 'api.php?act=field_cfg_save',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                console.log(data);
                if(1 == data.state){

                }
                // $("#messageBox").html(data.desc);
            },
            error: function (data) {

            },
            complete: function (XHR, TS) {

            }
        });

        event.preventDefault();
    });

    // 重置导入字段属性
    $( "#field-cfg-reset-button" ).off("click").click(function( event ) {

        // alert('field-cfg-reset-button');
        var formData = {};

        $.ajax({
            url: myDomain + 'api.php?act=field_cfg_reset',
            data: formData,
            type: 'POST',
            dataType: 'json',
            success: function (data) {
                console.log(data);
                if(1 == data.state && 1 < data.data.length){
                    var result = data.data;
                    // console.log(result);
                    var fieldlists = [];
                    for(var i=0,len=result.length; i<len; i++){
                        // console.log(i);
                        fieldlists.push('<li><div data-field="'+result[i].Field+'">'+result[i].Field+'('+result[i].Type+')</div></li>');
                    }
                    $('#from_field_lists').html(fieldlists);
                }
                // $("#messageBox").html(data.desc);
            },
            error: function (data) {

            },
            complete: function (XHR, TS) {

            }
        });

        event.preventDefault();
    });


    // 保存导入字段ID配置
    $( "#import-cfg-save-button" ).off("click").click(function( event ) {
        // alert('import-cfg-save-button');
        functionLib.saveImportTypeidConfig();

        event.preventDefault();
    });

    // 重置导出字段ID
    $( "#import-cfg-reset-from-button" ).off("click").click(function( event ) {
        // alert('import-cfg-reset-button');
        functionLib.resetExportTreeData();

        event.preventDefault();
    });

    // 重置导入字段ID
    $( "#import-cfg-reset-to-button" ).off("click").click(function( event ) {
        // alert('import-cfg-reset-button');
        functionLib.resetImportTreeData();

        event.preventDefault();
    });

    //是否导出图片
    $( "#is-export-images" ).off("click").click(function( event ) {
        // alert('是否导出图片？') ;
        // console.log($(this).context.checked);
        functionLib.saveIsExportImages($(this).context.checked);
    });


    // 开始导入
    $( "#start-button" ).off("click").click(function( event ) {

        // custom.alert('这是一个错误的提示信息！');
        console.log(myDomain);

        var confirmStr = '确定从【' + $("#from-type-state").text() + '】导入到【' + $("#to-type-state").text() + '】？';

        if(!confirm(confirmStr)){
            return;
        }

        custom.start('v3');

        event.preventDefault();
    });


    //默认页面加载关闭导出图片
    functionLib.saveIsExportImages(false);


    // 2018年11月28日17:57:13 xslooi V2 添加
    //重置数据表数据
    $("#form-cate-cfg-reset-button").off("click").click(function( event ){
        functionLib.resetExportTablesList();
    });

    //保存导入栏目id
    $("#import-type-save-button").off("click").click(function( event ){
        functionLib.saveImportTypeidConfig();
    });

    // 保存导出栏目数据表
    $("#form-cate-cfg-save-button").off("click").click(function( event ){
        functionLib.saveExportCateTableConfig();
    });

    // 重置类别表字段
    $("#field-cate-reset-button").off("click").click(function( event ){
        functionLib.resetExportCateField();
    });

    // 保存类别表字段映射
    $("#field-cate-save-button").off("click").click(function( event ){
        functionLib.saveExprotCateMapConfig();
    });

    // 重置保存栏目数据
    $("#export-cfg-reset-button").off("click").click(function( event ){
        functionLib.resetExportCateList();
    });

    // 保存导出栏目ID
    $("#export-cfg-save-button").off("click").click(function( event ){
        functionLib.saveExportTypeid();
    });

    // 重置导出数据表字段列表
    $("#export-table-reset-button").off("click").click(function( event ){
        functionLib.resetExportTables();
    });

    // 保存导出数据-主表
    $("#export-table-save-button").off("click").click(function( event ){
        functionLib.saveExportMainTable();
    });

    // 重置导出数据库字段列表
    $("#field-export-reset-button").off("click").click(function( event ){
        functionLib.resetExportFieldList();
    });

    // 重置导入数据库字段列表
    $("#field-import-reset-button").off("click").click(function( event ){
        functionLib.resetImportFieldList();
    });

    // 保存数据表关联配置
    $("#field-map-save-button").off("click").click(function( event ){
        functionLib.saveFieldRelevance();
    });

    //重新加载关联列表
    $("#relevance-reload").off("click").click(function( event ){
        functionLib.relevanceReload();
    });

    //重置关联列表配置数据（即 删除配置文件）
    $("#relevance-reset").off("click").click(function( event ){
        functionLib.relevanceReset();
    });

    //保存关联列表配置数据
    $("#relevance-save").off("click").click(function( event ){
        functionLib.relevanceSave();
    });

    //
    $("#updata-file-button").off("click").click(function(){
        var fileName = $('#access_file').val();　　　　　　　　　　　　　　　　　　//获得文件名称
        var fileType = fileName.substr(fileName.length-4,fileName.length);　　//截取文件类型,如(.xls)

        $.ajax({
            url: myDomain + 'api.php?act=uploadAccessFile',　　　　　　　　　　//上传地址
            type: 'POST',
            cache: false,
            data: new FormData($('#from_cfg_form')[0]),　　　　　　　　　　　　　//表单数据
            processData: false,
            contentType: false,
            dataType: "json",
            success: function(data){
                console.log(data);
                if(data.state == 1){
                    //保存成功
                    custom.alert(data.msg, 'success');
                    $('#access_file').val('')
                    $('#from-db-state').removeClass('ui-state-error').addClass('ui-state-active').html('已OK');

                }else{
                    custom.alert(data.msg);
                }
            },
            error: function(xhr, status, error){
                console.log(xhr);
                console.log(status);
                console.log(error);
            },
            complete: function(xhr, status){
                console.log(xhr);
                console.log(status);
            }
        });
    });


});

//自定义模块
require(['custom'], function (custom) {
    // custom.change();


});

// 拖拽插件
require(['jquery', 'jquery.dragsort'], function ($) {
    var saveOrder = function saveOrder() {
        $("#to_field_title li i").hide();
        $("#from_field_lists li i").show();
        return;
        var data = $("#from_field_lists li").map(function() {
            return $(this).children().html();
        }).get();

        $("input[name=list1SortOrder]").val(data.join("|"));
    };

    // $("#from_field_lists, #to_field_title, #to_field_litpic, #to_field_pubdate, #to_field_body").dragsort({ dragSelector: "div", dragBetween: true, dragEnd: saveOrder, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });



    // 导出数据字段列表删除控制
    $("#from_tables_lists li i").click(function(){
        $(this).parent().remove()
    });

    // 步骤三：选择导出数据库栏目表 拖拉
    var setp3 = function(){
        $("#form_table_cate li i").hide();

        $("#from_tables_lists li i").show();
    };
    $("#from_tables_lists, #form_table_cate").dragsort({ dragSelector: "div", dragBetween: true, dragEnd: setp3, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });

    // 步骤四：选择导出栏目表关联字段 拖拉
    var setp4 = function(){
        $("#to_field_id li i").hide();
        $("#to_field_pid li i").hide();
        $("#to_field_name li i").hide();

        $("#from_field_lists li i").show();
    };
    $("#from_field_lists, #to_field_id, #to_field_pid, #to_field_name").dragsort({ dragSelector: "div", dragBetween: true, dragEnd: setp4, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });


    // 步骤六：加载数据表并选择导出栏目数据主表
    var setp6 = function(){
        $("#from_table_master li i").hide();
        $("#from_table_additional li i").hide();

        $("#export_table_lists li i").show();
    };
    $("#export_table_lists, #from_table_master, #from_table_additional").dragsort({ dragSelector: "div", dragBetween: true, dragEnd: setp6, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });

    // 步骤七：加载导入数据表拖拉配置与导入表字段关联
    // var setp7 = function(){
        // $("#from_table_master li i").hide();
        // $("#from_table_additional li i").hide();
        //
        // $("#export_fields_lists li i").show();
    // };
    // $("#export_fields_lists").dragsort({ dragSelector: "div", dragBetween: true, dragEnd: setp7, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });

});