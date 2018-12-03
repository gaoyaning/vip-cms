<?php
define("START_TIME", microtime(true),TRUE);
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/', function() {
    return 'hello world';
});

Route::post('test', 'TestController@test');

// 后台管理系统
Route::group(['prefix' => 'api/admin', 'namespace' => 'Admin', 'middleware' => ['log', 'ParamsCheck']], function () {
    Route::post('v1/user/login',   'AdminUserController@login');
    // 管理用户身份认证&权限校验
    Route::group(['middleware' => 'AdminAuth'], function() {
        // 图片上传
        Route::post('v1/image/upload', 'AdminImageController@upload');

        Route::post('v1/user/info',     'AdminUserController@userInfo');
        Route::post('v1/system/menus',  'AdminUserController@menus');
        Route::post('v1/user/changepwd','AdminUserController@changepwd');
        // 账号管理 查找-添加-删除-重置密码-修改
        Route::post('v1/account/add',      'AdminUserController@add');
        Route::post('v1/account/start',    'AdminUserController@update');
        Route::post('v1/account/query',    'AdminUserController@query');
        Route::post('v1/account/delete',   'AdminUserController@update');
        Route::post('v1/account/modify',   'AdminUserController@modify');
        Route::post('v1/account/detail',   'AdminUserController@detail');
        Route::post('v1/account/resetPwd', 'AdminUserController@resetPwd');

        // 角色管理
        Route::post('v1/role/add',        'AdminRoleController@add');
        Route::post('v1/role/query',      'AdminRoleController@query');
        Route::post('v1/role/start',      'AdminRoleController@start');
        Route::post('v1/role/delete',     'AdminRoleController@delete');
        Route::post('v1/role/modify',     'AdminRoleController@modify');
        Route::post('v1/role/detail',     'AdminRoleController@detail');
        Route::post('v1/role/permission', 'AdminRoleController@info');

        // 广告管理
        Route::Post('v1/ads/add',    'AdminAdsController@add');
        Route::Post('v1/ads/top',    'AdminAdsController@top');
        Route::post('v1/ads/type',   'AdminAdsController@type');
        Route::Post('v1/ads/query',  'AdminAdsController@query');
        Route::Post('v1/ads/delete', 'AdminAdsController@del');
        Route::Post('v1/ads/online', 'AdminAdsController@online');
        Route::Post('v1/ads/modify', 'AdminAdsController@modify');
        Route::Post('v1/ads/detail', 'AdminAdsController@detail');
        Route::post('v1/ads/addType','AdminAdsController@addType');

        // 权益推荐配置
        Route::Post('v1/recommends/add',    'AdminRecommendController@add');
        Route::Post('v1/recommends/top',    'AdminRecommendController@top');
        Route::Post('v1/recommends/all',    'AdminRecommendController@all');
        Route::Post('v1/recommends/order',  'AdminRecommendController@order');
        Route::Post('v1/recommends/query',  'AdminRecommendController@query');
        Route::Post('v1/recommends/delete', 'AdminRecommendController@delete');
        Route::Post('v1/recommends/modify', 'AdminRecommendController@modify');
        Route::Post('v1/recommends/detail', 'AdminRecommendController@detail');
        Route::Post('v1/recommends/update', 'AdminRecommendController@update');

        // 权益配置
        Route::Post('v1/right/add',     'AdminRightController@add');
        Route::Post('v1/right/all',     'AdminRightController@all');    // 获取所有商品品类
        Route::Post('v1/right/query',   'AdminRightController@query');
        Route::Post('v1/right/modify',  'AdminRightController@modify');
        Route::Post('v1/right/detail',  'AdminRightController@detail');
        Route::Post('v1/right/delete',  'AdminRightController@delete');
        Route::Post('v1/right/online',  'AdminRightController@online');
        Route::Post('v1/right/offline', 'AdminRightController@offline');

        // 运营商管理
        Route::Post('v1/telco/add',     'AdminTelcoController@add');
        Route::Post('v1/telco/info',    'AdminTelcoController@info');
        Route::Post('v1/telco/query',   'AdminTelcoController@query');
        Route::Post('v1/telco/delete',  'AdminTelcoController@delete');
        Route::Post('v1/telco/modify',  'AdminTelcoController@modify');
        Route::Post('v1/telco/detail',  'AdminTelcoController@detail');
        Route::Post('v1/telco/enable',  'AdminTelcoController@enable');
        Route::Post('v1/telco/disable', 'AdminTelcoController@disable');

        Route::Post('v1/telco/login/create', 'AdminTelcoController@loginCreate');
        // 运营商等级维护
        Route::Post('v1/telco/addLevel',     'AdminTelcoController@addLevel');
        Route::Post('v1/telco/allLevel',     'AdminTelcoController@allLevel');
        Route::Post('v1/telco/modifyLevel',  'AdminTelcoController@modifyLevel');
        Route::Post('v1/telco/deleteLevel',  'AdminTelcoController@modifyLevel');
        Route::Post('v1/telco/queryLevels',  'AdminTelcoController@queryLevels');
        Route::Post('v1/telco/detailLevel',  'AdminTelcoController@detailLevel');

        // 商品管理 - 信息管理
        Route::Post('v1/product/info/add',     'AdminProductController@addInfo');
        Route::Post('v1/product/info/all',     'AdminProductController@allInfo');
        Route::Post('v1/product/info/query',   'AdminProductController@queryInfo');
        Route::Post('v1/product/info/delete',  'AdminProductController@deleteInfo');
        Route::Post('v1/product/info/online',  'AdminProductController@onlineInfo');
        Route::Post('v1/product/info/modify',  'AdminProductController@modifyInfo');
        Route::Post('v1/product/info/detail',  'AdminProductController@infoDetail');
        Route::Post('v1/product/info/offline', 'AdminProductController@offlineInfo');
        // 商品管理 - 分类管理
        Route::Post('v1/product/category/add',    'AdminProductController@addCategory');
        Route::Post('v1/product/category/top',    'AdminProductController@topCategory');
        Route::Post('v1/product/category/query',  'AdminProductController@queryCategory');
        Route::Post('v1/product/category/delete', 'AdminProductController@updateCategory');
        Route::Post('v1/product/category/modify', 'AdminProductController@modifyCategory');
        Route::Post('v1/product/category/detail', 'AdminProductController@categoryDetail');
        Route::Post('v1/product/category/ascend', 'AdminProductController@ascentCategory');
        Route::Post('v1/product/category/descend','AdminProductController@descentCategory');
    });
});

