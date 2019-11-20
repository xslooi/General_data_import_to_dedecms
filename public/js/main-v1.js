// 全局变量设置
var myDomain = (function(){
    var cur_href = window.location.href;
    var cur_href_arr = cur_href.split('/');
    return cur_href.replace(cur_href_arr[cur_href_arr.length-1], '');
})();

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

    // 操作说明
    $( "#dialog" ).dialog({
        autoOpen: false,
        width: 400,
        buttons: [
            {
                text: "Ok",
                click: function() {
                    $( this ).dialog( "close" );
                }
            },
            {
                text: "Cancel",
                click: function() {
                    $( this ).dialog( "close" );
                }
            }
        ]
    });

    $( "#dialog-link" ).click(function( event ) {
        $( "#dialog" ).dialog( "open" );
        event.preventDefault();
    });

    // 操作步骤：tab菜单
    $( "#tabs" ).tabs();

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

                    custom.alert('保存配置成功', 'success');
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

                    custom.alert('保存配置成功', 'success');

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

        custom.start();

        event.preventDefault();
    });


    //默认页面加载关闭导出图片
    functionLib.saveIsExportImages(false);


    function fileChange(base){
        $("#fileTypeError").html('');
        var fileName = $('#file_upload').val();　　　　　　　　　　　　　　　　　　//获得文件名称
        var fileType = fileName.substr(fileName.length-4,fileName.length);　　//截取文件类型,如(.xls)
        if(fileType=='.xls' || fileType=='.doc' || fileType=='.pdf'){　　　　　//验证文件类型,此处验证也可使用正则
            $.ajax({
                url: base+'/actionsupport/upload/uploadFile',　　　　　　　　　　//上传地址
                type: 'POST',
                cache: false,
                data: new FormData($('#uploadForm')[0]),　　　　　　　　　　　　　//表单数据
                processData: false,
                contentType: false,
                success:function(data){
                    if(data=='fileTypeError'){
                        $("#fileTypeError").html('*上传文件类型错误,支持类型: .xsl .doc .pdf');　　//根据后端返回值,回显错误信息
                    }
                    $("input[name='enclosureCode']").attr('value',data);
                }
            });
        }else{
            $("#fileTypeError").html('*上传文件类型错误,支持类型: .xls .doc .pdf');
        }
    }
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

    $("#from_field_lists, #to_field_title, #to_field_litpic, #to_field_pubdate, #to_field_body").dragsort({ dragSelector: "div", dragBetween: true, dragEnd: saveOrder, placeHolderTemplate: "<li class='placeHolder'><div></div></li>" });

    // 导出数据字段列表删除控制
    $("#from_field_lists li i").click(function(){
        console.log($(this));
        $(this).parent().remove()
    });
});


// 自定义函数库区
// =====================================================================================================================
// =====================================================================================================================
