<?php

namespace app\admin\controller\system;

use app\admin\model\system\SystemAttachment as SystemAttachmentModel;
use app\admin\controller\AuthController;
use service\UploadService as Upload;
/**
 * 附件管理控制器
 * Class SystemAttachment
 * @package app\admin\controller\system
 *
 */
class SystemAttachment extends AuthController
{


    /**
     * TODO 编辑器上传图片
     */
    public function upload()
    {
        $res = Upload::image('upfile','editor/'.date('Ymd'));
        if(is_array($res)){
            SystemAttachmentModel::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],0,$res['image_type'],$res['time']);
            $info = array(
                "originalName" => $res['name'],
                "name" => $res['name'],
                "url" => '.'.$res['dir'],
                "size" => $res['size'],
                "type" => $res['type'],
                "state" => "SUCCESS"
            );
        }else
            $info = array(
                "msg" => $res,
                "state" => "ERROR"
            );
        echo json_encode($info);
    }
}
