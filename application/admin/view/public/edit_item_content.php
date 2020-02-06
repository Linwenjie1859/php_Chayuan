<!doctype html>
<!--suppress JSAnnotator -->
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>编辑内容</title>
    <link href="{__FRAME_PATH}css/font-awesome.min.css" rel="stylesheet">
    <link href="{__ADMIN_PATH}plug/umeditor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">
    <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/jquery.min.js"></script>
    <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/third-party/template.min.js"></script>
    <script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/umeditor/umeditor.config.js"></script>
    <script type="text/javascript" charset="utf-8" src="{__ADMIN_PATH}plug/umeditor/umeditor.min.js"></script>
    <script type="text/javascript" src="{__ADMIN_PATH}plug/umeditor/lang/zh-cn/zh-cn.js"></script>
    
    <style>
      .flex{
          display: flex;
          align-items: center;
          justify-content: space-between;
          flex-wrap: wrap;
      }
      .bg-red {
          background-color: #e54d42;
          color: #ffffff;
      }

      .bg-orange {
          background-color: #f37b1d;
          color: #ffffff;
      }


      .bg-green {
          background-color: #39b54a;
          color: #ffffff;
      }

      .bg-cyan {
          background-color: #1cbbb4;
          color: #ffffff;
      }
        .div-has-backgound{
            height: 150px;
            width: 320px;
            margin: 10px 15px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .div-has-backgound>h1{
            text-align: center;
            margin: 0 auto;
        }
    </style>
</head>
<body>
<div style="display: flex;flex-direction: column">
    <div class="flex">
        <div onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_content')}?id={$content}&&type=1')" class="bg-red div-has-backgound description" ><h1>商品详情</h1></div>
        <div onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_content')}?id={$content}&&type=1&&field='+'process')" class="bg-green div-has-backgound process"><h1>行程安排</h1></div>
        <div onclick="$eb.createModalFrame(this.innerText,'{:Url('edit_content')}?id={$content}&&type=1&&field='+'attention')" class="bg-cyan div-has-backgound attention" ><h1>注意事项</h1></div>
        <div class="bg-orange div-has-backgound" ><h1>待开发模块...</h1></div>
    </div>
</div>

<script type="text/javascript">
    $eb = parent._mpApi;
</script>
</body>
</html>