# Laravel Sanctum 與 Captcha 與 多重身份認證 Api練習

###### tags: `Laravel` `php` `Captcha` `Sanctum` `guard` `FormRequest`
本次使用 admin , user 示範 

## 安裝Sanctum
```
$ composer require laravel/sanctum
$ php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
$ php artisan migrate
```
### 在 app/Http/Kernel.php 文件中將 Sanctum 的Middleware添加到你的 api Middleware中
```
'api' => [
    \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    'throttle:api',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```
### Issuing API Tokens
admin.php 與 user.php 新增 use HasFactory;
```
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
```

### 設定guard
config/auth.php
```
'guards' => [
        'user' => [
            'driver' => 'sanctum',
            'provider' => 'users',
            'hash' => false,
        ],

        'admin' => [
            'driver' => 'sanctum',
            'provider' => 'admins',
            'hash' => false,
        ],
    ],
```
### 設定對應的Model
config/auth.php

```
'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],
```
### 設定路由
app/Providers/RouteServiceProvider.php
```
$this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));

            Route::prefix('api/admin')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/admin.php'));

            Route::prefix('api/user')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/user.php'));
        });
```
routes/user.php
```
use App\Http\Controllers\UserController;

Route::post('register', [UserController::class, 'register']);
Route::post('login', [UserController::class, 'login']);

Route::group(['middleware' => ['auth:user']], function(){
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('memberInfo', [UserController::class, 'memberInfo']);
});
```
routes/admin.php
```
use App\Http\Controllers\AdminController;

Route::post('/register', [AdminController::class, 'register']);
Route::post('/login', [AdminController::class, 'login']);

Route::group(['middleware' => ['auth:admin']], function(){
    Route::post('/logout', [AdminController::class, 'logout']);
    Route::get('/memberInfo', [AdminController::class, 'memberInfo']);
});
```
## 使用 postman 測試
### admin 登入取得 token
![](https://i.imgur.com/Tn6DEhb.png)

### 取得會員資料
![](https://i.imgur.com/ofgF56f.png)

### 測試將 admin token 使用在 user 上
被擋住
![](https://i.imgur.com/yRje7jJ.png)

## 安裝Captcha
[Captcha for Laravel 5/6/7/8](https://github.com/mewebstudio/captcha)
```
$ composer require mews/captcha
```

### 註冊
config/app.php
```
    'providers' => [
        // ...
        'Mews\Captcha\CaptchaServiceProvider',
    ]
```
```
    'aliases' => [
        // ...
        'Captcha' => 'Mews\Captcha\Facades\Captcha',
    ]
```
## 設定
```
$ php artisan vendor:publish
```
config/captcha.php


本次使用flat
```
'flat' => [
        'length' => 5,
        'width' => 160,
        'height' => 46,
        'quality' => 90,
        'lines' => 0,
        'bgImage' => false,
        'bgColor' => '#ecf2f4',
        'fontColors' => ['#2c3e50', '#c0392b', '#16a085', '#c0392b', '#8e44ad', '#303f9f', '#f57c00', '#795548'],
        'contrast' => -5,
    ],
```
### 新增 CaptchaRequest
```
$  php artisan make:request CaptchaRequest   
```
```
class CaptchaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email' => 'required|email:rfc',
            'password' => 'required',
            'captcha' => 'required|captcha_api:'. request('key') . ',flat'
        ];
    }

    public function messages()
    {
        return [
            'captcha.captcha_api' =>  'Incurrent!!'
        ];
    }
}

```
### UserController更改
```
public function login(CaptchaRequest $request)
    {
        $credentials = $request->only('email', 'password');

        $user = User::where('email', Arr::get($credentials, 'email'))     ->first();

        if (!$user || ! Hash::check(Arr::get($credentials, 'password'), $user->password)) {
            throw new \Exception('credentials wrong');
        }

        $user->tokens()->delete();

        return response()->json([
            'status_code' => 200,
            'token' => $user->createToken('userToken')->plainTextToken
        ]);
    }
```

## postman 測試 Captcha
### 取得key,img
http://localhost/captcha/api/flat
![](https://i.imgur.com/XQbGXKB.png)

https://codebeautify.org/base64-to-image-converter
![](https://i.imgur.com/EHID7KN.png)

### 成功登入
![](https://i.imgur.com/2JlgIvK.png)

## 注意事項
Heads 增加 Accept 如下圖
![](https://i.imgur.com/1RMxjD8.png)
