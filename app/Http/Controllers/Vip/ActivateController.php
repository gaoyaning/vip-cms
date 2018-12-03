<?php
namespace  App\Http\Controllers\Vip;

use App\Models\Product;
use App\Models\VipUser;
use App\Services\Redis;
use App\Models\Recommend;
use App\Models\PartnerUser;
use App\Models\ExchangeCode;
use Illuminate\Http\Request;
use App\Models\ReceiveRecord;
use App\Services\TokenService;
use App\Services\CryptService;
use App\Services\CaptchaService;
use App\Services\ResponseService;
use App\Http\Controllers\Controller;

class ActivateController extends Controller
{
    private static $user_captcha_prefix = 'vip-user-captcha-';      // 用户验证码
    private static $user_captcha_times  = 'vip-user-captcha-times-'; // 用户一天获取次数
    private static $get_times = 5;

    public function getActivateCaptcha(Request $req) {
        $params = $req->params;
        $id = array_get($params, 'user.id', 0);
        $partner_user = PartnerUser::find($id);
        if ($partner_user && 0 != $partner_user->uid) {
            $vip_user = VipUser::find($partner_user->uid);
            if ($vip_user) {
                $params['mobile'] = $vip_user->mobile;
                $req->params = $params;
                return $this->getCaptcha($req);
            }
        }
        return ResponseService::returnArr([], '用户基本信息缺失', 2012);
    }

    public function getCaptcha(Request $req) {
        $params = $req->params;
        $captcha = array_get($params, 'captcha', '');
        $id = array_get($params, 'user.id', 0);
        if (0 == $id) {
            return ResponseService::returnArr([], '请登录', 2007);
        }
        $key_code  = self::$user_captcha_prefix . $id;
        $key_times = self::$user_captcha_times . $id;
        if ('' != $captcha) {
            // 验证验证码
            $code = Redis::get($key_code);
            if ($code != $captcha) {
                if ('' == $code) {
                    return ResponseService::returnArr([], '验证码失效', 2008);
                }
                return ResponseService::returnArr([], '验证码错误，请确认', 2008);
            } else {
                $this->setVipUser($params);
                return ResponseService::returnArr([], '验证成功', 200);
            }
        }
        // 获取验证码
        try {
            $times = Redis::get($key_times);
            if ($times >= self::$get_times) {
                return ResponseService::returnArr([], '超过验证次数', 2009);
            }
            $captcha = CaptchaService::getCaptcha($params);
        } catch (\Exception $e) {
            return ResponseService::returnArr([], '获取验证码失败，请重试', 2010);
        }
        // 验证码写redis
        Redis::setex($key_code, 60, $captcha);
        $times = Redis::incr($key_times);
        if (0 == $times) {
            $end = $this->getDayEnd();
            Redis::expire($key_times, $end);
        }
        return ResponseService::returnArr([], '获取验证码成功', 200);
    }

    private function getDayEnd() {
        $night = date('Y-m-d 23:59:59', time());
        return strtotime($night);
    }

    private function setVipUser($params) {
        try {
            $partner_user = PartnerUser::find(array_get($params, 'user.id', 0));
            if ($partner_user && 0 == $partner_user->uid) {
                $vip_user = VipUser::addOrFindUser($params['mobile']);
                $partner_user->uid = $vip_user->id;
                $partner_user->save();
            }
        } catch (\Exception $e) {
        }
    }

    public function rightActivate(Request $req) {
        $params = $req->params;
        $captcha = array_get($params, 'captcha', '');
        if ('' == $captcha) {
            return ResponseService::returnArr([], '请输入验证码', 2011);
        }
        $id = array_get($params, 'user.id', 0);
        $key_code  = self::$user_captcha_prefix . $id;
        // 验证验证码
        $code = Redis::get($key_code);
        if ($code != $captcha) {
            if ('' == $code) {
                return ResponseService::returnArr([], '验证码失效', 2008);
            }
            return ResponseService::returnArr([], '验证码错误，请确认', 2008);
        }
        // 用户是否领取过
        $record = ReceiveRecord::getReceivedProductRecord($params);
        if ($record) {
            return ResponseService::returnArr([], '该商品已经领取过！', 2012);
        }
        // 1 检验用户是否用于该权益
        $recommend = Recommend::hasRecommendRight($params);
        if (!$recommend) {
            return ResponseService::returnArr([], '用户无该商品领取权限', 2012);
        }
        $right = $recommend->right;
        $product  = $recommend->product;
        if ($right->total <= 0 || $product->total <= 0) {
            return ResponseService::returnArr([], '该商品已无库存', 2013);
        }
        // 2 启动事物消减商品 - 激活商品
        $code = "";
        $api  = false;
        \DB::beginTransaction();
        try {
            $right    = $recommend->rightForUpdate;
            $product  = $recommend->productForUpdate;
            // 2.1 权益商品是否还有余额
            if ($right->total <= 0 || $product->total <= 0) {
                \DB::rollBack();
                return ResponseService::returnArr([], '该商品已无库存', 2014);
            }
            $supplier = $product->supplier;
            // 获取coupon code
            if (1 == $supplier->activate) {
                $exchange = ExchangeCode::getExchangeCode($supplier->id, $product->id);
                if ($exchange) {
                    $code = $exchange->code;
                    $exchange->status = 1;
                    $exchange->save();
                    $right->total   = $right->total - 1;
                    $product->total = $product->total - 1;
                    $right->save();
                    $product->save();
                } else {
                    return ResponseService::returnArr([], '该商品已无库存', 2015);
                }
            } else {
                // api == todo
            }
            // 记录用户领用记录
            if ($api || "" != $code) {
                ReceiveRecord::setUserReceiveRecord($params, $code);
            }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return ResponseService::returnArr([], '该商品已无库存', 2015);
        }
        // 激活成功返回coupon
        if ($api) {
            return ResponseService::returnArr([], '激活成功，卡券已发送至相应的账户', 200);
        } else {
            return ResponseService::returnArr(['coupon' => $code], '激活成功', 200);
        }
    }
}
