$(function(){
    
   /*删除上传的图片（前台删除）*/
   $(document).delegate('.btn-close', 'click', function() {
       //给当前删除链接添加一个类名以便查找到是第几个删除链接
       $(this).addClass("selected");
       //循环得到元素下标，并删除相应的图片和相应的hidden input
       for ( var i = 0 ; i < $(".btn-close").length ; i++){
           if($(".btn-close").eq(i).hasClass("selected")){
               $(".btn-close").eq(i).css('border','5px solid green');
               $(".btn-close").eq(i).removeClass("selected");
               //移除该图片所对应的的input
               $("#tab1").find("input[type='hidden']").eq(i).remove();
               //移除此图片
               $(this).parents(".upload-pre-item").remove();
               return;
           }
       }
   })

})
