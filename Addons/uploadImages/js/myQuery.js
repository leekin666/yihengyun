/*删除上传的图片（前台删除）*/
   $(document).delegate('.btn-close', 'click', function(e) {

       e.preventDefault();

       //删除与按钮所对应图片的data-id相同的 hidden input

       var id = $(this).parents(".upload-pre-item").find("img").data("id");

      for(var i = 0 ; i < $("#tab1 input[type='hidden']").length ; i++){

        if($("#tab1 input[type='hidden']").eq(i).val() == id){
          $("#tab1 input[type='hidden']").eq(i).remove();
          $(this).parents(".upload-pre-item").remove();
          return;
        }
      }
   })

/* 上传图片预览弹出层 */
