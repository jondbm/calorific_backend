<?php

use App\User;
use Illuminate\Http\Response as HttpResponse;

/**
 * Displays Angular SPA application
 */
Route::get('/', function () {
    return view('spa');
});

/**
 * Registers a new user and returns a auth token
 */
Route::controllers([
    'password' => 'Auth\PasswordController',
]);
Route::group(['domain' => 'calorificserver.jonsites.co.uk', 'prefix' => 'v1'], function () {
Route::post('/signup', function () {
   $credentials = Input::only('email', 'password');
   //$credentials['password'] = Hash::make($credentials['password']);
   try {
      $user = User::create($credentials);
   } catch (Exception $e) {
       return Response::json($e, HttpResponse::HTTP_CONFLICT);
   }
   $token = JWTAuth::fromUser($user);

   return Response::json(compact('token'));
});
/**
 * Signs in a user using JWT
 */
Route::post('/signin', function () {
    $credentials = Input::only('email', 'password');

    if (!$token = JWTAuth::attempt($credentials)) {
        return Response::json(false, HttpResponse::HTTP_UNAUTHORIZED);
    }

    return Response::json(compact('token'));
});


/**
 * Fetches a restricted resource from the same domain used for user authentication
 */


Route::post('/summary', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@summary'));

Route::post('/foodlist', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@foodlist'));

Route::post('/todaysfoods', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@todaysfoods'));

Route::post('/changebulk', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@changebulk'));

Route::post('/changeworkout', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@changeworkout'));

Route::post('/changemacros', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@changemacros'));

Route::post('/addnewfood', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@addnewfood'));

Route::post('/addfood', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@addfood'));

Route::post('/changedate', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@changedate'));

Route::post('/changesummarydate', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@changesummarydate'));

Route::post('/removefood', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@removefood'));

Route::post('/fooddiary', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@fooddiary'));

Route::post('/todaysmacros', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@todaysmacros'));

Route::post('/getmacros', array('before' => 'jwt-auth', 'uses' => 'CaloriesController@getmacros'));


 Route::resource('dbtest', 'caloriesController@dbresult');

/**
 * Fetches a restricted resource from API subdomain using CORS
 */

    Route::post('/restricted2', function () {
        try {
            JWTAuth::parseToken()->toUser();
        } catch (Exception $e) {
            return Response::json(['error' => $e->getMessage()], HttpResponse::HTTP_UNAUTHORIZED);
        }

        return ['data' => 'This has come from a dedicated API subdomain with restricted access.'];
    });
});