// 用户会员权益领取系统
Route::group(['prefix' => 'vip/api', 'namespace' => 'Vip', 'middleware' => ['log', 'ParamsCheck']], function () {
    Route::post('v1/user/login', 'PartnerUserController@login');
    // 权益用户身份认证
    Route::group(['middleware' => 'VipAuth'], function() {
        // 用户操作
        Route::post('v1/user/info',   'PartnerUserController@userInfo');
        Route::post('v1/user/notice', 'PartnerUserController@notice');
        Route::post('v1/user/logout', 'PartnerUserController@logout');
        Route::post('v1/user/mobile', 'PartnerUserController@mobile');
        // 商品分类推荐 - 商品权益管理
        Route::Post('v1/all/recommend/right/query',        'UserRightController@allRecommendRightQuery');
        Route::Post('v1/user/recommend/right/query',       'UserRightController@userRecommendRightQuery');  // 我的全部权益
        Route::Post('v1/level/recommend/right/query',      'UserRightController@levelRecommendRightQuery');
        Route::Post('v1/user/recommend/valid/right/query', 'UserRightController@userRecommendiValidRightQuery');  // 我的有效权益
        Route::Post('v1/product/recommend/right/detail',   'UserRightController@productDetail');
        // 客服
        Route::Post('v1/service/get/questions', 'CustomerController@getQuestions');
        Route::Post('v1/service/get/question',  'CustomerController@getQuestion');
        // 搜索
        Route::Post('v1/search',         'SearchController@search');
        Route::Post('v1/hot/search',     'SearchController@hotSearch');
        Route::Post('v1/search/history', 'SearchController@history');
        // 广告
        Route::Post('v1/ads/query', 'AdsController@query');
        Route::Post('v1/ads/types', 'AdsController@getTypes');
        // 权益领取
        Route::Post('v1/user/activate', 'ActivateController@rightActivate');                    // 权益激活
        Route::Post('v1/user/get/captcha', 'ActivateController@getCaptcha');                    // 获取验证码
        Route::Post('v1/user/activate/captcha', 'ActivateController@getActivateCaptcha');       // 获取激活验证码
        Route::Post('v1/user/recommend/right/activate', 'ActivateController@rightActivate');    // 权益激活
    });
});
