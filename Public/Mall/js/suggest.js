/**
 * 焦点查询关键词
 * 
 * @param {type} keyword
 * @returns {undefined}
 * 
 * @Author guan
 */

var timer;
function lookup(keyword)
{

    clearTimeout(timer);

    var search_type = $("input[name='search_type']").val();
    //var category_list = document.getElementById('category');
    var suggestions = document.getElementById('suggestions');
	var category = json_languages.all;
    if(keyword.length == 0)
    {
        //隐藏建议框
        suggestions.style.display = 'none';
    }
    else
    {
        timer = setTimeout(function () {

            $.ajax({

                url: "/Mall/Search/lookup",

                type: "post",

                data:{'keyword':keyword,'search_type':search_type},


                success: function(data,textStatus){

                    //$('#divtest').css('background', 'yellow').html(data).show();

                    if(data)
                    {
                        //防止操作过快 显示上一步的筛选页面
                        var real_keword = document.getElementById('keyword').value;
                        if(real_keword.length > 0){
                            $("#suggestions").css('display', 'block');
                            $("#auto_suggestions_list").html(data);
                        }
                    }else{
                        $("#suggestions").css('display', 'none');
                    }

                    //$("#divtest2").html(data);

                },

                error: function(o){

                    //   alert(o.responseText);

                }

            });
        },500);
        document.documentElement.onkeyup = keyup;
    }

}

/**
 * 按键查询关键字
 * 
 * @type Number|Number|Number|@exp;li@pro;length
 * 
 * @author guan
 */




index = 0; //初始化索引
function keyup(e)
{
    clearTimeout(timer);
    var search_type = $("input[name='search_type']").val();
    var keyword = document.getElementById('keyword').value;
    var suggestions = document.getElementById('suggestions');
    var category_list = document.getElementById('category');
    //var category = category_list.options[category_list.selectedIndex].value;
    e = window.event || e;
    if(40 == e.keyCode) //按键盘向下键
    {
        var li = document.getElementById('suggestions_list_id').getElementsByTagName('li');
		if(index == 0 && document.getElementById('keyOne').value == 1)
		{
			index = -1;
			document.getElementById('keyOne').value = 0;
		}

		if(++index == li.length) { index = 0; }
        setView(li, index, li[index].title);
    }
    else if(38 == e.keyCode)//按键盘向上键
    {
        var li = document.getElementById('suggestions_list_id').getElementsByTagName('li');
        if(--index == -1) { index = li.length-1; }
        setView(li, index, li[index].title);
    }
    else
    {
        if(keyword.length == 0)
        {
            //隐藏建议框
            suggestions.style.display = 'none';
        }
        else
        {
            timer = setTimeout(function () {
			$.ajax({

				url: "/Mall/Search/lookup",

				type: "post",

				data:{'keyword':keyword,'search_type':search_type},

				success: function(data,textStatus){

				//$('#divtest').css('background', 'yellow').html(data).show();
				
				if(data)
				{     
                                    //防止操作过快 显示上一步的筛选页面
                                    var real_keword = document.getElementById('keyword').value;
                                    if(real_keword.length > 0){
                                        $("#suggestions").css('display', 'block');
                                        $("#auto_suggestions_list").html(data);
                                    }
                                    
				}

				//$("#divtest2").html(data);

				},

				 error: function(o){

				 //   alert(o.responseText);

				}

			});
            },500);
        }
    }
}

function change_suggestions_response(res)
{
    var suggestions = document.getElementById('suggestions');
    var auto_suggestions_list = document.getElementById('auto_suggestions_list');
    if(res.option)
    {
        suggestions.style.display = 'block';
        auto_suggestions_list.innerHTML = res.option;
    }
    else
    {
        auto_suggestions_list.innerHTML = 'error';
    }
}

/**
 * 设置背景颜色
 * 
 * @param {string} elems
 * @param {string} index
 * @returns {undefined}
 * 
 * @Author guan
 */
function setView(elems, index, str)
{
    var input_obj = document.getElementById('keyword');
    for(var j=0; j<elems.length; j++)
    {
        elems[j].style.background = '';
    }
    elems[index].style.background = '#ffdfc6';
	
	
    input_obj.value = str;
	//var str_word = '<span class="suggest_span">';
    //str = str.substr(0, str.indexOf(str_word));

    //str = str.replace(/<[^>]+>/g,"");
	
    //for(var i=0, len=str.length; i<len; i++)
	//{
		//str = str.replace('&nbsp;', " ");
	//}
}

/**
 * 隐藏提示框，并提交搜索
 * 
 * @param {type} this_value
 * @returns {undefined}
 * 
 * @author guan
 */
function fill(obj_value)
{
    //document.documentElement.onkeyup = false;
    var keyword = document.getElementById('keyword');
    if(obj_value)
    {
        keyword.value = obj_value;
        document.getElementById('searchForm').submit();
    }
	else
	{
		return false;
	}
}


function hide_suggest()
{
	var suggestions = document.getElementById('suggestions');
    setTimeout("suggestions.style.display='none'", 100);
}

function _over(li)
{
    var li_list = document.getElementById('suggestions_list_id').getElementsByTagName('li');
    for(var i=0, len=li_list.length; i<len; i++)
    {
        li_list[i].style.background = '';
        li.style.cursor = '';
    }
    li.style.background = '#f7f7f7';
    li.style.cursor = 'pointer';
}

function _out(li)
{
    li.style.background = '';
    li.style.cursor = '';
}

$(function(){
	$("#ECS_CARTINFO").mouseenter(function(){
		var eveval = $(this).data("carteveval");
		
		if(eveval == 0){
			$.ajax({
			   type: "POST",
			   url: "/Mall/Cart/cartInfo",
			   data: {block:"header"},
			   dataType:'html',
			   success: function(data){
			   	if(data==-9){
                    $("#ECS_CARTINFO").html('<div class="shopCart-con dsc-cm">' +
                        '<a href="/mall/cart/index">' +
                        '<i class="iconfont icon-carts"></i>' +
                        '<span>我的购物车</span>' +
                        '<em class="count cart_num">0</em>' +
                        '</a>' +
                        '</div>' +
                        '<div class="dorpdown-layer" ectype="dorpdownLayer">' +
                        '    <div class="prompt"><div class="nogoods"><b></b><span>请先 <a href="javascript:user_login()">登录！</a></span></div></div>' +
                        '</div>');
                    $("#ECS_CARTINFO").data("carteveval", 1);
                }else{
                    $("#ECS_CARTINFO").html(data);
                    $("#ECS_CARTINFO").data("carteveval", 1);
                }
			   },
			   beforeSend : function(){
				   //加载效果
				   try{
					   if(typeof load_cart_info != undefined){
						   $("*[ectype='dorpdownLayer']").html(load_cart_info);
					   }
				   }catch(e){}
			   }
			});
		}
	});
	
	$(document).click(function(){
		$(".suggestions_box").hide();
	});
	
	$(".suggestions_box").click(function(e){//自己要阻止
		e.stopPropagation();//阻止冒泡到body
	});
});