<?php
namespace  App\Http\Controllers\Vip;

use App\Models\Product;
use App\Models\VipUser;
use App\Models\Recommend;
use App\Models\PartnerUser;
use Illuminate\Http\Request;
use App\Services\TokenService;
use App\Services\CryptService;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class PartnerUserController extends Controller
{
    public function login(Request $req) {
        $params   = $req->params;
        $partner  = array_get($params, 'partner', '');
        // 解密运营商请求参数
        $content  = array_get($params, 'content', '');
        $partner_config = config('partner');
        $key = str_replace('\\n', "\n", $partner_config[$partner]);
        $decpryt  = CryptService::decrypt('rsa', $key, $content);
        $url_info = json_decode($decpryt, true);
        $level    = array_get($url_info, 'level', '');
        $union_id = array_get($url_info, 'union_id', '');
        if ('' == $union_id) {
            return ResponseService::returnArr([], '缺少union id', 50001);
        }
        if ('' == $level) {
            return ResponseService::returnArr([], '缺少等级信息', 50002);
        }
        $params['level']    = $level;
        $params['union_id'] = $union_id;
        try {
            $user = PartnerUser::getOrCreateUserByTecoTid($params);
            if (is_array($user)) {
                return $user;
            }
        } catch (\Exception $e) {
            return ResponseService::returnArr([], '系统错误', 50003);
        }
        // 生成token 返回给用户
        $token_info = [
            'id'       => $user->id,
            'tid'      => $user->tid,
            'uid'      => $user->uid,
            'telco_id' => $user->telco_id,
            'level_id' => $user->level_id,
        ];
        $user->status = 1;
        $user->save();
        $values = [
            'token' => TokenService::encode($token_info),
        ];
        return ResponseService::returnArr($values);
    }

    public function logout(Request $req) {
        $params = $req->params;
        $user = PartnerUser::find($params['id']);
        $user->status = 0;
        $user->save();
        return ResponseService::returnArr();
    }

    public function userInfo(Request $req) {
        $params = $req->params;
        try {
            $values = PartnerUser::getUserById($params);
        } catch (\Exception $e) {
            \Log::error('------ user info error ------',[$e->getCode(), $e->getMessage()]);
            return ResponseService::returnArr([], '系统错误', 50004);
        }
        return ResponseService::returnArr($values);
    }

    public function notice(Request $req) {
        $params = $req->params;
        $user_info = $params['user'];
        try {
            $recommends = Recommend::getRecommendsByTelcoLevelId($user_info);
            $product_ids = [];
            foreach ($recommends as $recommend) {
                $product_ids[] = $recommend->product_id;
            }
            $products = Product::getNoticeProducts($product_ids);
            $values = [];
            foreach ($products as $product) {
                $values['notices'][] = '消息: 您有' . $product->type->name .'-'. $product->name . '即将到期!';
            }
        } catch (\Exception $e) {
            return ResponseService::returnArr([], '系统错误', 50004);
        }
        return ResponseService::returnArr($values);
    }

    public function mobile(Request $req) {
        $params = $req->params;
        $id = array_get($params, 'user.id', 0);
        $mobile = "";
        try {
            $parnter_user = PartnerUser::find($id);
            if ($parnter_user) {
                $uid = $parnter_user->uid;
                if (0 == $uid) {
                    return ResponseService::returnArr([], '手机号不存在', 50005);
                }
                $vip_user = VipUser::find($uid);
                if ($vip_user) {
                    $mobile = $vip_user->mobile;
                } else {
                    return ResponseService::returnArr([], '系统错误', 50006);
                }
            } else {
                return ResponseService::returnArr([], '用户不存在', 50007);
            }
        } catch (\Exception $e) {
            return ResponseService::returnArr([], '系统异常', 50008);
        }
        return ResponseService::returnArr(['mobile' => $mobile]);
    }
}
